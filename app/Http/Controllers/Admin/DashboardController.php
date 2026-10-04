<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactUnlock;
use App\Models\Donation;
use App\Models\Post;
use App\Models\Report;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class DashboardController extends Controller
{
    public function index(): Response
    {
        try {
            return Inertia::render('Admin/Dashboard', [
                'metrics' => [
                    'total_users' => User::count(),
                    'active_listings' => Post::where('listing_status', 'active')->whereDate('expires_at', '>=', today())->count(),
                    'open_reports' => Report::whereIn('status', ['open', 'reviewing'])->count(),
                    'today_unlocks' => ContactUnlock::whereDate('unlocked_at', today())->count(),
                    'verified_donations' => Donation::where('status', 'verified')->sum('amount'),
                ],
            ]);
        } catch (Throwable $exception) {
            Log::error('Admin dashboard metrics failed', ['user_id' => auth()->id(), 'exception' => $exception->getMessage()]);
            return Inertia::render('Admin/Dashboard', ['metrics' => null, 'error' => 'Dashboard metrics abhi load nahi ho paaye.']);
        }
    }
}
