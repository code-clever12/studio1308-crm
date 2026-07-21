<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

/*
|--------------------------------------------------------------------------
| --connection=mysql is passed explicitly rather than switching
| database.default: RefreshDatabase (auto-applied to Feature/ tests) relies
| on the default connection staying the in-memory sqlite test DB, so
| overriding it mid-test breaks the transaction machinery those other tests
| depend on.
|--------------------------------------------------------------------------
*/

beforeEach(function () {
    config(['database.connections.mysql.host' => '127.0.0.1']);
    config(['database.connections.mysql.port' => 3306]);
    config(['database.connections.mysql.username' => 'root']);
    config(['database.connections.mysql.password' => 'secret']);
    config(['database.connections.mysql.database' => 'studio1308_test_backup']);

    File::deleteDirectory(storage_path('app/backups'));
});

afterEach(function () {
    File::deleteDirectory(storage_path('app/backups'));
});

it('refuses to run against a non-mysql connection', function () {
    $this->artisan('backup:database', ['--connection' => 'sqlite'])
        ->assertFailed()
        ->expectsOutputToContain('only supports the mysql driver');
});

it('shells out to mysqldump and writes a compressed backup file', function () {
    Process::fake(function ($process) {
        // Simulate mysqldump writing its --result-file argument.
        foreach ($process->command as $arg) {
            if (str_starts_with($arg, '--result-file=')) {
                file_put_contents(substr($arg, strlen('--result-file=')), "-- fake dump\nSELECT 1;\n");
            }
        }

        return Process::result('', '', 0);
    });

    $this->artisan('backup:database', ['--connection' => 'mysql'])->assertSuccessful();

    Process::assertRan(function ($process) {
        return $process->command[0] === 'mysqldump'
            && in_array('--host=127.0.0.1', $process->command, true)
            && in_array('studio1308_test_backup', $process->command, true)
            && $process->environment['MYSQL_PWD'] === 'secret';
    });

    $files = File::files(storage_path('app/backups'));
    expect($files)->toHaveCount(1)
        ->and($files[0]->getFilename())->toEndWith('.sql.gz');

    $decompressed = gzdecode(File::get($files[0]->getPathname()));
    expect($decompressed)->toContain('SELECT 1;');
});

it('fails loudly when mysqldump exits non-zero', function () {
    Process::fake(fn () => Process::result('', 'access denied', 1));

    $this->artisan('backup:database', ['--connection' => 'mysql'])->assertFailed();

    expect(File::files(storage_path('app/backups')))->toHaveCount(0);
});

it('prunes backups older than the retention window', function () {
    File::ensureDirectoryExists(storage_path('app/backups'));

    $old = storage_path('app/backups/old.sql.gz');
    $recent = storage_path('app/backups/recent.sql.gz');
    File::put($old, 'x');
    File::put($recent, 'x');
    touch($old, now()->subDays(30)->timestamp);
    touch($recent, now()->subDays(1)->timestamp);

    Process::fake(function ($process) {
        foreach ($process->command as $arg) {
            if (str_starts_with($arg, '--result-file=')) {
                file_put_contents(substr($arg, strlen('--result-file=')), 'dump');
            }
        }

        return Process::result('', '', 0);
    });

    $this->artisan('backup:database', ['--connection' => 'mysql', '--keep-days' => 14])->assertSuccessful();

    expect(File::exists($old))->toBeFalse()
        ->and(File::exists($recent))->toBeTrue();
});
