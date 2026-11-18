<?php

namespace App\Filament\Resources\Contacts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;

class ContactsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('name')
                    ->label('Họ tên')
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('phone')
                    ->label('Số ĐT')
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                \Filament\Tables\Columns\TextColumn::make('subject')
                    ->label('Tiêu đề')
                    ->searchable(),
                \Filament\Tables\Columns\SelectColumn::make('status')
                    ->label('Trạng thái')
                    ->options([
                        'pending' => 'Chờ xử lý',
                        'processing' => 'Đang xử lý',
                        'resolved' => 'Đã giải quyết',
                    ])
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->label('Ngày gửi')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options([
                        'pending' => 'Chờ xử lý',
                        'processing' => 'Đang xử lý',
                        'resolved' => 'Đã giải quyết',
                    ]),
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
