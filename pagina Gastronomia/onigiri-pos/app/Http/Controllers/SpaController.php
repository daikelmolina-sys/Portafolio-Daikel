<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class SpaController extends Controller
{
    /**
     * Sirve el shell HTML de la SPA React.
     * Todas las rutas del frontend caen aquí; React Router maneja la navegación.
     */
    public function __invoke(): View
    {
        return view('app');
    }
}
