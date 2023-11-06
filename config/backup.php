<?php

return [

    'backup' => [

        /*
         * The name of this application. You can use this name to monitor
         * the backups.
         */
        'name' => env('APP_NAME', 'laravel-backup'),
        'database_dump_file_extension' => 'backup',
        'source' => [

            'files' => [

                /*
                 * The list of directories and files that will be included in the backup.
                 */
                'include' => [
                    base_path('storage/app'),
                    base_path('public/base-map/map'),
                ],
            ],
        ],

        'destination' => [

            /*
             * The disk names on which the backups will be stored.
             * The backup folder must not be in the storage/app folder or it will cause a loop
             */
            'disks' => [
                'backup',
            ],
        ],

        /*
         * The directory where the temporary files will be stored.
         */
    ],

];
