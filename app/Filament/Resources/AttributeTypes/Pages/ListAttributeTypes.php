<?php

namespace App\Filament\Resources\AttributeTypes\Pages;

use App\Filament\Resources\AttributeTypes\AttributeTypeResource;
use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAttributeTypes extends ListRecords
{
    protected static string $resource = AttributeTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back_to_products')
                ->label('Quay lại Sản phẩm')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(ProductResource::getUrl('index')),
            CreateAction::make()
                ->label('Thêm loại thuộc tính'),
        ];
    }

    public function getBreadcrumbs(): array
    {
        return [
            ProductResource::getUrl('index') => 'Sản phẩm',
            'Loại thuộc tính',
        ];
    }
}
