<?php

namespace App\Livewire;

use App\Models\Setting;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.app')] class extends Component
{
    public $setting;

    public function mount()
    {
        $this->setting = Setting::getSystemSetting();
    }
};
