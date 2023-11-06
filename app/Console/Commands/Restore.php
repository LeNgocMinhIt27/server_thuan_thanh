<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Helpers\System\RestoreHelper;
use Illuminate\Support\Facades\Artisan;
use App\Helpers\System\BackupHelper;
use Exception;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use App\Helpers\System\LogHelper;

class Restore extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:restore {file} {--with-file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restore database and files from file backup';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $options = [
            'with_db' => true,
            'with_file' => $this->option('with-file') || false
        ];
        $disk = Storage::disk(config('backup.backup.destination.disks')[0]);
        $time = Carbon::now();
        $cb_show = function ($message) {
            $this->info($message);
        };
        $path = config('backup.backup.name') . '/' . $this->argument('file');
        if (!$disk->exists($path)) {
            throw new Exception("Không tồn tại file backup");
        }
        $backup = null;
        try {
            Artisan::call('down');
            $cb_show('Shut down server');
            $cb_show('Create backup for current data');
            $backup = BackupHelper::backup($time->format('Y-m-d-H-i-s'), $cb_show, $options);

            RestoreHelper::restore($disk->path($path), $cb_show, $options);
            LogHelper::logOption([
                'logname' => 'restore',
                'method' => 'created',
                'description' =>  'restore.created',
                'properties' => [],
                'subject_display' => 'Khôi phục file ' . $this->argument('file'),
                'causer_display' =>  null,
                'trans_properties' => (object)[]
            ]);
            if (isset($backup)) {
                File::delete($backup['full_path']);
            }
            Artisan::call('up');
            $cb_show('Turn on server');
        } catch (Exception $e) {
            $cb_show('Restore error');
            $this->error($e->getMessage());
            $cb_show('Restore previous backup');
            if (isset($backup)) {
                RestoreHelper::restore($backup['full_path'], $cb_show, $options);
            }
            LogHelper::logOption([
                'logname' => 'restore',
                'method' => 'created',
                'description' =>  'restore.created',
                'properties' => [],
                'subject_display' => 'Khôi phục file ' . $this->argument('file'),
                'causer_display' =>  null,
                'trans_properties' => (object)[]
            ]);
            Artisan::call('up');
            $cb_show('Turn on server');
        }
    }
}
