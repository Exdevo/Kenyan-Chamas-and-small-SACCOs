<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment('Small steps make strong communities.');
})->purpose('Display an inspiring quote');
