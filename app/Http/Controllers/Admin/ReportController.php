<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentReport;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        $reports = ContentReport::with(['reporter', 'reported', 'reportable'])
            ->latest()
            ->paginate(20);

        return view('admin.moderation.reports', compact('reports'));
    }

    public function show(ContentReport $report)
    {
        $report->load(['reporter', 'reported', 'reportable']);
        return view('admin.moderation.report-show', compact('report'));
    }

    public function resolve(Request $request, ContentReport $report)
    {
        $validated = $request->validate([
            'action' => 'required|in:dismiss,warning,ban,delete_content',
            'notes' => 'nullable|string',
        ]);

        if ($validated['action'] === 'ban') {
            if ($report->reported) {
                $report->reported->update([
                    'is_banned' => true,
                    'banned_until' => null, // Indefinite ban
                ]);
            }
        } elseif ($validated['action'] === 'delete_content') {
            if ($report->reportable) {
                $report->reportable->delete();
            }
        } elseif ($validated['action'] === 'warning') {
            // Logic to send warning (e.g., email or notification)
            // For now, we'll just note it.
        }

        $report->update([
            'status' => 'resolved',
            'admin_notes' => $validated['notes'],
            'resolved_by' => auth()->id(),
            'resolved_at' => now(),
        ]);

        return back()->with('success', 'Report resolved successfully.');
    }
}
