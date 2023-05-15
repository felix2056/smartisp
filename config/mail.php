
        <?php
        return [
        'driver' => 'smtp',
        'host' => 'mail.hassler.ec',
        'port' =>465,
        'from' => [
        'address' => 'kleine@hassler.ec',
        'name' => 'SmartISP',
        ],
        'encryption' => 'ssl',
        'username' => 'kleine@hassler.ec',
        'password' => '123456',
        'sendmail' => '/usr/sbin/sendmail -bs',
        'markdown' => [
        'theme' => 'default',

        'paths' => [
        resource_path('views/vendor/mail'),
        ],
        ],
        'log_channel' => env('MAIL_LOG_CHANNEL'),
        ];
        