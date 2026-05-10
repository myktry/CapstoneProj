<?php

return [
    'layout' => 'components.layouts.app',
    'legacy_model_binding' => false,
    'temporary_file_upload' => [
        'disk' => 'local', // Store in local disk
        'directory' => 'livewire-tmp',
        'rules' => 'file|max:102400', // 100MB max
        'middleware' => 'throttle:30,1',
        'temporary_files_lifetime_in_minutes' => 60,
    ],
    'render_on_redirect' => false,
];
