<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ModuleMarkImportRequest;
use App\Models\Mark;
use App\Models\Module;
use Illuminate\Http\RedirectResponse;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Throwable;

class ModuleMarkImportController extends Controller
{
    public function __invoke(ModuleMarkImportRequest $request, Module $module): RedirectResponse
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
                ->withErrors(['file' => 'Failed to read the uploaded file. Please upload a valid Excel document.']);
        }

        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        $students = $module->students()->get()->keyBy('apogee_code');

        $processed = 0;
        $created = 0;
        $updated = 0;
        $errors = [];

        foreach ($rows as $rowIndex => $columns) {
            if ($rowIndex === 1) {
                continue;
            }

            $apogeeCode = trim((string) ($columns['A'] ?? ''));
            $grade = trim((string) ($columns['B'] ?? ''));

            if ($apogeeCode === '' && $grade === '') {
                continue;
            }

            if ($apogeeCode === '') {
                $errors[] = "Row {$rowIndex}: missing apogée code.";
                continue;
            }

            $processed++;

            if (! $students->has($apogeeCode)) {
                $errors[] = "Row {$rowIndex}: student with apogée code {$apogeeCode} is not enrolled in this module.";
                continue;
            }

            if ($grade === '') {
                $errors[] = "Row {$rowIndex}: missing grade for student {$apogeeCode}.";
                continue;
            }

            $student = $students->get($apogeeCode);

            try {
                $mark = Mark::updateOrCreate(
                    [
                        'module_id' => $module->id,
                        'student_id' => $student->id,
                    ],
                    [
                        'grade' => $grade,
                        'recheck_requested_at' => null,
                    ],
                );
            } catch (Throwable $exception) {
                report($exception);
                $errors[] = "Row {$rowIndex}: failed to save grade for student {$apogeeCode}.";

                continue;
            }

            if ($mark->wasRecentlyCreated) {
                $created++;
            } else {
                $updated++;
            }
        }

        if ($processed === 0) {
            return redirect()->route('admin.modules.edit', $module)
                ->withErrors(['file' => 'No mark rows were found beyond the header in the uploaded file.']);
        }

        $summary = [
            'processed' => $processed,
            'created' => $created,
            'updated' => $updated,
            'errors' => $errors,
        ];

        $issuesSuffix = $errors === [] ? '' : sprintf(', %d issue(s)', count($errors));

        $message = sprintf(
            'Marks import completed: %d processed, %d created, %d updated%s.',
            $processed,
            $created,
            $updated,
            $issuesSuffix
        );

        $redirect = redirect()->route('admin.modules.edit', $module)
            ->with('status', $message)
            ->with('mark_import_summary', $summary);

        if ($errors !== []) {
            $redirect->with('mark_import_errors', $errors);
        }

        return $redirect;
    }
}
