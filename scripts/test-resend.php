<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$to = 'troyshet15@gmail.com';
$from = (string) config('mail.from.address');

Illuminate\Support\Facades\Mail::mailer('resend')->raw(
    'Resend test from Laravel at ' . date('c'),
    function ($message) use ($to, $from): void {
        $message->from($from);
        $message->to($to)->subject('Resend test');
    }
);

echo 'sent-from:' . $from . ' sent-to:' . $to . PHP_EOL;