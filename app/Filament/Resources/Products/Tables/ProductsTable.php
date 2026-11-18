<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Awcodes\Curator\Components\Tables\CuratorColumn;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sort_order')
                    ->label('Thứ tự')
                    ->numeric()
                    ->sortable(),
                CuratorColumn::make('image_url')
                    ->label('Hình ảnh')
                    ->size(40),
                TextColumn::make('name')
                    ->label('Sản phẩm')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('categories.name')
                    ->label('Danh mục')
                    ->badge()
                    ->separator(',')
                    ->searchable(),
                TextColumn::make('base_price')
                    ->label('Giá gốc')
                    ->badge(fn ($record) => (float) ($record->base_price ?? 0) <= 0)
                    ->color(fn ($record) => (float) ($record->base_price ?? 0) <= 0 ? 'danger' : null)
                    ->formatStateUsing(fn ($state) => (float) $state <= 0 ? '0 ₫ (Chưa có giá)' : number_format((float) $state, 0, ',', '.') . ' ₫')
                    ->sortable(),
                TextColumn::make('sale_price')
                    ->label('Giá KM')
                    ->numeric()
                    ->money('VND')
                    ->sortable(),
                TextColumn::make('unit')
                    ->label('ĐVT')
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('weight')
                    ->label('KL / Thể tích')
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('stock_quantity')
                    ->label('Hàng tồn')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('sold_count')
                    ->label('Đã bán')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Kích hoạt')
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_featured')
                    ->label('Nổi bật')
                    ->boolean()
                    ->sortable(),

            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('categories')
                    ->label('Danh mục')
                    ->relationship('categories', 'name')
                    ->searchable()
                    ->preload(),
                \Filament\Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Kích hoạt'),
                \Filament\Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Nổi bật'),
                \Filament\Tables\Filters\Filter::make('base_price')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('price_from')
                            ->label('Giá gốc từ (VND)')
                            ->numeric(),
                        \Filament\Forms\Components\TextInput::make('price_to')
                            ->label('Giá gốc đến (VND)')
                            ->numeric(),
                    ])
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data): \Illuminate\Database\Eloquent\Builder {
                        return $query
                            ->when(
                                $data['price_from'],
                                fn (\Illuminate\Database\Eloquent\Builder $query, $price): \Illuminate\Database\Eloquent\Builder => $query->where('base_price', '>=', $price),
                            )
                            ->when(
                                $data['price_to'],
                                fn (\Illuminate\Database\Eloquent\Builder $query, $price): \Illuminate\Database\Eloquent\Builder => $query->where('base_price', '<=', $price),
                            );
                    })
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
