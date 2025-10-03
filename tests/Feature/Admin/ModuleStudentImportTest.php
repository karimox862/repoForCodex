<?php

namespace Tests\Feature\Admin;

use App\Models\Module;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class ModuleStudentImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_import_students_from_spreadsheet_with_header_row(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $module = Module::create([
            'code' => 'CS101',
            'title' => 'Introduction to Programming',
        ]);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Apogee');
        $sheet->setCellValue('B1', 'Nom');
        $sheet->setCellValue('C1', 'Prenom');
        $sheet->setCellValue('D1', 'Naissance');
        $sheet->setCellValue('A2', 'APO001');
        $sheet->setCellValue('B2', 'DOE');
        $sheet->setCellValue('C2', 'john');
        $sheet->setCellValue('D2', '2001-02-03');
        $sheet->setCellValue('A3', 'APO002');
        $sheet->setCellValue('B3', 'SMITH');
        $sheet->setCellValue('C3', 'JANE');
        $sheet->setCellValue('D3', ExcelDate::PHPToExcel(new \DateTimeImmutable('2002-04-15')));
        $sheet->getStyle('D3')->getNumberFormat()->setFormatCode('yyyy-mm-dd');

        $tempFile = tempnam(sys_get_temp_dir(), 'import');
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFile);
        $uploadedFile = new UploadedFile(
            $tempFile,
            'students.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        $response = $this->actingAs($admin)->post(
            route('admin.modules.students.import', $module),
            ['file' => $uploadedFile]
        );

        $response->assertRedirect(route('admin.modules.edit', $module));
        $response->assertSessionHas('status');
        $response->assertSessionHas('import_summary.processed', 2);
        $response->assertSessionHas('import_summary.created', 2);
        $response->assertSessionHas('import_summary.updated', 0);
        $response->assertSessionHas('import_summary.attached', 2);

        @unlink($tempFile);

        $this->assertDatabaseHas('students', [
            'apogee_code' => 'APO001',
            'first_name' => 'John',
            'last_name' => 'DOE',
        ]);

        $this->assertDatabaseHas('students', [
            'apogee_code' => 'APO002',
            'first_name' => 'Jane',
            'last_name' => 'SMITH',
        ]);

        $studentOne = Student::where('apogee_code', 'APO001')->firstOrFail();
        $studentTwo = Student::where('apogee_code', 'APO002')->firstOrFail();

        $this->assertEquals('2001-02-03', $studentOne->birth_date?->toDateString());
        $this->assertEquals('2002-04-15', $studentTwo->birth_date?->toDateString());

        $this->assertTrue($module->students()->whereKey($studentOne->id)->exists());
        $this->assertTrue($module->students()->whereKey($studentTwo->id)->exists());
    }

    public function test_admin_can_import_students_from_csv_with_header_row_and_updates_existing_student(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $module = Module::create([
            'code' => 'CS201',
            'title' => 'Advanced Programming',
        ]);

        $existingStudent = Student::create([
            'apogee_code' => 'APO010',
            'last_name' => 'OLD',
            'first_name' => 'Name',
            'birth_date' => '2000-01-01',
        ]);

        $tempFileBase = tempnam(sys_get_temp_dir(), 'import_csv');
        $csvFilePath = $tempFileBase . '.csv';

        rename($tempFileBase, $csvFilePath);

        $handle = fopen($csvFilePath, 'w');
        fputcsv($handle, ['Apogee', 'Nom', 'Prenom', 'Naissance']);
        fputcsv($handle, ['APO010', 'DOE', 'john', '2001-02-03']);
        fputcsv($handle, ['APO011', 'SMITH', 'jane', '2002-04-15']);
        fclose($handle);

        $extensionlessPath = $tempFileBase;
        rename($csvFilePath, $extensionlessPath);

        $uploadedFile = new UploadedFile(
            $extensionlessPath,
            'students.csv',
            'text/csv',
            null,
            true
        );

        $response = $this->actingAs($admin)->post(
            route('admin.modules.students.import', $module),
            ['file' => $uploadedFile]
        );

        $response->assertRedirect(route('admin.modules.edit', $module));
        $response->assertSessionHas('status');
        $response->assertSessionHas('import_summary.processed', 2);
        $response->assertSessionHas('import_summary.created', 1);
        $response->assertSessionHas('import_summary.updated', 1);
        $response->assertSessionHas('import_summary.attached', 2);

        @unlink($extensionlessPath);

        $this->assertDatabaseHas('students', [
            'apogee_code' => 'APO010',
            'first_name' => 'John',
            'last_name' => 'DOE',
        ]);

        $this->assertDatabaseHas('students', [
            'apogee_code' => 'APO011',
            'first_name' => 'Jane',
            'last_name' => 'SMITH',
        ]);

        $updatedStudent = $existingStudent->fresh();

        $this->assertEquals('2001-02-03', $updatedStudent->birth_date?->toDateString());
        $this->assertTrue($module->students()->whereKey($updatedStudent->id)->exists());
        $this->assertTrue($module->students()->where('apogee_code', 'APO011')->exists());
    }
}
