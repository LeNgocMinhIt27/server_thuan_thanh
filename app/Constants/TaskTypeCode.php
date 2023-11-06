<?php

namespace App\Constants;

final class TaskTypeCode
{
    public const RESTORE = 'restore';
    public const BACKUP = 'backup';
    public const ANALYZE = 'create-analyst-data';
    public const GROUP = 'create-group-data';
    public const PRINT = 'print';
    public const IMPORT = 'import-group-data';
    public const DOWNLOAD = 'download-group-data';
    public const CREATE_GROUP_REQUEST = 'create-group-data-request';
}
