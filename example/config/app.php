<?php

return [
        'commands_paths' => [
                __DIR__ . '/../src/Commands' => 'App\\Commands',
        ],
        'schedule' => [
                'greet Smerteliko --yell' => '*/2 * * * *',
                'greet Admin'             => '0 12 * * *',
        ],
];