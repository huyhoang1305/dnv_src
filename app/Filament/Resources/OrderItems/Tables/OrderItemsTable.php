<?php

namespace App\Filament\Resources\OrderItems\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrderItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order.order_number')
                    ->label('Mã ĐH')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('product.name')
                    ->label('Sản phẩm')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('variant.sku')
                    ->label('Phân loại (SKU)')
                    ->searchable(),
                TextColumn::make('price')
                    ->label('Đơn giá')
                    ->numeric()
                    ->money('VND')
                    ->sortable(),
                TextColumn::make('quantity')
                    ->label('Số lượng')
                    ->numeric()
                    ->sortable()
                    ->badge(),
                TextColumn::make('total')
                    ->label('Tổng phụ')
                    ->numeric()
                    ->money('VND')
                    ->sortable(),
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
            ]);
    }
}
