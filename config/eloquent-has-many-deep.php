<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | IDE Helper
    |--------------------------------------------------------------------------
    |
    | Automatically register the model hook to receive correct type hints
    |
    */
    'ide_helper_enabled' => env('ELOQUENT_HAS_MANY_DEEP_IDE_HELPER_ENABLED', true),
];
