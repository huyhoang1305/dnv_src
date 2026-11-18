<?php

namespace App\Filament\Resources\AttributeTypes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AttributeTypesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sort_order')
                    ->label('Thứ tự')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Tên loại')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label('Mã (Slug)')
                    ->searchable(),
                TextColumn::make('values.value')
                    ->label('Các giá trị')
                    ->badge()
                    ->placeholder('Chưa có giá trị'),

                TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
