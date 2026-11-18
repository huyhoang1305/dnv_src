<?php

namespace App\Filament\Resources\Contents\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\IconColumn;
use Awcodes\Curator\Components\Tables\CuratorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ContentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                CuratorColumn::make('image_url')->label('Hình ảnh')->size(40),
                TextColumn::make('title')->label('Tiêu đề bài viết')->searchable()->wrap(),
                TextColumn::make('view_count')->label('Lượt xem')->numeric()->sortable()->badge()->color('success'),
                IconColumn::make('is_active')->label('Hiển thị')->boolean(),
                TextColumn::make('content_type_id')->label('Nhóm')->numeric()->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('content_category_id')->label('Danh mục')->numeric()->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('slug')->label('Đường dẫn')->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('seo_title')->label('SEO Title')->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('seo_keywords')->label('SEO Keywords')->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')->label('Ngày đăng')->dateTime('d/m/Y H:i')->sortable(),
                TextColumn::make('updated_at')->label('Cập nhật lúc')->dateTime('d/m/Y H:i')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Hiển thị'),
                SelectFilter::make('content_type_id')
                    ->label('Danh mục Gốc')
                    ->relationship('contentType', 'name')
                    ->searchable()
                    ->preload(),
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')->label('Từ ngày'),
                        DatePicker::make('created_until')->label('Đến ngày'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
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
