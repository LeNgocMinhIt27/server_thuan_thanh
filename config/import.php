<?php

return [

    'import' => [

        'name' => env('APP_NAME', 'laravel-import'),

        'destination' => [

            /*
             * The disk names on which the imports will be stored.
             * The import folder must not be in the storage/app folder or it will cause a loop
             */
            'disks' => [
                'import',
            ],
        ],

        /*
         * The directory where the temporary files will be stored.
         */
    ],

];
