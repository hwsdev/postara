<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Email;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $workspaceId = session('current_workspace_id');

        $stats = [
            'emails_sent_today' => Email::where('workspace_id', $workspaceId)
                ->whereDate('created_at', today())
                ->count(),
            'emails_sent_month' => Email::where('workspace_id', $workspaceId)
                ->whereMonth('created_at', now()->month)
                ->count(),
            'delivered_rate' => $this->getDeliveredRate($workspaceId),
            'active_campaigns' => Campaign::where('workspace_id', $workspaceId)
                ->whereIn('status', ['sending', 'scheduled'])
                ->count(),
        ];

        $recentEmails = Email::where('workspace_id', $workspaceId)
            ->latest()
            ->limit(10)
            ->get();

        return view('dashboard.index', compact('stats', 'recentEmails'));
    }

    private function getDeliveredRate(int $workspaceId): float
    {
        $total = Email::where('workspace_id', $workspaceId)
            ->whereMonth('created_at', now()->month)
            ->count();

        if ($total === 0) {
            return 0;
        }

        $delivered = Email::where('workspace_id', $workspaceId)
            ->where('status', 'delivered')
            ->whereMonth('created_at', now()->month)
            ->count();

        return round(($delivered / $total) * 100, 1);
    }
}
