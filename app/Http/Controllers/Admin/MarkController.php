<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mark;
use App\Models\Module;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

class MarkController extends Controller
{
    public function downloadReport(Module $module): Response
    {
        $module->load(['marks.student', 'professor.user']);

        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, ['Module Code', 'Module Title', 'Professor', 'Student Name', 'Student Email', 'Apogée Code', 'Grade', 'Recheck Requested At']);

        foreach ($module->marks as $mark) {
            fputcsv($handle, [
                $module->code,
                $module->title,
                optional($module->professor?->user)->name,
                trim($mark->student->first_name . ' ' . $mark->student->last_name) ?: $mark->student->name,
                $mark->student->email,
                $mark->student->apogee_code,
                $mark->grade,
                optional($mark->recheck_requested_at)?->toDateTimeString(),
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle) ?: '';
        fclose($handle);

        $fileName = sprintf('module-%s-marks.csv', $module->code);

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename='.$fileName,
        ]);
    }

    public function requestRecheck(Mark $mark): RedirectResponse
    {
        $mark->update(['recheck_requested_at' => now()]);

        return back()->with('status', 'Re-check requested for the selected mark.');
    }
}
