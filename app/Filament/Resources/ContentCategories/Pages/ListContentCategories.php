<?php

namespace App\Filament\Resources\ContentCategories\Pages;

use App\Filament\Resources\ContentCategories\ContentCategoryResource;
use App\Filament\Resources\Contents\ContentResource;
use App\Models\ContentType;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListContentCategories extends ListRecords
{
    protected static string $resource = ContentCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back_to_contents')
                ->label('Quay lại Nội dung')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(ContentResource::getUrl('index')),

            CreateAction::make()
                ->label('Thêm danh mục'),
        ];
    }

    public function getBreadcrumbs(): array
    {
        return [
            ContentResource::getUrl('index') => 'Nội dung',
            'Danh mục',
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
