<?php

namespace App\Filament\Resources\Coupons\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Awcodes\Curator\Components\Tables\CuratorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CouponsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                CuratorColumn::make('image_url')
                    ->label('Ảnh')
                    ->size(45),
                TextColumn::make('code')
                    ->label('Mã Voucher')
                    ->searchable()
                    ->badge(),
                TextColumn::make('type')
                    ->label('Loại')
                    ->searchable()
                    ->badge()
                    ->formatStateUsing(function ($state) {
                        if (in_array($state, ['percent', 'percentage'])) return '%';
                        if ($state === 'free_shipping') return 'Freeship (0đ)';
                        if ($state === 'shipping') return 'Giảm phí ship';
                        return 'VNĐ';
                    })
                    ->color(function ($state) {
                        if (in_array($state, ['percent', 'percentage'])) return 'info';
                        if ($state === 'free_shipping') return 'success';
                        if ($state === 'shipping') return 'warning';
                        return 'primary';
                    }),
                TextColumn::make('value')
                    ->label('Giá trị')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('used_count')
                    ->label('Đã dùng')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn ($state, $record) => $state . ' / ' . ($record->usage_limit ?: '∞')),
                IconColumn::make('is_active')
                    ->label('Kích hoạt')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('valid_until')
                    ->label('Hạn dùng')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                \Filament\Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Tình trạng Kích hoạt'),
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
