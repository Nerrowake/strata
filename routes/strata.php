<?php

use Illuminate\Support\Facades\Route;

Route::middleware(config('strata.dashboard.middleware', ['web']))
    ->get(config('strata.dashboard.path', 'strata'), function () {
        return view('strata::dashboard');
    })
    ->name('strata.dashboard');
