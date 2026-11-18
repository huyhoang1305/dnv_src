<?php

namespace App\Livewire;

use App\Models\Content;
use App\Models\Setting;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.app')] class extends Component
{
    public $setting;

    public $slug = null;

    public $policyList;

    public $policyContent;

    public function mount($slug = null)
    {
        $this->setting = Setting::getSystemSetting();
        $this->slug = $slug;
        $this->loadPolicyData();
    }

    public function setPolicy($slug)
    {
        $this->slug = $slug;
        $this->loadPolicyData();
    }

    protected function loadPolicyData(): void
    {
        $this->policyList = Content::where('is_active', true)
            ->whereHas('contentType', fn ($q) => $q->where('slug', 'chinh-sach'))
            ->orderBy('id', 'asc')
            ->get();

        $this->policyContent = ($this->slug ? $this->policyList->firstWhere('slug', $this->slug) : null)
            ?? $this->policyList->first();

        if ($this->policyContent) {
            $this->slug = $this->policyContent->slug;
            $this->policyContent->increment('view_count');
        }
    }
};
