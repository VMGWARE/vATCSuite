<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\ATISAudioFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Throwable;

// TODO: Figure out to run queue:work on production server
class CleanUpExpiredATISAudioFiles implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Cleaning up expired ATIS audio files...');

        // Get all expired ATIS audio files
        $expiredATISAudioFiles = ATISAudioFile::where('expires_at', '<', now())->get();
        Log::info('Found ' . count($expiredATISAudioFiles) . ' expired ATIS audio files.');

        // Delete all expired ATIS audio files
        foreach ($expiredATISAudioFiles as $expiredATISAudioFile) {
            try {
                $id = $expiredATISAudioFile->id;
                $name = $expiredATISAudioFile->file_name;
                Storage::delete('public/atis/' . $id . '/' . $name);
                $expiredATISAudioFile->delete();
            } catch (Throwable $exception) {
                Log::error('Failed to delete expired ATIS audio file: ' . $exception->getMessage());
            }
        }

        Log::info('Finished cleaning up expired ATIS audio files.');
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        Log::error('Failed to clean up expired ATIS audio files: ' . $exception->getMessage());
    }
}
