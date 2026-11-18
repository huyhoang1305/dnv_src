<?php

namespace App\Filament\Resources\Reviews\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Filament\Resources\Reviews\ReviewResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListReviews extends ListRecords
{
    protected static string $resource = ReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back_to_products')
                ->label('Quay lại Sản phẩm')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(ProductResource::getUrl('index')),
            CreateAction::make()
                ->label('Thêm đánh giá'),
        ];
    }

    public function getBreadcrumbs(): array
    {
        return [
            ProductResource::getUrl('index') => 'Sản phẩm',
            'Đánh giá',
        ];
    }
}
