<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ModuleStudentImportRequest;
use App\Models\Module;
use App\Models\Student;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Throwable;

class ModuleStudentImportController extends Controller
{
    public function __invoke(ModuleStudentImportRequest $request, Module $module): RedirectResponse
    {
        $uploadedFile = $request->file('file');

        if ($uploadedFile === null) {
            return redirect()->route('admin.modules.edit', $module)
                ->withErrors(['file' => 'No file was uploaded.']);
        }

        try {
            $reader = IOFactory::createReaderForFile($uploadedFile->getRealPath());
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($uploadedFile->getRealPath());
        } catch (Throwable $exception) {
            report($exception);

            return redirect()->route('admin.modules.edit', $module)
                ->withErrors(['file' => 'Failed to read the uploaded file. Please upload a valid Excel or CSV document.']);
        }

        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        $existingAttachments = array_map(
            'intval',
            $module->students()->pluck('students.id')->all()
        );

        $processed = 0;
        $created = 0;
        $updated = 0;
        $attached = 0;
        $errors = [];

        for ($rowIndex = 18; isset($rows[$rowIndex]); $rowIndex++) {
            $apogeeCode = trim((string) ($rows[$rowIndex]['A'] ?? ''));

            if ($apogeeCode === '') {
                break;
            }

            $processed++;

            $lastName = $this->normalizeLastName($rows[$rowIndex]['B'] ?? '');
            $firstName = $this->normalizeFirstName($rows[$rowIndex]['C'] ?? '');
            $birthDateRaw = $rows[$rowIndex]['D'] ?? null;

            try {
                $birthDate = $this->normalizeBirthDate($birthDateRaw);
            } catch (Throwable $exception) {
                report($exception);
                $errors[] = "Row {$rowIndex}: unable to parse birth date for student {$apogeeCode}.";
                $birthDate = null;
            }

            try {
                $student = Student::updateOrCreate(
                    ['apogee_code' => $apogeeCode],
                    [
                        'last_name' => $lastName,
                        'first_name' => $firstName,
                        'birth_date' => $birthDate,
                    ]
                );
            } catch (Throwable $exception) {
                report($exception);
                $errors[] = "Row {$rowIndex}: failed to save student {$apogeeCode}.";

                continue;
            }

            if ($student->wasRecentlyCreated) {
                $created++;
            } else {
                $updated++;
            }

            if (!in_array($student->id, $existingAttachments, true)) {
                $attached++;
                $existingAttachments[] = $student->id;
            }

            $module->students()->syncWithoutDetaching([$student->id]);
        }

        if ($processed === 0) {
            return redirect()->route('admin.modules.edit', $module)
                ->withErrors(['file' => 'No student rows were found starting at row 18 in the uploaded file.']);
        }

        $summary = compact('processed', 'created', 'updated', 'attached');
        $summary['errors'] = $errors;

        $message = sprintf(
            'Import completed: %d row(s) processed, %d created, %d updated, %d linked.',
            $processed,
            $created,
            $updated,
            $attached
        );

        $redirect = redirect()->route('admin.modules.edit', $module)
            ->with('status', $message)
            ->with('import_summary', $summary);

        if ($errors !== []) {
            $redirect->with('import_errors', $errors);
        }

        return $redirect;
    }

    private function normalizeLastName(?string $value): string
    {
        return Str::of((string) $value)->squish()->upper();
    }

    private function normalizeFirstName(?string $value): string
    {
        return Str::of((string) $value)->squish()->title();
    }

    private function normalizeBirthDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            $dateTime = ExcelDate::excelToDateTimeObject((float) $value);

            return CarbonImmutable::instance($dateTime)->format('Y-m-d');
        }

        return CarbonImmutable::parse((string) $value)->format('Y-m-d');
    }
}
