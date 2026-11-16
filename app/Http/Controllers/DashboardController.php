<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Admin\StatisticsController;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        protected StatisticsController $statistics,
    ) {}

    public function index(Request $request): Response
    {
        $statistics = $request->user()?->canManageUsers()
            ? $this->statistics->collect($request)
            : null;

        return Inertia::render('Dashboard', [
            'statistics' => $statistics,
        ]);
    }
}
