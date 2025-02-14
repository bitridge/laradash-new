<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Project;
use App\Models\SeoLog;
use App\Models\Report;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Get counts for key metrics
        $metrics = [
            'total_customers' => Customer::count(),
            'total_projects' => Project::count(),
            'total_seo_logs' => SeoLog::count(),
            'total_reports' => Report::count(),
            'active_projects' => Project::where('status', 'active')->count(),
        ];

        // Get recent SEO logs
        $recentSeoLogs = SeoLog::with(['project', 'user'])
            ->latest()
            ->take(5)
            ->get();

        // Get recent reports
        $recentReports = Report::with(['project', 'generatedBy'])
            ->latest('generated_at')
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'metrics',
            'recentSeoLogs',
            'recentReports'
        ));
    }
} 