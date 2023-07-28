<?php

namespace App\Http\Controllers;

use App\Models\Slider;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard.index', [
            'users' => User::count(),
            'sliders' => Slider::where('status', 'active')->get(),
        ]);
    }
}
