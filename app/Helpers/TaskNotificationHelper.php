<?php

namespace App\Helpers;

use App\Notifications\General\ContentNotification;
use App\Notifications\General\GeneralNotification;
use App\Constants\TaskType;
use App\Constants\Task;
use App\Constants\NotificationType;
use App\Constants\TaskTypeCode;

class TaskNotificationHelper
{
    public static function notificationHandler($task)
    {
        if (empty($task->user)) {
            return;
        }
        $content_notification = ContentNotification::create()
            ->setTitle(NotificationType::USER_TASK_PROCESSS["title"])
            ->setSubject(NotificationType::USER_TASK_PROCESSS["subject"])
            ->setContent("Task {$task->type->title} {$task->name} is <b>{$task->status}</b>!")
            ->setNotification("")
            ->setUrl("");
        $colorStatus = "";
        switch ($task->status) {
            case TaskType::CREATED:
                # code...
                $status = Task::CREATED;
                $colorStatus = "#898989";
                break;
            case TaskType::PENDING:
                # code...
                $status = Task::PENDING;
                $colorStatus = "#00A3FF";
                break;
            case TaskType::DONE:
                # code...
                $status = Task::DONE;
                $colorStatus = "#4CAF50";
                if ($task->type->code == TaskTypeCode::BACKUP) {
                    $content_notification->setActionUrl(([
                        "type" => NotificationType::BACKUP_REDIRECT,
                        "id" => null
                    ]));
                } elseif ($task->type->code == TaskTypeCode::PRINT) {
                    $content_notification->setActionUrl(([
                        "type" => NotificationType::PRINT_REDIRECT,
                        "id" => null
                    ]));
                } elseif ($task->type->code == TaskTypeCode::GROUP) {
                    $content_notification->setActionUrl(([
                        "type" => NotificationType::GROUP_REDIRECT,
                        "id" => null
                    ]));
                }
                break;
            case TaskType::ERROR:
                # code...
                $status = Task::ERROR;
                $colorStatus = "#FF5252";
                if ($task->type->code == TaskTypeCode::PRINT) {
                    $content_notification->setActionUrl(([
                        "type" => NotificationType::PRINT_REDIRECT,
                        "id" => null
                    ]));
                } else {
                    $content_notification->setActionUrl(([
                        "type" => NotificationType::TASK_REDIRECT,
                        "id" => null
                    ]));
                }
                break;
            case TaskType::CANCEL:
                # code...
                $status = Task::CANCEL;
                $colorStatus = "#FF5252";
                break;
            default:
                # code...
                break;
        }
        $content_notification->setContent("Tiến trình {$task->name} <b style=\"color:$colorStatus\">{$status}</b>!");
        if (isset($task->user))
            $task->user->notify(new GeneralNotification($content_notification));
    }
}
