<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AlertController extends Controller
{
    /**
     * Display a listing of the alerts.
     */
    public function index(Request $request)
    {
        $query = Alert::with(['client', 'lockedBy'])
            ->orderByRaw("CASE WHEN status IN ('new', 'open') THEN 0 ELSE 1 END") // Open alerts first
            ->orderBy('created_at', 'desc');

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('ticket_number', 'like', "%{$search}%");
            });
        }

        // Status Filter
        if ($request->has('status')) {
            if ($request->status != '') {
                $query->where('status', $request->status);
            }
        } else {
            // Default view: Hide closed alerts UNSLESS we are filtering by date (e.g. resolved today)
            // or explicitly asking for closed via SLA filter (though SLA usually implies open)
            if (!$request->has('date')) {
                $query->where('status', '!=', 'closed');
            }
        }

        // Severity Filter
        if ($request->has('severity') && $request->severity != '') {
            $query->where('severity', $request->severity);
        }

        // SLA Filter
        if ($request->has('sla') && $request->sla == 'overdue') {
            // This is a bit complex as SLA is dynamic. 
            // We can't easily do this in SQL without storing the deadline.
            // For now, let's filter the collection if the dataset is small, 
            // OR (better) since we want to paginate, we should rely on a stored deadline if possible.
            // CURRENT LIMITATION: "isOverdue" is computed. 
            // fallback: We will just show ALL open alerts if the user asks for overdue, 
            // and rely on the UI to highlight them? No, that defeats the purpose.
            
            // Fix: We need to filter by status != closed/resolved and check created_at.
            // Since deadline depends on client policy, we can't do a simple SQL where.
            // Hack for now: Get IDs of overdue alerts (this might be slow for massive data but fine for <1000)
            $overdueIds = Alert::where('status', '!=', 'closed')
                ->where('status', '!=', 'resolved')
                ->get()
                ->filter(function($alert) {
                    return $alert->isOverdue();
                })
                ->pluck('id');
            
            $query->whereIn('id', $overdueIds);
        }

        // Date Filter (e.g. Resolved Today)
        if ($request->has('date') && $request->date == 'today') {
            // Usually combined with status=resolved or closed
            $query->whereDate('closed_at', now());
        }

        $alerts = $query->paginate(20);

        return view('alerts.index', compact('alerts'));
    }

    /**
     * Display critical alerts dashboard.
     */
    public function critical(Request $request)
    {
        // Force severity to critical
        $request->merge(['severity' => 'critical']);
        
        $alerts = Alert::with(['client', 'lockedBy'])
            ->where('severity', 'critical')
            ->orderByRaw("CASE WHEN status IN ('new', 'open') THEN 0 ELSE 1 END")
            ->orderBy('created_at', 'desc');

        // Apply same search/filters if needed
        if ($request->has('search')) {
            $search = $request->search;
            $alerts->where(function($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('ticket_number', 'like', "%{$search}%");
            });
        }
        
        if ($request->has('status')) {
            if ($request->status != '') {
                $alerts->where('status', $request->status);
            }
        } else {
            // Default view: Hide closed alerts
            $alerts->where('status', '!=', 'closed');
        }

        $alerts = $alerts->paginate(20);
        $dashboardTitle = 'Critical Alert Dashboard';
        
        return view('alerts.index', compact('alerts', 'dashboardTitle'));
    }

    /**
     * Display default alerts dashboard.
     */
    public function default(Request $request)
    {
        // Force severity to non-critical (warning, info, default)
        $request->merge(['severity' => 'default']); // Just for UI state
        
        $alerts = Alert::with(['client', 'lockedBy'])
            ->where('severity', '!=', 'critical')
            ->orderByRaw("CASE WHEN status IN ('new', 'open') THEN 0 ELSE 1 END")
            ->orderBy('created_at', 'desc');

        if ($request->has('search')) {
            $search = $request->search;
            $alerts->where(function($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('ticket_number', 'like', "%{$search}%");
            });
        }
        
        if ($request->has('status')) {
            if ($request->status != '') {
                $alerts->where('status', $request->status);
            }
        } else {
            // Default view: Hide closed alerts
            $alerts->where('status', '!=', 'closed');
        }

        $alerts = $alerts->paginate(20);
        $dashboardTitle = 'Default Alert Dashboard';
        
        return view('alerts.index', compact('alerts', 'dashboardTitle'));
    }

    /**
     * Display alerts locked by the current user.
     */
    public function myAlerts(Request $request)
    {
        $alerts = Alert::with(['client', 'lockedBy'])
            ->where('locked_by', Auth::id())
            ->orderByRaw("CASE WHEN status IN ('open') THEN 0 ELSE 1 END")
            ->orderBy('created_at', 'desc');

        if ($request->has('search')) {
            $search = $request->search;
            $alerts->where(function($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('ticket_number', 'like', "%{$search}%");
            });
        }
        
        if ($request->has('status')) {
            if ($request->status != '') {
                $alerts->where('status', $request->status);
            }
        } else {
            // Default view: Hide closed alerts
            $alerts->where('status', '!=', 'closed');
        }

        $alerts = $alerts->paginate(20);
        $dashboardTitle = 'My Alerts';
        
        return view('alerts.index', compact('alerts', 'dashboardTitle'));
    }

    /**
     * Display the specified resource (for AJAX).
     */
    public function show(Alert $alert)
    {
        return response()->json([
            'html' => view('alerts.partials.details', compact('alert'))->render()
        ]);
    }

    /**
     * Take ownership of an alert (lock it).
     */
    public function take(Alert $alert)
    {
        if ($alert->locked_by && $alert->locked_by != Auth::id()) {
            return response()->json(['error' => 'Alert is already locked by another user.'], 403);
        }

        $alert->update([
            'locked_by' => Auth::id(),
            'locked_at' => now(),
            'status' => 'open' // Automatically set to open when taken
        ]);

        return response()->json(['success' => true, 'user' => Auth::user()->name]);
    }

    /**
     * Release ownership of an alert (unlock it).
     */
    public function release(Alert $alert)
    {
        if ($alert->locked_by != Auth::id()) {
            return response()->json(['error' => 'You do not own this alert.'], 403);
        }

        $alert->update([
            'locked_by' => null,
            'locked_at' => null
        ]);

        return response()->json(['success' => true]);
    }
    /**
     * Mark an alert as resolved.
     */
    public function resolve(Request $request, Alert $alert)
    {
        if ($alert->locked_by != Auth::id()) {
            return response()->json(['error' => 'You do not own this alert.'], 403);
        }

        $request->validate([
            'resolution_notes' => 'required|string',
            'ticket_number' => 'nullable|string'
        ]);

        $alert->update([
            'status' => 'resolved',
            'resolution_notes' => $request->resolution_notes,
            'ticket_number' => $request->ticket_number ?? $alert->ticket_number
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Close an alert (final state).
     */
    public function close(Alert $alert)
    {
        // Allow owner or admin (todo: admin check)
        if ($alert->locked_by != Auth::id() && !Auth::user()->hasRole('admin')) {
             return response()->json(['error' => 'Unauthorized.'], 403);
        }

        $alert->update([
            'status' => 'closed',
            'closed_at' => now(),
            'closed_by' => Auth::id()
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Reopen a closed or resolved alert.
     */
    public function reopen(Alert $alert)
    {
        $alert->update([
            'status' => 'open',
            'closed_at' => null,
            'closed_by' => null,
            // Keep resolution notes for history context? Or clear them?
            // Let's keep them but maybe append "Reopened"? For now just leave as is.
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Resolve multiple alerts at once.
     */
    public function bulkResolve(Request $request)
    {
        $request->validate([
            'alert_ids' => 'required|array',
            'alert_ids.*' => 'exists:alerts,id',
            'resolution_notes' => 'required|string',
            'ticket_number' => 'nullable|string'
        ]);

        // Fetch alerts to update
        $alerts = Alert::whereIn('id', $request->alert_ids)->get();
        $count = 0;

        foreach ($alerts as $alert) {
            // Check permissions? For now, allow logged in users to resolve any alert they manipulate
            // Or restrict to 'open', 'new', 'in_progress' and not 'closed'.
            if ($alert->status === 'closed') {
                continue;
            }

            $alert->update([
                'status' => 'closed', // User asked to "close them out"
                'locked_by' => Auth::id(), // Take ownership if not already
                'resolution_notes' => $request->resolution_notes,
                'ticket_number' => $request->ticket_number ?? $alert->ticket_number,
                'closed_at' => now(),
                'closed_by' => Auth::id()
            ]);
            $count++;
        }

        return response()->json(['success' => true, 'count' => $count]);
    }
    /**
     * Export alerts to CSV based on current filters.
     */
    public function export(Request $request)
    {
        // Reuse the index query logic (duplicated for now to ensure exact match)
        // Ideally should extract to a repository or private method
        $query = Alert::with(['client', 'lockedBy'])
            ->orderByRaw("CASE WHEN status IN ('new', 'open') THEN 0 ELSE 1 END")
            ->orderBy('created_at', 'desc');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('ticket_number', 'like', "%{$search}%");
            });
        }

        if ($request->has('status')) {
            if ($request->status != '') {
                $query->where('status', $request->status);
            }
        } else {
            if (!$request->has('date')) {
                $query->where('status', '!=', 'closed');
            }
        }

        if ($request->has('severity') && $request->severity != '') {
            $query->where('severity', $request->severity);
        }
        
        // SLA Filter
        if ($request->has('sla') && $request->sla == 'overdue') {
             $overdueIds = Alert::where('status', '!=', 'closed')
                ->where('status', '!=', 'resolved')
                ->get()
                ->filter(function($alert) {
                    return $alert->isOverdue();
                })
                ->pluck('id');
            $query->whereIn('id', $overdueIds);
        }

        // Date Filter
        if ($request->has('date') && $request->date == 'today') {
            $query->whereDate('closed_at', now());
        }

        $filename = 'alerts_export_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function() use ($query) {
            $file = fopen('php://output', 'w');
            
            // CSV Header
            fputcsv($file, ['ID', 'Subject', 'Severity', 'Status', 'Client', 'Ticket #', 'Created At', 'Resovled At', 'Resolution Notes', 'SLA Deadline']);

            // Stream rows
            $query->chunk(100, function($alerts) use ($file) {
                foreach ($alerts as $alert) {
                    fputcsv($file, [
                        $alert->id,
                        $alert->subject,
                        $alert->severity,
                        $alert->status,
                        $alert->client ? $alert->client->name : 'N/A',
                        $alert->ticket_number,
                        $alert->created_at->format('Y-m-d H:i:s'),
                        $alert->closed_at ? $alert->closed_at->format('Y-m-d H:i:s') : '',
                        $alert->resolution_notes,
                        $alert->getSlaDeadline() ? $alert->getSlaDeadline()->format('Y-m-d H:i:s') : 'N/A'
                    ]);
                }
            });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Manually trigger alert fetching.
     */
    public function sync(Request $request)
    {
        try {
            \Illuminate\Support\Facades\Artisan::call('alerts:fetch');
            
            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Alerts synchronized successfully.']);
            }
            
            return back()->with('success', 'Alerts synchronized successfully.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Sync failed: ' . $e->getMessage()], 500);
            }
            return back()->with('error', 'Sync failed: ' . $e->getMessage());
        }
    }
}
