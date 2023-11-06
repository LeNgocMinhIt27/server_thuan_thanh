<?php

namespace App\Jobs;

use App\Constants\TaskType;
use App\Models\Task\Task;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Storage;
use App\Helpers\TaskNotificationHelper;

class AJob implements ShouldQueue
{
    public $tries = 1;
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $task;
    /**
     * @var callable
     */
    protected $cb_show;
    protected $type;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Task $task)
    {
        $this->task = $task;
        $cb_show =  function ($message) use ($task) {
            $task_id = $task->getKey();
            $type = $this->type;
            if (is_string($message)) {
                $message = "[$type][$task_id] Message: $message";
                error_log($message);
                Storage::disk('task')->append($task_id . '.log', $message);
            }
        };
        $task_id = $task->getKey();
        $cb_show('task-' . $task_id);
        TaskNotificationHelper::notificationHandler($task);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function cb_handle($cb)
    {
        $this->task->status = TaskType::PENDING;
        $this->task->start_at = Carbon::now();
        $jobID = $this->job->getJobId();
        $this->task->queue_name = $jobID;
        $this->task->save();
        $task = $this->task;
        $cb_show =  function ($message) use ($task) {
            $task_id = $task->getKey();
            $type = $this->type;
            if (is_string($message)) {
                $message = "[$type][$task_id] Message: $message";
                error_log($message);
                Storage::disk('task')->append($task_id . '.log', $message);
            }
        };
        $cb_show('job-' . $jobID);
        $cb_show("---- time start: " . Carbon::now()->format('Y-m-d H:i:s') . "----");
        $this->cb_show = $cb_show;
        try {
            $cb_show("---- TASK START ----");
            $cb($cb_show);
            $this->task->status = TaskType::DONE;
            $this->task->end_at = Carbon::now();
            $this->task->save();

            TaskNotificationHelper::notificationHandler($task);

            $cb_show("---- time end: " . Carbon::now()->format('Y-m-d H:i:s') . "----");
            $cb_show("---- TASK DONE ----");
        } catch (\Exception $e) {
            $this->failed($e);
        }
    }
    public function failed($exception)
    {
        $this->task->status = TaskType::ERROR;
        $this->task->end_at = Carbon::now();
        $this->task->error = $exception->getMessage();
        $this->task->save();
        TaskNotificationHelper::notificationHandler($this->task);
        Storage::disk('task')->append($this->task->id . '.log', $exception);
        ($this->cb_show)("---- time end: " . Carbon::now()->format('Y-m-d H:i:s') . "----");
        ($this->cb_show)("---- TASK ERROR ----");
    }
}
