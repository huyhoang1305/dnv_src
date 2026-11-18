<?php

namespace App\Filament\Resources\ContentCategories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Awcodes\Curator\Components\Tables\CuratorColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ContentCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sort_order')->label('Thứ tự')->numeric()->sortable(),
                CuratorColumn::make('image_url')->size(40),
                TextColumn::make('name')->label('Tên danh mục')->searchable(),
                TextColumn::make('slug')->label('Đường dẫn')->searchable(),
                TextColumn::make('contentType.name')->label('Loại nội dung')->badge()->sortable(),
                TextColumn::make('parent_id')->label('Phân cấp')->numeric()->sortable()->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')->label('Hiển thị')->boolean(),

                TextColumn::make('seo_title')->label('SEO Title')->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('seo_keywords')->label('SEO Keywords')->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')->label('Ngày tạo')->dateTime('d/m/Y H:i')->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->label('Cập nhật lúc')->dateTime('d/m/Y H:i')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Hiển thị'),
                \Filament\Tables\Filters\SelectFilter::make('content_type_id')
                    ->label('Danh mục Gốc')
                    ->relationship('contentType', 'name')
                    ->searchable()
                    ->preload(),
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
