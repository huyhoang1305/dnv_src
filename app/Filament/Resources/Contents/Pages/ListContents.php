<?php

namespace App\Filament\Resources\Contents\Pages;

use App\Filament\Resources\ContentCategories\ContentCategoryResource;
use App\Filament\Resources\Contents\ContentResource;
use App\Filament\Resources\ContentTypes\ContentTypeResource;
use App\Models\ContentCategory;
use App\Models\ContentType;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListContents extends ListRecords
{
    protected static string $resource = ContentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('categories')
                ->label('Danh mục')
                ->icon('heroicon-o-folder')
                ->color('gray')
                ->badge(fn (): string => (string) ContentCategory::count())
                ->visible(fn (): bool => auth()->user()?->can('ViewAny:ContentCategory') ?? true)
                ->url(ContentCategoryResource::getUrl('index')),

            Action::make('types')
                ->label('Phân loại')
                ->icon('heroicon-o-flag')
                ->color('gray')
                ->badge(fn (): string => (string) ContentType::count())
                ->visible(fn (): bool => auth()->user()?->can('ViewAny:ContentType') ?? true)
                ->url(ContentTypeResource::getUrl('index')),

            CreateAction::make()
                ->label('Tạo nội dung'),
        ];
    }

    public function getTabs(): array
    {
        // Khởi tạo tab "Tất cả" mặc định
        $tabs = ['all' => Tab::make('Tất cả')];

        // Lấy danh sách ContentType đang hoạt động
        $contentTypes = ContentType::where('is_active', true)->orderBy('sort_order')->get();

        // Tự động sinh thêm tab cho từng loại ContentType
        foreach ($contentTypes as $type) {
            $tabs[$type->slug] = Tab::make($type->name)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('content_type_id', $type->id));
        }

        return $tabs;
    }
}
