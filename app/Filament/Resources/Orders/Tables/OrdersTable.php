<?php

namespace App\Filament\Resources\Orders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->label('Mã ĐH')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('customer_name')
                    ->label('Khách hàng')
                    ->searchable()
                    ->description(fn ($record) => $record->customer_phone),
                TextColumn::make('status')
                    ->label('Trạng thái')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'processing' => 'warning',
                        'shipping' => 'info',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'primary',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Chờ xử lý',
                        'processing' => 'Chuẩn bị hàng',
                        'shipping' => 'Đang giao',
                        'completed' => 'Hoàn thành',
                        'cancelled' => 'Đã hủy',
                        default => $state,
                    })
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->label('Tổng thanh toán')
                    ->numeric()
                    ->money('VND')
                    ->sortable(),
                TextColumn::make('payment_status')
                    ->label('Thanh toán')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'paid' => 'success',
                        'unpaid', 'pending' => 'warning',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'paid' => 'Đã thanh toán',
                        'unpaid', 'pending', null, '' => 'Chưa thanh toán',
                        'failed' => 'Lỗi giao dịch',
                        default => $state,
                    }),
                TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->label('Trạng thái Đơn hàng')
                    ->options([
                        'pending' => 'Chờ xử lý',
                        'processing' => 'Chuẩn bị hàng',
                        'shipping' => 'Đang giao',
                        'completed' => 'Hoàn thành',
                        'cancelled' => 'Đã hủy',
                    ]),
                \Filament\Tables\Filters\SelectFilter::make('payment_status')
                    ->label('Trạng thái TT')
                    ->options([
                        'paid' => 'Đã thanh toán',
                        'unpaid' => 'Chưa thanh toán',
                        'failed' => 'Lỗi giao dịch',
                    ])
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data): \Illuminate\Database\Eloquent\Builder {
                        $val = $data['value'] ?? null;
                        if ($val === 'unpaid') {
                            return $query->whereIn('payment_status', ['unpaid', 'pending']);
                        }

                        return $val ? $query->where('payment_status', $val) : $query;
                    }),
                \Filament\Tables\Filters\Filter::make('created_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('created_from')->label('Từ ngày'),
                        \Filament\Forms\Components\DatePicker::make('created_until')->label('Đến ngày'),
                    ])
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data): \Illuminate\Database\Eloquent\Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (\Illuminate\Database\Eloquent\Builder $query, $date): \Illuminate\Database\Eloquent\Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (\Illuminate\Database\Eloquent\Builder $query, $date): \Illuminate\Database\Eloquent\Builder => $query->whereDate('created_at', '<=', $date),
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
            ]);
    }
}
