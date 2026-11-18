<?php

namespace App\Filament\Resources\OrderItems\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;

class OrderItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Tabs')
                    ->tabs([
                        Tabs\Tab::make('Chi tiết món hàng')
                            ->icon('heroicon-o-shopping-bag')
                            ->schema([
                                Section::make('Thông tin sản phẩm')
                                    ->schema([
                                        Select::make('order_id')
                                            ->label('Thuộc Đơn hàng')
                                            ->helperText('Mã đơn hàng liên kết')
                                            ->relationship('order', 'order_number')
                                            ->searchable()
                                            ->preload()
                                            ->required(),
                                        Select::make('product_id')
                                            ->label('Sản phẩm')
                                            ->helperText('Sản phẩm khách đã đặt')
                                            ->relationship('product', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->required(),
                                        Select::make('product_variant_id')
                                            ->label('Phân loại (Biến thể)')
                                            ->helperText('Nếu là sản phẩm có nhiều màu, size...')
                                            ->relationship('variant', 'sku')
                                            ->searchable()
                                            ->preload(),
                                    ])->columns(2),

                                Section::make('Đơn giá & Số lượng')
                                    ->schema([
                                        TextInput::make('price')
                                            ->label('Đơn giá (VND)')
                                            ->helperText('Giá của 1 sản phẩm tại thời điểm mua')
                                            ->numeric()
                                            ->required()
                                            ->prefix('₫'),
                                        TextInput::make('quantity')
                                            ->label('Số lượng')
                                            ->helperText('Số lượng sản phẩm khách đặt')
                                            ->numeric()
                                            ->required()
                                            ->default(1),
                                        TextInput::make('total')
                                            ->label('Tổng phụ (VND)')
                                            ->helperText('Bằng Đơn giá x Số lượng')
                                            ->numeric()
                                            ->required()
                                            ->prefix('₫'),
                                    ])->columns(3),
                            ]),
                    ])->columnSpanFull()
            ]);
    }
}
