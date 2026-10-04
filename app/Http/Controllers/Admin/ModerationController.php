<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateModerationRequest;
use App\Models\ActivityLog;
use App\Models\Report;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class ModerationController extends Controller
{
    public function index(): Response
    {
        $reports = Report::query()->with(['post:id,title,slug,listing_status,is_flagged', 'user:id,name,email'])->latest()->paginate(15);

        return Inertia::render('Admin/Moderation', ['reports' => $reports]);
    }

    public function update(UpdateModerationRequest $request, Report $report): RedirectResponse
    {
        try {
            DB::transaction(function () use ($request, $report): void {
                $data = $request->validated();
                $report->update([
                    'status' => $data['status'],
                    'moderation_action' => $data['moderation_action'],
                    'moderation_note' => $data['moderation_note'] ?? null,
                    'reviewed_by' => $request->user()->id,
                    'reviewed_at' => now(),
                ]);

                if ($data['moderation_action'] === 'hide') {
                    $report->post()->update(['listing_status' => 'hidden', 'is_flagged' => true]);
                } elseif ($data['moderation_action'] === 'restore') {
                    $report->post()->update(['listing_status' => 'active', 'is_flagged' => false]);
                }

                ActivityLog::create([
                    'user_id' => $request->user()->id,
                    'action' => 'moderation.report_updated',
                    'entity_type' => Report::class,
                    'entity_id' => $report->id,
                    'meta_data' => ['status' => $data['status'], 'action' => $data['moderation_action'], 'post_id' => $report->post_id],
                    'ip_address' => $request->ip(),
                    'created_at' => now(),
                ]);
            });

            return back()->with('status', 'Moderation action saved successfully.');
        } catch (Throwable $exception) {
            Log::error('Moderation action failed', ['report_id' => $report->id, 'user_id' => $request->user()->id, 'exception' => $exception->getMessage()]);
            return back()->withErrors(['moderation' => 'Moderation action could not be saved. Please try again.']);
        }
    }
}
