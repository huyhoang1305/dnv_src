<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Tabs')
                    ->tabs([
                        Tabs\Tab::make('Thông tin chung')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Section::make('Khách hàng & Giao hàng')
                                    ->schema([
                                        Select::make('user_id')
                                            ->label('Tài khoản User')
                                            ->helperText('Chọn tài khoản nếu khách hàng đã đăng ký (Có thể bỏ trống)')
                                            ->relationship('user', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->columnSpanFull(),
                                        TextInput::make('customer_name')
                                            ->label('Tên người nhận')
                                            ->helperText('Họ và tên người nhận hàng')
                                            ->required(),
                                        TextInput::make('customer_phone')
                                            ->label('Số điện thoại')
                                            ->helperText('Số điện thoại liên hệ giao hàng')
                                            ->required(),
                                        TextInput::make('customer_email')
                                            ->label('Email')
                                            ->helperText('Email nhận thông báo đơn hàng (nếu có)'),
                                        Textarea::make('shipping_address')
                                            ->label('Địa chỉ giao hàng')
                                            ->helperText('Địa chỉ chi tiết (Số nhà, đường, phường/xã, quận/huyện, tỉnh/thành phố)')
                                            ->required()
                                            ->columnSpanFull(),
                                    ])->columns(3),

                                Section::make('Trạng thái & Hình thức')
                                    ->schema([
                                        TextInput::make('order_number')
                                            ->label('Mã đơn hàng')
                                            ->helperText('Mã định danh duy nhất của đơn hàng (DNV-00XXXX)')
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->validationMessages([
                                                'unique' => 'Mã đơn hàng này đã tồn tại trên hệ thống.',
                                            ])
                                            ->default(fn () => \App\Models\Order::generateOrderNumber()),
                                        ToggleButtons::make('status')
                                            ->label('Trạng thái đơn hàng')
                                            ->helperText('Tiến độ xử lý hiện tại của đơn hàng')
                                            ->inline()
                                            ->options([
                                                'pending' => 'Chờ xử lý',
                                                'processing' => 'Đang chuẩn bị',
                                                'shipping' => 'Đang giao hàng',
                                                'completed' => 'Thành công',
                                                'cancelled' => 'Đã hủy',
                                            ])
                                            ->colors([
                                                'pending' => 'warning',
                                                'processing' => 'info',
                                                'shipping' => 'primary',
                                                'completed' => 'success',
                                                'cancelled' => 'danger',
                                            ])
                                            ->required()
                                            ->default('pending'),
                                        ToggleButtons::make('payment_method')
                                            ->label('Phương thức thanh toán')
                                            ->helperText('Hình thức khách hàng chọn để thanh toán')
                                            ->inline()
                                            ->options([
                                                'cod' => 'Nhận hàng (COD)',
                                                'bank_transfer' => 'Chuyển khoản',
                                                'momo' => 'Ví MoMo',
                                                'vnpay' => 'VNPay',
                                            ])
                                            ->required(),
                                        ToggleButtons::make('payment_status')
                                            ->label('Trạng thái thanh toán')
                                            ->helperText('Tình trạng nhận tiền của đơn hàng')
                                            ->inline()
                                            ->options([
                                                'unpaid' => 'Chưa thanh toán',
                                                'paid' => 'Đã thanh toán',
                                                'failed' => 'Lỗi giao dịch',
                                            ])
                                            ->colors([
                                                'unpaid' => 'warning',
                                                'paid' => 'success',
                                                'failed' => 'danger',
                                            ])
                                            ->formatStateUsing(fn (?string $state): string => $state === 'pending' || empty($state) ? 'unpaid' : $state)
                                            ->required()
                                            ->default('unpaid'),
                                    ])->columns(2),
                            ]),

                        Tabs\Tab::make('Chi tiết sản phẩm')
                            ->icon('heroicon-o-shopping-bag')
                            ->schema([
                                Section::make('Danh sách Sản phẩm')
                                    ->schema([
                                        Repeater::make('items')
                                            ->relationship('items')
                                            ->schema([
                                                Select::make('product_id')
                                                    ->label('Sản phẩm')
                                                    ->relationship('product', 'name')
                                                    ->searchable()
                                                    ->preload()
                                                    ->required()
                                                    ->columnSpan(4),
                                                Select::make('product_variant_id')
                                                    ->label('Biến thể / Phân loại')
                                                    ->relationship('variant', 'sku')
                                                    ->searchable()
                                                    ->preload()
                                                    ->columnSpan(3),
                                                TextInput::make('price')
                                                    ->label('Đơn giá')
                                                    ->numeric()
                                                    ->required()
                                                    ->columnSpan(2),
                                                TextInput::make('quantity')
                                                    ->label('Số lượng')
                                                    ->numeric()
                                                    ->required()
                                                    ->default(1)
                                                    ->columnSpan(1),
                                                TextInput::make('total')
                                                    ->label('Thành tiền (VND)')
                                                    ->numeric()
                                                    ->required()
                                                    ->columnSpan(2),
                                            ])
                                            ->columns(12)
                                            ->defaultItems(0) // Nếu đơn rỗng thì không bắt hiển thị form rỗng
                                            ->addActionLabel('Thêm sản phẩm')
                                            ->columnSpanFull()
                                    ])
                            ]),

                        Tabs\Tab::make('Tổng tiền & Khuyến mãi')
                            ->icon('heroicon-o-currency-dollar')
                            ->schema([
                                Section::make('Chi tiết Tiền')
                                    ->schema([
                                        TextInput::make('sub_total')
                                            ->label('Tổng tiền hàng (VND)')
                                            ->helperText('Tổng giá trị các sản phẩm chưa tính phí ship')
                                            ->numeric()
                                            ->required()
                                            ->prefix('₫'),
                                        TextInput::make('shipping_fee')
                                            ->label('Phí vận chuyển (VND)')
                                            ->helperText('Phí giao hàng dự kiến')
                                            ->numeric()
                                            ->default(0)
                                            ->prefix('₫'),
                                        Select::make('coupon_id')
                                            ->label('Mã giảm giá đã dùng')
                                            ->helperText('Mã voucher mà khách hàng đã áp dụng (nếu có)')
                                            ->relationship('coupon', 'code')
                                            ->searchable(),
                                        TextInput::make('discount_amount')
                                            ->label('Tiền được giảm (VND)')
                                            ->helperText('Số tiền được trừ đi nhờ mã giảm giá')
                                            ->numeric()
                                            ->default(0)
                                            ->prefix('₫'),
                                        TextInput::make('total_amount')
                                            ->label('Tổng thanh toán (VND)')
                                            ->helperText('Tổng số tiền khách hàng phải trả cuối cùng')
                                            ->numeric()
                                            ->required()
                                            ->prefix('₫')
                                            ->columnSpanFull(),
                                    ])->columns(2),
                            ]),
                    ])->columnSpanFull()
            ]);
    }
}
