<?php

namespace App\Console\Commands;

use App\Constants\TaskType;
use Carbon\Carbon;
use DB;
use Illuminate\Console\Command;

class UpdateTimeOutTask extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:set-task-timeout';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'set task timeout';


    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $default = config('queue.default');
        $max_time_second = config("queue.connections.$default.retry_after");
        $now = Carbon::now();
        $before = $now->subSeconds($max_time_second);
        DB::table("tasks")->where('created_at', '<=', $before)->whereNull('start_at')->update(['updated_at' => $now, 'end_at' => $now, 'start_at' => $now, 'status' => TaskType::ERROR, 'error' => 'Timeout']);
        DB::table("tasks")->where('created_at', '<=', $before)->whereNotIn('status', [TaskType::CANCEL, TaskType::DONE, TaskType::ERROR])->update(['updated_at' => $now, 'end_at' => $now, 'status' => TaskType::ERROR, 'error' => 'Timeout']);
        return 0;
    }
}
