<?php

namespace App\Livewire;

use App\Models\Content;
use App\Models\Setting;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.app')] class extends Component
{
    public $setting;
    public $slug;
    public $article;
    public $relatedArticles = [];

    public function mount($slug = null)
    {
        $this->setting = Setting::getSystemSetting();
        $this->slug = $slug;

        $this->article = $slug
            ? Content::with('contentCategory')
                ->where('slug', $slug)
                ->where('is_active', true)
                ->first()
            : Content::with('contentCategory')
                ->where('is_active', true)
                ->whereHas('contentType', fn ($q) => $q->where('slug', '!=', 'chinh-sach'))
                ->first();

        if (!$this->article) {
            abort(404);
        }

        if ($this->article) {
            // Increment view count
            $this->article->increment('view_count');

            $this->relatedArticles = Content::with('contentCategory')
                ->where('id', '!=', $this->article->id)
                ->where('is_active', true)
                ->whereHas('contentType', fn ($q) => $q->where('slug', '!=', 'chinh-sach'))
                ->latest()
                ->take(3)
                ->get();
        }
    }
};

