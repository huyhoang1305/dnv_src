<?php

namespace App\Filament\Resources\ContentTypes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ContentTypesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sort_order')->label('Thứ tự')->numeric()->sortable(),
                TextColumn::make('name')->label('Tên nhóm nội dung')->searchable(),
                TextColumn::make('slug')->label('Đường dẫn')->searchable(),
                IconColumn::make('is_active')->label('Hiển thị')->boolean(),

                TextColumn::make('created_at')->label('Ngày tạo')->dateTime('d/m/Y H:i')->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->label('Cập nhật lúc')->dateTime('d/m/Y H:i')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Hiển thị'),
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
