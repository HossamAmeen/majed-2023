<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'database:backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dump the application database into storage/backups';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $today = today()->format('Y-m-d');

        if (! is_dir($directory = storage_path('backups'))) {
            mkdir($directory, 0755, true);
        }

        // fromShellCommandline() is required since Symfony Process 4.2: the
        // constructor only accepts an array of arguments and would not be able
        // to handle the ">" output redirection used here.
        $process = Process::fromShellCommandline(
            'mysqldump --compact --skip-comments -u$DB_USER -p$DB_PASSWORD $DB_NAME > $DB_DUMP_PATH'
        );

        $process->setTimeout(900);

        try {
            $process->mustRun(null, [
                'DB_USER' => config('database.connections.mysql.username'),
                'DB_PASSWORD' => config('database.connections.mysql.password'),
                'DB_NAME' => config('database.connections.mysql.database'),
                'DB_DUMP_PATH' => storage_path("backups/{$today}.sql"),
            ]);

            Log::info('Daily DB Backup - Success');

            return self::SUCCESS;
        } catch (ProcessFailedException $exception) {
            Log::error('Daily DB Backup - Failed: '.$exception->getMessage());

            return self::FAILURE;
        }
    }
}
