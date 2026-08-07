<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| This project ships as an API-first ERP (see routes/api.php), intended to
| be consumed by a decoupled SPA or mobile client. Authentication for the
| API itself is handled by Sanctum in AuthController — these web routes
| just serve a landing page and, optionally, a server-rendered dashboard.
*/

Route::get('/', function () {
    return view('welcome');
});
