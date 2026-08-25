<?php

use App\Http\Controllers\SpaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — Laravel sirve el shell SPA para todas las rutas del frontend
|--------------------------------------------------------------------------
|
| React Router maneja /tienda, /pos, /admin, etc.
| Laravel solo necesita servir el index.blade.php para cualquier path.
|
*/

// Todas las rutas van a la SPA de React
Route::get('/{any?}', SpaController::class)->where('any', '.*');
