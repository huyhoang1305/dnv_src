<?php

namespace App\Livewire;

use App\Models\Content;
use App\Models\ContentCategory;
use App\Models\Setting;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    public $setting;

    #[Url]
    public $category = 'all';

    #[Url]
    public $search = '';

    public function mount()
    {
        $this->setting = Setting::getSystemSetting();
    }

    public function setCategory($cat)
    {
        $this->category = $cat;
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function getCategoriesProperty()
    {
        return ContentCategory::where('is_active', true)
            ->whereHas('contentType', fn ($q) => $q->where('slug', '!=', 'chinh-sach'))
            ->withCount(['contents' => function ($q) {
                $q->where('is_active', true);
            }])
            ->get();
    }

    public function getArticlesProperty()
    {
        $query = Content::with('contentCategory')
            ->where('is_active', true)
            ->whereHas('contentType', fn ($q) => $q->where('slug', '!=', 'chinh-sach'));

        if ($this->category && $this->category !== 'all') {
            $cat = ContentCategory::where('slug', $this->category)->first();
            if ($cat) {
                $query->where('content_category_id', $cat->id);
            }
        }

        if ($this->search) {
            $query->where('title', 'like', '%' . $this->search . '%');
        }

        $perPage = (int) ($this->setting?->interface_settings['blog']['per_page'] ?? 6);
        return $query->latest()->paginate($perPage > 0 ? $perPage : 6);
    }

    public function getPopularArticlesProperty()
    {
        return Content::with('contentCategory')
            ->where('is_active', true)
            ->whereHas('contentType', fn ($q) => $q->where('slug', '!=', 'chinh-sach'))
            ->orderBy('view_count', 'desc')
            ->take(5)
            ->get();
    }
};
