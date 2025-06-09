<?php

namespace App\Http\Controllers;

use App\Models\Scheme;
use App\Models\DataIot;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        // Get all schemes belonging to current user
        $schemes = Scheme::where('user_id', auth()->id())
            ->where('deleted', false)
            ->withCount('data')
            ->get();
        
        // Calculate total data points
        $schemeIds = $schemes->pluck('id')->toArray();
        $totalDataPoints = DataIot::whereIn('scheme_id', $schemeIds)
            ->where('deleted', false)
            ->count();
        
        // Get last updated timestamp
        $lastUpdated = DataIot::whereIn('scheme_id', $schemeIds)
            ->where('deleted', false)
            ->latest('created_at')
            ->first()?->created_at;
        
        // Get data points by month for chart
        $dataByMonth = DataIot::whereIn('scheme_id', $schemeIds)
            ->where('deleted', false)
            ->select(DB::raw("date_trunc('month', created_at)::date as month"), DB::raw('COUNT(*) as count'))
            ->groupBy('month')
            ->orderBy('month')
            ->get();
        
        // Get initial data for first scheme (if any)
        $initialData = [];
        $initialScheme = null;
        
        if ($schemes->isNotEmpty()) {
            $initialScheme = $schemes->first();
            
            // Get last 7 days of data for the initial scheme
            $initialData = DataIot::where('scheme_id', $initialScheme->id)
                ->where('deleted', false)
                ->where('created_at', '>=', Carbon::now()->subDays(7))
                ->orderBy('created_at', 'desc')
                ->get();
        }
        
        return view('access.user.user-home', compact(
            'schemes', 
            'initialData', 
            'initialScheme',
            'totalDataPoints',
            'lastUpdated',
            'dataByMonth'
        ));
    }
}