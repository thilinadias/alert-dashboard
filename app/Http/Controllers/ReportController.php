<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Alert;
use App\Models\Client;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index()
    {
        // 1. Alert Volume History (Last 30 Days)
        $dates = collect(range(29, 0))->map(function($days) {
            return Carbon::today()->subDays($days)->format('Y-m-d');
        });

        $volumeStats = Alert::where('created_at', '>=', Carbon::today()->subDays(29))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();

        $lineChartData = $dates->mapWithKeys(function($date) use ($volumeStats) {
            return [$date => $volumeStats[$date] ?? 0];
        });

        // 2. Top Alert Sources (Clients) - Top 5
        $topSources = Alert::select('client_id', DB::raw('count(*) as total'))
            ->whereNotNull('client_id')
            ->groupBy('client_id')
            ->orderByDesc('total')
            ->take(5)
            ->with('client')
            ->get()
            ->map(function($item) {
                return [
                    'name' => $item->client->name,
                    'count' => $item->total
                ];
            });
            
        // 3. SLA Compliance (Met vs Missed)
        // This is tricky because "Met" isn't explicitly stored, but "Missed" is calculated.
        // We'll approximate for Closed alerts.
        // Met: Closed BEFORE deadline.
        // Missed: Closed AFTER deadline.
        $closedAlerts = Alert::where('status', 'closed')->get();
        
        $slaMet = 0;
        $slaMissed = 0;
        
        foreach ($closedAlerts as $alert) {
             $deadline = $alert->getSlaDeadline();
             // If no deadline, we assume MET or N/A. Let's count as Met for now if policy existed.
             if (!$deadline) continue;
             
             if ($alert->closed_at->lte($deadline)) {
                 $slaMet++;
             } else {
                 $slaMissed++;
             }
        }
        
        $pieChartData = [
            'Met' => $slaMet,
            'Missed' => $slaMissed
        ];

        return view('reports.index', compact('lineChartData', 'topSources', 'pieChartData'));
    }

    public function download(Request $request)
    {
        $request->validate([
            'report_type' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $type = $request->report_type;
        $start = Carbon::parse($request->start_date)->startOfDay();
        $end = Carbon::parse($request->end_date)->endOfDay();
        
        $filename = $type . '_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function() use ($type, $start, $end) {
            $file = fopen('php://output', 'w');

            if ($type == 'resolved_alerts') {
                fputcsv($file, ['ID', 'Subject', 'Client', 'Ticket #', 'Created At', 'Resolved At', 'Resolution Notes', 'Resolved By']);
                
                Alert::with(['client', 'lockedBy'])
                    ->whereIn('status', ['resolved', 'closed'])
                    ->whereBetween('closed_at', [$start, $end])
                    ->chunk(100, function($alerts) use ($file) {
                        foreach ($alerts as $alert) {
                            fputcsv($file, [
                                $alert->id,
                                $alert->subject,
                                $alert->client ? $alert->client->name : 'N/A',
                                $alert->ticket_number,
                                $alert->created_at,
                                $alert->closed_at,
                                $alert->resolution_notes,
                                $alert->lockedBy ? $alert->lockedBy->name : 'N/A'
                            ]);
                        }
                    });

            } elseif ($type == 'sla_breached') {
                fputcsv($file, ['ID', 'Subject', 'Client', 'Status', 'Created At', 'SLA Deadline', 'Time Overdue']);
                
                Alert::with(['client'])
                    ->whereBetween('created_at', [$start, $end])
                    ->chunk(100, function($alerts) use ($file) {
                        foreach ($alerts as $alert) {
                            if ($alert->isOverdue()) {
                                fputcsv($file, [
                                    $alert->id,
                                    $alert->subject,
                                    $alert->client ? $alert->client->name : 'N/A',
                                    $alert->status,
                                    $alert->created_at,
                                    $alert->getSlaDeadline() ? $alert->getSlaDeadline()->format('Y-m-d H:i:s') : 'N/A',
                                    'YES' 
                                ]);
                            }
                        }
                    });

            } elseif ($type == 'open_alerts') {
                fputcsv($file, ['ID', 'Subject', 'Client', 'Status', 'Severity', 'Created At', 'Owner']);
                
                Alert::with(['client', 'lockedBy'])
                    ->whereIn('status', ['new', 'open', 'in_progress'])
                    ->whereBetween('created_at', [$start, $end])
                    ->chunk(100, function($alerts) use ($file) {
                        foreach ($alerts as $alert) {
                            fputcsv($file, [
                                $alert->id,
                                $alert->subject,
                                $alert->client ? $alert->client->name : 'N/A',
                                $alert->status,
                                $alert->severity,
                                $alert->created_at,
                                $alert->lockedBy ? $alert->lockedBy->name : 'Unassigned'
                            ]);
                        }
                    });

            } elseif ($type == 'pickup_time') {
                fputcsv($file, ['User ID', 'Name', 'Total Alerts Picked Up', 'Avg Time to Pickup (Minutes)']);
                
                $stats = Alert::whereNotNull('locked_by')
                    ->whereBetween('locked_at', [$start, $end])
                    ->get()
                    ->groupBy('locked_by');

                foreach ($stats as $userId => $alerts) {
                    $user = \App\Models\User::find($userId);
                    if (!$user) continue;

                    $totalTimeSeconds = 0;
                    $count = 0;

                    foreach ($alerts as $alert) {
                        $created = Carbon::parse($alert->created_at);
                        $locked = Carbon::parse($alert->locked_at);
                        $diff = $created->diffInSeconds($locked);
                        $totalTimeSeconds += $diff;
                        $count++;
                    }

                    $avgMinutes = $count > 0 ? round(($totalTimeSeconds / $count) / 60, 2) : 0;

                    fputcsv($file, [
                        $user->id,
                        $user->name,
                        $count,
                        $avgMinutes
                    ]);
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
