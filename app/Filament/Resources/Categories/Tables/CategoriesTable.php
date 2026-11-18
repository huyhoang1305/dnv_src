<?php

namespace App\Filament\Resources\Categories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Awcodes\Curator\Components\Tables\CuratorColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CategoriesTable
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
                    ->circular(),
                TextColumn::make('name')
                    ->label('Tên danh mục')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('parent.name')
                    ->label('Danh mục cha')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label('Đường dẫn')
                    ->searchable(),
                IconColumn::make('is_active')
                    ->label('Kích hoạt')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Cập nhật lần cuối')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Kích hoạt'),
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
