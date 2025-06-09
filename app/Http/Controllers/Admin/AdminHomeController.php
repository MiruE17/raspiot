<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Scheme;
use App\Models\DataIot;

class AdminHomeController extends Controller
{
    public function index()
    {
        $stats = [
            'users' => User::count(),
            'schemes' => Scheme::count(),
            'data_points' => DataIot::count(),
            'data_by_month' => DataIot::selectRaw('DATE_TRUNC(\'month\', created_at) as month, count(*) as count')
                ->groupBy('month')
                ->orderBy('month')
                ->get()
        ];

        return view('access.admin.admin-home', compact('stats'));
    }
}
