<?php

namespace App\Filament\Resources\Products\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ReviewsRelationManager extends RelationManager
{
    protected static string $relationship = 'reviews';

    protected static ?string $recordTitleAttribute = 'customer_name';

    protected static ?string $title = 'Đánh giá sản phẩm';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('customer_name')
                    ->label('Tên khách hàng')
                    ->required()
                    ->maxLength(255),
                ToggleButtons::make('rating')
                    ->label('Số sao')
                    ->inline()
                    ->options([
                        1 => '1 ⭐',
                        2 => '2 ⭐',
                        3 => '3 ⭐',
                        4 => '4 ⭐',
                        5 => '5 ⭐',
                    ])
                    ->colors([
                        1 => 'danger',
                        2 => 'danger',
                        3 => 'warning',
                        4 => 'success',
                        5 => 'success',
                    ])
                    ->default(5)
                    ->required(),
                Textarea::make('content')
                    ->label('Nội dung nhận xét')
                    ->required()
                    ->columnSpanFull(),
                Toggle::make('is_approved')
                    ->label('Đã duyệt hiển thị')
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('customer_name')
            ->columns([
                TextColumn::make('customer_name')
                    ->label('Khách hàng')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('rating')
                    ->label('Đánh giá')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => str_repeat('⭐', (int) $state)),
                TextColumn::make('content')
                    ->label('Nội dung')
                    ->limit(60)
                    ->searchable(),
                IconColumn::make('is_approved')
                    ->label('Đã duyệt')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Ngày gửi')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('rating')
                    ->label('Số sao')
                    ->options([
                        5 => '5 Sao',
                        4 => '4 Sao',
                        3 => '3 Sao',
                        2 => '2 Sao',
                        1 => '1 Sao',
                    ]),
                TernaryFilter::make('is_approved')
                    ->label('Tình trạng duyệt'),
            ])
            ->headerActions([
                CreateAction::make()->label('Thêm đánh giá'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
