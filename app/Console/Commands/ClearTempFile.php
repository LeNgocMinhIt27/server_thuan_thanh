<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use File;
use Illuminate\Console\Command;
use Storage;

class ClearTempFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:clear-temp-file';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Xóa file rác';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $now = Carbon::now()->subWeek();
        $time = $now;
        $total_size = 0;
        $directories = Storage::disk('temp')->directories();
        foreach ($directories as $directory) {
            $full_path = Storage::disk('temp')->path($directory);
            $folder_size = 0;
            foreach (File::allFiles($full_path) as $file) {
                $folder_size += $file->getSize();
            };
            $file_time = Carbon::createFromTimestamp(File::lastModified($full_path));
            if ($file_time->lte($time)) {
                File::deleteDirectory($full_path);
                $total_size += $folder_size;
            }
        }
    }
}
