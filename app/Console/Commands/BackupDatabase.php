<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use RuntimeException;

/**
 * Dumps the database to a gzip-compressed .sql.gz file under
 * storage/app/backups/ and prunes anything older than --keep-days.
 *
 * Deliberately a plain mysqldump wrapper rather than spatie/laravel-backup:
 * that package requires ext-zip and ext-pcntl, neither available on this
 * Windows/XAMPP dev box (pcntl doesn't exist on Windows PHP builds at all),
 * and the app only needs a database dump — not the full file/zip archiving
 * that package is built around. Writes to local disk per the salon's
 * preference (no S3); for real disaster recovery the production host
 * should sync storage/app/backups/ off-server on its own schedule.
 */
class BackupDatabase extends Command
{
    protected $signature = 'backup:database
        {--keep-days=14 : Delete backups older than this many days}
        {--connection= : Database connection to dump (defaults to the app\'s default connection)}';

    protected $description = 'Dump the database to storage/app/backups/ and prune old backups';

    public function handle(): int
    {
        $connectionName = $this->option('connection') ?: config('database.default');
        $connection = config("database.connections.{$connectionName}");

        if (($connection['driver'] ?? null) !== 'mysql') {
            $this->error("backup:database only supports the mysql driver (current default connection \"{$connectionName}\" uses \"{$connection['driver']}\").");

            return self::FAILURE;
        }

        $backupDir = storage_path('app/backups');
        File::ensureDirectoryExists($backupDir);

        $filename = sprintf('%s-%s.sql', $connection['database'], now()->format('Y-m-d_His'));
        $sqlPath = $backupDir.DIRECTORY_SEPARATOR.$filename;
        $gzPath = $sqlPath.'.gz';

        $this->info("Dumping database \"{$connection['database']}\"...");

        try {
            $this->dump($connection, $sqlPath);
            $this->compress($sqlPath, $gzPath);
        } catch (RuntimeException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        } finally {
            if (File::exists($sqlPath)) {
                File::delete($sqlPath);
            }
        }

        $this->info('Backup written to '.$gzPath);

        $pruned = $this->pruneOldBackups($backupDir, (int) $this->option('keep-days'));
        if ($pruned > 0) {
            $this->info("Pruned {$pruned} backup(s) older than {$this->option('keep-days')} day(s).");
        }

        return self::SUCCESS;
    }

    /**
     * @param  array<string, mixed>  $connection
     */
    private function dump(array $connection, string $sqlPath): void
    {
        $result = Process::env(['MYSQL_PWD' => (string) ($connection['password'] ?? '')])
            ->run([
                'mysqldump',
                '--host='.$connection['host'],
                '--port='.$connection['port'],
                '--user='.$connection['username'],
                '--single-transaction',
                '--quick',
                '--result-file='.$sqlPath,
                $connection['database'],
            ]);

        if ($result->failed()) {
            throw new RuntimeException('mysqldump failed: '.$result->errorOutput());
        }
    }

    private function compress(string $sqlPath, string $gzPath): void
    {
        $source = fopen($sqlPath, 'rb');
        $destination = gzopen($gzPath, 'wb9');

        while (! feof($source)) {
            gzwrite($destination, fread($source, 1024 * 512));
        }

        fclose($source);
        gzclose($destination);
    }

    private function pruneOldBackups(string $backupDir, int $keepDays): int
    {
        $cutoff = now()->subDays($keepDays);
        $pruned = 0;

        foreach (File::files($backupDir) as $file) {
            if (now()->createFromTimestamp($file->getMTime())->lt($cutoff)) {
                File::delete($file->getPathname());
                $pruned++;
            }
        }

        return $pruned;
    }
}
