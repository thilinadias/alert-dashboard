<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Alert;
use App\Models\AlertHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $userId = $user->id;
        $isAnalyst = !$user->hasRole('admin'); // Treat anyone not admin as analyst for now

        // 1. KPIs
        $assignedToMe = Alert::where('locked_by', $userId)
            ->where('status', '!=', 'closed')
            ->count();

        $resolvedToday = Alert::where('closed_by', $userId)
            ->whereDate('closed_at', Carbon::today())
            ->count();
            
        // SLA Breaches
        // Optimization: Eager load client.slaPolicy
        $openAlertsQuery = Alert::with('client.slaPolicy')
            ->where('status', '!=', 'closed');
            
        if ($isAnalyst) {
            // Analysts only see their own breaches in the KPI card
            $openAlertsQuery->where('locked_by', $userId);
        }

        $allOpenAlerts = $openAlertsQuery->get();
            
        $slaBreaches = $allOpenAlerts->filter(function($alert) {
            return $alert->isOverdue();
        })->count();

        // Avg Resolution Time (Today) - Always personal
        $resolvedTodayAlerts = Alert::where('closed_by', $userId)
            ->whereDate('closed_at', Carbon::today())
            ->get();
            
        $totalMinutes = $resolvedTodayAlerts->sum(function($alert) {
            return $alert->closed_at->diffInMinutes($alert->created_at);
        });
        
        $avgResolutionTime = $resolvedTodayAlerts->count() > 0 
            ? round($totalMinutes / $resolvedTodayAlerts->count()) . ' mins'
            : '--';

        // 2. Charts Data
        
        // Distribution of Open Alerts
        // Admin: System-wide
        // Analyst: My Workload
        $severityQuery = Alert::where('status', '!=', 'closed');
        if ($isAnalyst) {
            $severityQuery->where('locked_by', $userId);
        }

        $severityCounts = $severityQuery->select('severity', DB::raw('count(*) as total'))
            ->groupBy('severity')
            ->pluck('total', 'severity')
            ->toArray();
            
        $pieChartData = [
            'critical' => $severityCounts['critical'] ?? 0,
            'warning' => $severityCounts['warning'] ?? 0,
            'info' => $severityCounts['info'] ?? 0,
            'default' => $severityCounts['default'] ?? 0,
        ];

        // Weekly Performance (Personal for everyone usually)
        $dates = collect(range(6, 0))->map(function($days) {
            return Carbon::today()->subDays($days)->format('Y-m-d');
        });
        
        $weeklyStats = Alert::where('closed_by', $userId)
            ->where('closed_at', '>=', Carbon::today()->subDays(6))
            ->select(DB::raw('DATE(closed_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();
            
        $barChartData = $dates->mapWithKeys(function($date) use ($weeklyStats) {
            return [$date => $weeklyStats[$date] ?? 0];
        });

        // 3. Feeds
        $recentActivity = AlertHistory::where('user_id', $userId)
            ->with(['alert'])
            ->latest()
            ->take(5)
            ->get();

        $urgentAlerts = Alert::where('locked_by', $userId)
            ->where('status', '!=', 'closed')
            ->orderByRaw("CASE WHEN severity='critical' THEN 0 WHEN severity='warning' THEN 1 ELSE 2 END")
            ->orderBy('created_at', 'asc')
            ->take(5)
            ->get();

        // 4. Response Time Leaderboard (TTA) - Admin Only
        $ttaLeaderboard = collect();
        if (!$isAnalyst) {
            $ttaLeaderboard = \App\Models\User::with(['lockedAlerts' => function($query) {
                    $query->whereNotNull('locked_at')
                          ->where('created_at', '>=', Carbon::now()->subDays(7));
                }])
                ->get()
                ->map(function($user) {
                    $count = $user->lockedAlerts->count();
                    if ($count === 0) return null;

                    $avgMinutes = $user->lockedAlerts->avg(function($alert) {
                        return $alert->locked_at->diffInMinutes($alert->created_at);
                    });

                    return [
                        'name' => $user->name,
                        'avg_tta' => round($avgMinutes),
                        'count' => $count
                    ];
                })
                ->filter() 
                ->sortByDesc('avg_tta') 
                ->values()
                ->take(5);
        }

        $view = $isAnalyst ? 'dashboard-analyst' : 'dashboard';

        return view($view, compact(
            'assignedToMe', 'resolvedToday', 'slaBreaches', 'avgResolutionTime',
            'pieChartData', 'barChartData',
            'recentActivity', 'urgentAlerts', 'ttaLeaderboard'
        ));
    }
}
