<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Backup;
use App\Services\BackupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BackupController extends Controller
{
    public function index(): View
    {
        return view('backups.index', [
            'backups' => Backup::query()->with('user')->latest()->paginate(15),
            'types' => Backup::TYPES,
        ]);
    }

    public function store(Request $request, BackupService $backupService): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:'.implode(',', Backup::TYPES)],
        ]);

        try {
            $backup = $backupService->create($data['type'], Auth::user());

            return back()->with('success', 'Backup created: '.$backup->filename);
        } catch (\Throwable $exception) {
            return back()->with('error', 'Backup failed: '.$exception->getMessage());
        }
    }

    public function download(Backup $backup, BackupService $backupService): BinaryFileResponse|RedirectResponse
    {
        $path = $backupService->absolutePath($backup);

        if (! File::exists($path) || $backup->status !== 'completed') {
            return back()->with('error', 'Backup file is not available.');
        }

        ActivityLog::record('backup_downloaded', 'Backup downloaded: '.$backup->filename, $backup);

        return response()->download($path, $backup->filename);
    }

    public function destroy(Backup $backup, BackupService $backupService): RedirectResponse
    {
        $path = $backupService->absolutePath($backup);

        if (File::exists($path)) {
            File::delete($path);
        }

        ActivityLog::record('backup_deleted', 'Backup deleted: '.$backup->filename, $backup);
        $backup->delete();

        return redirect()->route('backups.index')->with('success', 'Backup deleted.');
    }
}
