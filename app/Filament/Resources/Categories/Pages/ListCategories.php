<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\Products\ProductResource;
use App\Models\Category;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListCategories extends ListRecords
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back_to_products')
                ->label('Quay lại Sản phẩm')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(ProductResource::getUrl('index')),
            CreateAction::make()
                ->label('Thêm danh mục'),
        ];
    }

    public function getBreadcrumbs(): array
    {
        return [
            ProductResource::getUrl('index') => 'Sản phẩm',
            'Danh mục',
        ];
    }

    public function getTabs(): array
    {
        $tabs = [
            'all' => Tab::make('Tất cả'),
            'root' => Tab::make('Danh mục Gốc')->modifyQueryUsing(fn (Builder $query) => $query->whereNull('parent_id')),
        ];

        $parentCategories = Category::whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        foreach ($parentCategories as $parent) {
            $tabs['parent_' . $parent->id] = Tab::make($parent->name)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('parent_id', $parent->id));
        }

        return $tabs;
    }
}
