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

    public function test_admin_can_import_students_into_module(): void
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
        $sheet->setCellValue('A18', 'APO001');
        $sheet->setCellValue('B18', 'DOE');
        $sheet->setCellValue('C18', 'john');
        $sheet->setCellValue('D18', '2001-02-03');
        $sheet->setCellValue('A19', 'APO002');
        $sheet->setCellValue('B19', 'SMITH');
        $sheet->setCellValue('C19', 'JANE');
        $sheet->setCellValue('D19', ExcelDate::PHPToExcel(new \DateTimeImmutable('2002-04-15')));
        $sheet->getStyle('D19')->getNumberFormat()->setFormatCode('yyyy-mm-dd');

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

    public function test_admin_can_import_students_from_csv_without_extension(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $module = Module::create([
            'code' => 'CS201',
            'title' => 'Advanced Programming',
        ]);

        $tempFileBase = tempnam(sys_get_temp_dir(), 'import_csv');
        $csvFilePath = $tempFileBase . '.csv';

        rename($tempFileBase, $csvFilePath);

        $handle = fopen($csvFilePath, 'w');
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

        $studentOne = Student::where('apogee_code', 'APO010')->firstOrFail();
        $studentTwo = Student::where('apogee_code', 'APO011')->firstOrFail();

        $this->assertEquals('2001-02-03', $studentOne->birth_date?->toDateString());
        $this->assertEquals('2002-04-15', $studentTwo->birth_date?->toDateString());

        $this->assertTrue($module->students()->whereKey($studentOne->id)->exists());
        $this->assertTrue($module->students()->whereKey($studentTwo->id)->exists());
    }
}
