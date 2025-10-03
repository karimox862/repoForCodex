<?php

namespace Tests\Feature;

use App\Models\Mark;
use App\Models\Module;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Tests\TestCase;

class ModuleMarkImportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'password' => Hash::make('password'),
        ]);
    }

    public function test_admin_can_import_marks_from_xls(): void
    {
        $module = Module::create([
            'code' => 'MTH101',
            'title' => 'Calculus I',
        ]);

        $studentA = Student::create([
            'apogee_code' => 'APO001',
            'first_name' => 'Alice',
            'last_name' => 'Example',
            'email' => 'alice@example.com',
        ]);

        $studentB = Student::create([
            'apogee_code' => 'APO002',
            'first_name' => 'Bob',
            'last_name' => 'Example',
            'email' => 'bob@example.com',
        ]);

        $module->students()->sync([$studentA->id, $studentB->id]);

        $existingMark = Mark::create([
            'module_id' => $module->id,
            'student_id' => $studentB->id,
            'grade' => '10',
        ]);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Apogee');
        $sheet->setCellValue('B1', 'Grade');
        $sheet->setCellValue('A2', $studentA->apogee_code);
        $sheet->setCellValue('B2', '15.5');
        $sheet->setCellValue('A3', $studentB->apogee_code);
        $sheet->setCellValue('B3', '12');
        $sheet->setCellValue('A4', 'UNKNOWN');
        $sheet->setCellValue('B4', '8');

        $writer = IOFactory::createWriter($spreadsheet, 'Xls');
        ob_start();
        $writer->save('php://output');
        $xlsContents = ob_get_clean() ?: '';

        $file = UploadedFile::fake()->createWithContent('marks.xls', $xlsContents);

        $response = $this->actingAs($this->admin)
            ->from(route('admin.modules.edit', $module))
            ->post(route('admin.modules.marks.import', $module), [
                'file' => $file,
            ]);

        $response->assertRedirect(route('admin.modules.edit', $module));
        $response->assertSessionHas('status', static function (string $message): bool {
            return str_contains($message, 'Marks import completed');
        });

        $response->assertSessionHas('mark_import_summary', function (array $summary): bool {
            $this->assertSame(3, $summary['processed']);
            $this->assertSame(1, $summary['created']);
            $this->assertSame(1, $summary['updated']);
            $this->assertCount(1, $summary['errors']);

            return true;
        });

        $response->assertSessionHas('mark_import_errors');

        $this->assertDatabaseHas('marks', [
            'module_id' => $module->id,
            'student_id' => $studentA->id,
            'grade' => '15.5',
        ]);

        $this->assertDatabaseHas('marks', [
            'module_id' => $module->id,
            'student_id' => $studentB->id,
            'grade' => '12',
        ]);

        $this->assertSame(2, Mark::where('module_id', $module->id)->count());
        $this->assertNull($existingMark->fresh()->recheck_requested_at);
    }

    public function test_import_requires_xls_file(): void
    {
        $module = Module::create([
            'code' => 'PHY201',
            'title' => 'Physics',
        ]);

        $response = $this->actingAs($this->admin)
            ->from(route('admin.modules.edit', $module))
            ->post(route('admin.modules.marks.import', $module), [
                'file' => UploadedFile::fake()->create('marks.pdf', 10, 'application/pdf'),
            ]);

        $response->assertRedirect(route('admin.modules.edit', $module));
        $response->assertSessionHasErrors('file');
    }
}
