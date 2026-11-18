<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\AttributeTypes\AttributeTypeResource;
use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\Products\ProductResource;
use App\Filament\Resources\Reviews\ReviewResource;
use App\Models\AttributeType;
use App\Models\Category;
use App\Models\Review;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('categories')
                ->label('Danh mục')
                ->icon('heroicon-o-folder')
                ->color('gray')
                ->badge(fn (): string => (string) Category::count())
                ->visible(fn (): bool => auth()->user()?->can('ViewAny:Category') ?? true)
                ->url(CategoryResource::getUrl('index')),

            Action::make('attribute_types')
                ->label('Loại thuộc tính')
                ->icon('heroicon-o-adjustments-horizontal')
                ->color('gray')
                ->badge(fn (): string => (string) AttributeType::count())
                ->visible(fn (): bool => auth()->user()?->can('ViewAny:AttributeType') ?? true)
                ->url(AttributeTypeResource::getUrl('index')),

            Action::make('reviews')
                ->label('Đánh giá')
                ->icon('heroicon-o-star')
                ->color('gray')
                ->badge(fn (): string => (string) Review::count())
                ->visible(fn (): bool => auth()->user()?->can('ViewAny:Review') ?? true)
                ->url(ReviewResource::getUrl('index')),

            CreateAction::make()
                ->label('Tạo sản phẩm'),
        ];
    }

    public function getTabs(): array
    {
        $tabs = ['all' => Tab::make('Tất cả')];

        // Lấy danh sách danh mục cha (Root categories)
        $parentCategories = Category::whereNull('parent_id')
            ->where('is_active', true)
            ->with('children')
            ->orderBy('sort_order')
            ->get();

        foreach ($parentCategories as $parent) {
            // Lấy ID của danh mục cha và tất cả ID danh mục con trực thuộc
            $categoryIds = array_merge([$parent->id], $parent->children->pluck('id')->toArray());

            $tabs['parent_' . $parent->id] = Tab::make($parent->name)
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->whereHas('categories', fn ($q) => $q->whereIn('categories.id', $categoryIds))
                );
        }

        return $tabs;
    }
}
