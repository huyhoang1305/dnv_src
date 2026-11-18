<?php

namespace App\Filament\Resources\Reviews\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReviewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label('Sản phẩm')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('customer_name')
                    ->label('Người đánh giá')
                    ->searchable(),
                TextColumn::make('rating')
                    ->label('Sao')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => str_repeat('⭐', $state)),
                IconColumn::make('is_approved')
                    ->label('Đã duyệt')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Thời gian')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('rating')
                    ->label('Số Sao')
                    ->options([
                        5 => '5 Sao',
                        4 => '4 Sao',
                        3 => '3 Sao',
                        2 => '2 Sao',
                        1 => '1 Sao',
                    ]),
                \Filament\Tables\Filters\TernaryFilter::make('is_approved')
                    ->label('Tình trạng Duyệt'),
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
