<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return 'PHP container: ' . gethostname();
});
