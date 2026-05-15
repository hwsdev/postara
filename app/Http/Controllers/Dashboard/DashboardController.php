<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Contact;
use App\Models\Email;
use App\Models\EmailEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $workspaceId = session('current_workspace_id');

        $stats = [
            'emails_sent_today'  => Email::where('workspace_id', $workspaceId)
                ->whereDate('created_at', today())
                ->count(),
            'emails_sent_month'  => Email::where('workspace_id', $workspaceId)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'delivered_rate'     => $this->getDeliveredRate($workspaceId),
            'active_campaigns'   => Campaign::where('workspace_id', $workspaceId)
                ->whereIn('status', ['sending', 'scheduled'])
                ->count(),
            'total_contacts'     => Contact::where('workspace_id', $workspaceId)->count(),
            'opens_today'        => EmailEvent::whereHas('email', fn ($q) => $q->where('workspace_id', $workspaceId))
                ->where('type', 'opened')
                ->whereDate('created_at', today())
                ->count(),
        ];

        // 7-day send volume for sparkline
        $chartData = $this->getLast7DaysVolume($workspaceId);

        $recentEmails = Email::where('workspace_id', $workspaceId)
            ->latest()
            ->limit(10)
            ->get();

        // Recent campaigns with stats
        $recentCampaigns = Campaign::where('workspace_id', $workspaceId)
            ->whereIn('status', ['sent', 'sending'])
            ->latest('sent_at')
            ->limit(5)
            ->get();

        return view('dashboard.index', compact('stats', 'recentEmails', 'chartData', 'recentCampaigns'));
    }

    private function getDeliveredRate(int $workspaceId): float
    {
        $total = Email::where('workspace_id', $workspaceId)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        if ($total === 0) {
            return 0;
        }

        $delivered = Email::where('workspace_id', $workspaceId)
            ->where('status', 'delivered')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return round(($delivered / $total) * 100, 1);
    }

    private function getLast7DaysVolume(int $workspaceId): array
    {
        $days = collect(range(6, 0))->map(fn ($i) => now()->subDays($i)->toDateString());

        $counts = Email::where('workspace_id', $workspaceId)
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date');

        return $days->map(fn ($date) => [
            'date'  => $date,
            'label' => \Carbon\Carbon::parse($date)->format('D'),
            'count' => (int) ($counts[$date] ?? 0),
        ])->values()->toArray();
    }
}
