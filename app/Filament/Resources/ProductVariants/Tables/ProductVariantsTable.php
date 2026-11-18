<?php

namespace App\Filament\Resources\ProductVariants\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Awcodes\Curator\Components\Tables\CuratorColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductVariantsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sort_order')
                    ->label('Thứ tự')
                    ->numeric()
                    ->sortable(),
                CuratorColumn::make('image_url')->size(40)
                    ->label('Hình ảnh')
                    ->disk('public')
                    ->square(),
                TextColumn::make('product.name')
                    ->label('Sản phẩm')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('sku')
                    ->label('Mã SKU')
                    ->searchable(),
                TextColumn::make('price')
                    ->label('Giá (VND)')
                    ->numeric()
                    ->money('VND')
                    ->sortable(),
                TextColumn::make('stock')
                    ->label('Kho')
                    ->numeric()
                    ->sortable()
                    ->badge(),

            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->reorderable('sort_order')
            ->defaultSort('sort_order', 'asc');
    }
}
