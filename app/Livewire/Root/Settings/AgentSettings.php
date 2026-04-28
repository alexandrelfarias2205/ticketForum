<?php declare(strict_types=1);

namespace App\Livewire\Root\Settings;

use App\Models\PlatformSetting;
use Illuminate\Contracts\View\View;
use Livewire\Component;

final class AgentSettings extends Component
{
    public string $aiModel = 'claude-sonnet-4-6';
    public int $riskThreshold = 70;
    public float $similarityThreshold = 0.85;
    public int $maxRetries = 3;
    public bool $agentEnabled = true;

    public function mount(): void
    {
        abort_unless(auth()->user()->isRoot(), 403);

        $this->aiModel             = (string)  PlatformSetting::get('agent.aiModel', 'claude-sonnet-4-6');
        $this->riskThreshold       = (int)     PlatformSetting::get('agent.riskThreshold', 70);
        $this->similarityThreshold = (float)   PlatformSetting::get('agent.similarityThreshold', 0.85);
        $this->maxRetries          = (int)     PlatformSetting::get('agent.maxRetries', 3);
        $this->agentEnabled        = (bool)    PlatformSetting::get('agent.agentEnabled', true);
    }

    public function save(): void
    {
        abort_unless(auth()->user()->isRoot(), 403);

        $this->validate([
            'aiModel'             => ['required', 'string', 'in:claude-haiku-4-5-20251001,claude-sonnet-4-6,claude-opus-4-7'],
            'riskThreshold'       => ['required', 'integer', 'min:0', 'max:100'],
            'similarityThreshold' => ['required', 'numeric', 'min:0.0', 'max:1.0'],
            'maxRetries'          => ['required', 'integer', 'min:1', 'max:5'],
            'agentEnabled'        => ['required', 'boolean'],
        ]);

        PlatformSetting::set('agent.aiModel', $this->aiModel);
        PlatformSetting::set('agent.riskThreshold', $this->riskThreshold);
        PlatformSetting::set('agent.similarityThreshold', $this->similarityThreshold);
        PlatformSetting::set('agent.maxRetries', $this->maxRetries);
        PlatformSetting::set('agent.agentEnabled', $this->agentEnabled);

        $this->dispatch('notify', message: 'Configurações do agente salvas com sucesso.');
    }

    public function render(): View
    {
        return view('livewire.root.settings.agent-settings');
    }
}
