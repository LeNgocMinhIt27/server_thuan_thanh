<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Helpers\System\BackupHelper;
use Carbon\Carbon;
use Exception;
use App\Helpers\System\LogHelper;

class Backup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:backup {--with-file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'backup database and files';

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
        LogHelper::logOption([
            'logname' => 'backup',
            'method' => 'created',
            'description' =>  'backup.created',
            'properties' => [],
            'subject_display' => 'Sao lưu - ' . Carbon::now()->format('Y-m-d-H-i-s'),
            'causer_display' =>  null,
            'trans_properties' => (object)[]
        ]);
        try {
            $options = [
                'with_db' => true,
                'with_file' => $this->option('with-file') || false
            ];
            $cb_show = function ($message) {
                $this->info($message);
            };
            BackupHelper::backup(Carbon::now()->format('Y-m-d-H-i-s'), $cb_show, $options);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
