<?php declare(strict_types=1);

namespace App\Livewire\Root\Settings;

use App\Models\PlatformSetting;
use Illuminate\Contracts\View\View;
use Livewire\Component;

final class PlatformSettings extends Component
{
    public string $platformName     = 'ticketForum';
    public string $baseUrl          = '';
    public string $supportEmail     = '';
    public bool   $maintenanceMode  = false;

    public function mount(): void
    {
        abort_unless(auth()->user()->isRoot(), 403);

        $this->platformName    = (string) PlatformSetting::get('platform.platformName', 'ticketForum');
        $this->baseUrl         = (string) PlatformSetting::get('platform.baseUrl', '');
        $this->supportEmail    = (string) PlatformSetting::get('platform.supportEmail', '');
        $this->maintenanceMode = (bool)   PlatformSetting::get('platform.maintenanceMode', false);
    }

    public function save(): void
    {
        abort_unless(auth()->user()->isRoot(), 403);

        $this->validate([
            'platformName'    => ['required', 'string', 'max:100'],
            'baseUrl'         => ['nullable', 'url'],
            'supportEmail'    => ['nullable', 'email'],
            'maintenanceMode' => ['required', 'boolean'],
        ]);

        PlatformSetting::set('platform.platformName', $this->platformName);
        PlatformSetting::set('platform.baseUrl', $this->baseUrl);
        PlatformSetting::set('platform.supportEmail', $this->supportEmail);
        PlatformSetting::set('platform.maintenanceMode', $this->maintenanceMode);

        $this->dispatch('notify', message: 'Configurações da plataforma salvas com sucesso.');
    }

    public function render(): View
    {
        return view('livewire.root.settings.platform-settings');
    }
}
