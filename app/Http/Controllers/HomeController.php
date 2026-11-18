<?php

namespace App\Http\Controllers;

use App\Models\Film;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Home', [
            'films' => Film::query()
                ->currentlyScheduled()
                ->orderBy('title')
                ->limit(8)
                ->get(),
        ]);
    }
}
