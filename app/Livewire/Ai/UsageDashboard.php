<?php

namespace App\Livewire\Ai;

use App\Models\AiUsageLog;
use App\Services\Ai\UsageTracker;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('AI Usage')]
class UsageDashboard extends Component
{
    use AuthorizesRequests;

    public string $period = 'day';

    public function render(UsageTracker $tracker): View
    {
        $user    = auth()->user();
        $summary = $tracker->summary($user, $this->period);

        $recentLogs = AiUsageLog::query()
            ->where('user_id', $user->id)
            ->with('prompt')
            ->latest()
            ->limit(50)
            ->get();

        return view('livewire.ai.usage-dashboard', [
            'summary'    => $summary,
            'recentLogs' => $recentLogs,
        ]);
    }
}
