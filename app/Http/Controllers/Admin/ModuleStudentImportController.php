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
            $extension = strtolower((string) $uploadedFile->getClientOriginalExtension());
            $readerType = match ($extension) {
                'xlsx' => 'Xlsx',
                'csv' => 'Csv',
                default => 'Xlsx',
            };

            $reader = IOFactory::createReader($readerType);
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

        $columnMapping = [];

        foreach ($rows as $rowIndex => $columns) {
            if (!is_array($columns)) {
                continue;
            }

            if ($this->rowIsEmpty($columns)) {
                if ($processed > 0) {
                    break;
                }

                continue;
            }

            if ($this->rowLooksLikeHeader($columns)) {
                $columnMapping = $this->buildHeaderColumnMapping($columns) + $columnMapping;

                continue;
            }

            $apogeeColumn = $this->resolveColumnKey($columnMapping, $columns, 'apogee_code', 0);
            $apogeeCode = trim((string) ($columns[$apogeeColumn] ?? ''));

            if ($apogeeCode === '') {
                continue;
            }

            $processed++;

            $lastNameColumn = $this->resolveColumnKey($columnMapping, $columns, 'last_name', 1);
            $firstNameColumn = $this->resolveColumnKey($columnMapping, $columns, 'first_name', 2);
            $birthDateColumn = $this->resolveColumnKey($columnMapping, $columns, 'birth_date', 3);
            $labelColumn = $columnMapping['label'] ?? null;

            $lastName = $this->normalizeLastName($columns[$lastNameColumn] ?? '');
            $firstName = $this->normalizeFirstName($columns[$firstNameColumn] ?? '');
            $birthDateRaw = $columns[$birthDateColumn] ?? null;

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

            if ($labelColumn !== null) {
                $rawLabel = $columns[$labelColumn] ?? null;

                if (is_string($rawLabel)) {
                    $rawLabel = trim($rawLabel);
                }

                $label = ($rawLabel === null || $rawLabel === '') ? null : (string) $rawLabel;

                $module->students()->syncWithoutDetaching([
                    $student->id => ['label' => $label],
                ]);
            } else {
                $module->students()->syncWithoutDetaching([$student->id]);
            }
        }

        if ($processed === 0) {
            return redirect()->route('admin.modules.edit', $module)
                ->withErrors(['file' => 'No student rows were found beneath the header row or before the first blank row in the uploaded file.']);
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

    private function rowIsEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function rowLooksLikeHeader(array $row): bool
    {
        $firstColumnKey = $this->getColumnKey($row, 0);

        if ($firstColumnKey === null) {
            return false;
        }

        return $this->normalizeHeaderTitle($row[$firstColumnKey] ?? null) === 'apogee_code';
    }

    /**
     * @return array<string, string|int>
     */
    private function buildHeaderColumnMapping(array $row): array
    {
        $mapping = [];

        foreach ($row as $columnKey => $value) {
            $field = $this->normalizeHeaderTitle($value);

            if ($field !== null && !isset($mapping[$field])) {
                $mapping[$field] = $columnKey;
            }
        }

        return $mapping;
    }

    private function normalizeHeaderTitle(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = Str::of((string) $value)
            ->ascii()
            ->lower()
            ->squish()
            ->replaceMatches('/[^a-z]/', '')
            ->value();

        return match ($normalized) {
            'apogee', 'codeapogee', 'apogeecode' => 'apogee_code',
            'nom' => 'last_name',
            'prenom' => 'first_name',
            'naissance', 'datenaissance', 'datedenaissance' => 'birth_date',
            'label' => 'label',
            default => null,
        };
    }

    private function getColumnKey(array $row, int $offset): string|int|null
    {
        $keys = array_keys($row);

        return $keys[$offset] ?? null;
    }

    private function resolveColumnKey(array $columnMapping, array $row, string $field, int $position): string|int|null
    {
        if (isset($columnMapping[$field])) {
            return $columnMapping[$field];
        }

        return $this->getColumnKey($row, $position);
    }
}
