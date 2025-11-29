<?php

namespace App\Services;

use App\Models\ContentReport;
use App\Models\DmcaTakedown;
use App\Models\Track;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ContentModerationService
{
    /**
     * Submit a content report
     */
    public function submitReport($reporterId, $reportableType, $reportableId, $reason, $description = null)
    {
        $report = ContentReport::create([
            'reporter_id' => $reporterId,
            'reportable_type' => $reportableType,
            'reportable_id' => $reportableId,
            'reason' => $reason,
            'description' => $description,
            'status' => 'pending',
        ]);

        // Notify admins
        $this->notifyAdmins('new_report', $report);

        // Auto-action for severe cases
        if ($reason === 'copyright' || $reason === 'illegal_content') {
            $this->autoReview($report);
        }

        return $report;
    }

    /**
     * Submit DMCA takedown
     */
    public function submitDmcaTakedown(array $data)
    {
        $takedown = DmcaTakedown::create([
            'claimant_name' => $data['claimant_name'],
            'claimant_email' => $data['claimant_email'],
            'claimant_company' => $data['claimant_company'] ?? null,
            'content_type' => $data['content_type'],
            'content_id' => $data['content_id'],
            'original_work_description' => $data['original_work_description'],
            'infringement_description' => $data['infringement_description'],
            'signature' => $data['signature'],
            'good_faith_statement' => $data['good_faith_statement'] ?? false,
            'accuracy_statement' => $data['accuracy_statement'] ?? false,
            'status' => 'pending',
        ]);

        // Notify admins immediately
        $this->notifyAdmins('dmca_takedown', $takedown);

        // Auto-suspend content pending review
        $this->suspendContent($data['content_type'], $data['content_id']);

        return $takedown;
    }

    /**
     * Review a report
     */
    public function reviewReport($reportId, $adminId, $action, $notes = null)
    {
        $report = ContentReport::findOrFail($reportId);

        $report->update([
            'status' => $action === 'approve' ? 'resolved' : 'dismissed',
            'reviewed_by' => $adminId,
            'admin_notes' => $notes,
            'reviewed_at' => now(),
        ]);

        if ($action === 'approve') {
            // Take action on the reported content
            $this->takeAction($report);
        }

        // Notify reporter
        $this->notifyReporter($report);

        return $report;
    }

    /**
     * Process DMCA takedown
     */
    public function processDmcaTakedown($takedownId, $adminId, $action, $notes = null)
    {
        $takedown = DmcaTakedown::findOrFail($takedownId);

        $takedown->update([
            'status' => $action,
            'processed_by' => $adminId,
            'admin_notes' => $notes,
            'processed_at' => now(),
        ]);

        if ($action === 'approved') {
            // Remove content permanently
            $this->removeContent($takedown->content_type, $takedown->content_id);

            // Notify content owner
            $this->notifyContentOwner($takedown);
        } else {
            // Restore content
            $this->restoreContent($takedown->content_type, $takedown->content_id);
        }

        // Notify claimant
        $this->notifyClaimant($takedown);

        return $takedown;
    }

    /**
     * Auto-review for severe cases
     */
    protected function autoReview($report)
    {
        // Check report history
        $reportCount = ContentReport::where('reportable_type', $report->reportable_type)
            ->where('reportable_id', $report->reportable_id)
            ->where('status', 'pending')
            ->count();

        // Auto-suspend if multiple reports
        if ($reportCount >= 3) {
            $this->suspendContent($report->reportable_type, $report->reportable_id);

            Log::warning("Content auto-suspended due to multiple reports", [
                'type' => $report->reportable_type,
                'id' => $report->reportable_id,
            ]);
        }
    }

    /**
     * Take action on reported content
     */
    protected function takeAction($report)
    {
        switch ($report->reason) {
            case 'copyright':
            case 'illegal_content':
                $this->removeContent($report->reportable_type, $report->reportable_id);
                break;

            case 'spam':
            case 'inappropriate':
                $this->suspendContent($report->reportable_type, $report->reportable_id);
                break;

            case 'harassment':
                // Warn user
                $this->warnUser($report);
                break;
        }
    }

    /**
     * Suspend content
     */
    protected function suspendContent($type, $id)
    {
        $model = app($type);
        $content = $model::find($id);

        if ($content) {
            $content->update(['status' => 'suspended']);
        }
    }

    /**
     * Remove content
     */
    protected function removeContent($type, $id)
    {
        $model = app($type);
        $content = $model::find($id);

        if ($content) {
            // Soft delete
            $content->delete();
        }
    }

    /**
     * Restore content
     */
    protected function restoreContent($type, $id)
    {
        $model = app($type);
        $content = $model::withTrashed()->find($id);

        if ($content) {
            $content->restore();
            $content->update(['status' => 'active']);
        }
    }

    /**
     * Warn user
     */
    protected function warnUser($report)
    {
        $content = $report->reportable;

        if ($content && method_exists($content, 'user')) {
            $user = $content->user;

            // Send warning email
            // Mail::to($user->email)->send(new ContentWarningMail($report));
        }
    }

    /**
     * Notify admins
     */
    protected function notifyAdmins($type, $item)
    {
        $admins = User::where('role', 'admin')->get();

        foreach ($admins as $admin) {
            // Create notification
            // $admin->notify(new ContentModerationNotification($type, $item));
        }
    }

    /**
     * Notify reporter
     */
    protected function notifyReporter($report)
    {
        // Mail::to($report->reporter->email)->send(new ReportResolvedMail($report));
    }

    /**
     * Notify content owner
     */
    protected function notifyContentOwner($takedown)
    {
        $content = app($takedown->content_type)::withTrashed()->find($takedown->content_id);

        if ($content && method_exists($content, 'user')) {
            // Mail::to($content->user->email)->send(new DmcaTakedownMail($takedown));
        }
    }

    /**
     * Notify claimant
     */
    protected function notifyClaimant($takedown)
    {
        // Mail::to($takedown->claimant_email)->send(new DmcaProcessedMail($takedown));
    }

    /**
     * Get moderation queue
     */
    public function getModerationQueue($type = 'all')
    {
        $query = ContentReport::with(['reporter', 'reportable'])
            ->where('status', 'pending')
            ->latest();

        if ($type !== 'all') {
            $query->where('reason', $type);
        }

        return $query->paginate(20);
    }

    /**
     * Get DMCA queue
     */
    public function getDmcaQueue()
    {
        return DmcaTakedown::where('status', 'pending')
            ->latest()
            ->paginate(20);
    }
}
