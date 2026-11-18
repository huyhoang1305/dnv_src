<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class LatestOrdersWidget extends BaseWidget
{
    use HasWidgetShield;
    protected static bool $isLazy = false;
    protected static ?string $heading = 'Đơn hàng mới nhất';
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';
    
    public function table(Table $table): Table
    {
        return $table
            ->heading('Đơn hàng mới nhất')
            ->query(Order::query()->latest()->limit(5))
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('Mã Đơn')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Khách hàng')
                    ->description(fn (Order $record) => $record->customer_phone)
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Trạng thái')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'pending' => 'gray',
                        'processing' => 'warning',
                        'shipping' => 'info',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'primary',
                    })
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'pending' => 'Chờ xử lý',
                        'processing' => 'Chuẩn bị hàng',
                        'shipping' => 'Đang giao',
                        'completed' => 'Hoàn thành',
                        'cancelled' => 'Đã hủy',
                        default => (string) $state,
                    }),
                Tables\Columns\TextColumn::make('payment_status')
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
                        default => (string) $state,
                    }),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Tổng Tiền')
                    ->money('VND')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Thời gian')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->actions([
                \Filament\Actions\Action::make('Xem')
                    ->url(fn (Order $record): string => OrderResource::getUrl('edit', ['record' => $record]))
                    ->icon('heroicon-m-eye'),
            ]);
    }
}
