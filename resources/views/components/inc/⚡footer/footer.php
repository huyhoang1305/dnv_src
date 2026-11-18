<?php

namespace App\Livewire;

use App\Models\Setting;
use Livewire\Component;

new class extends Component
{
    public $setting;

    public function mount()
    {
        if (!$this->setting) {
            $this->setting = Setting::getSystemSetting();
        }
    }
};