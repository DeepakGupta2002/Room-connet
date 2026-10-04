<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReportRequest;
use App\Models\Post;
use App\Models\Report;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

class ReportController extends Controller
{
    public function store(StoreReportRequest $request, Post $post): RedirectResponse
    {
        try {
            Report::create([
                'post_id' => $post->id,
                'user_id' => $request->user()->id,
                'reason' => $request->validated('reason'),
                'details' => $request->validated('details'),
                'status' => 'open',
            ]);

            return back()->with('status', 'Report submit ho gayi. Moderation team ise review karegi.');
        } catch (Throwable $exception) {
            Log::warning('Listing report could not be created', ['post_id' => $post->id, 'user_id' => $request->user()->id, 'exception' => $exception->getMessage()]);
            return back()->withErrors(['report' => 'Report submit nahi ho saki. Ho sakta hai aapne ise pehle report kiya ho.']);
        }
    }
}
