<?php

namespace App\Filament\Resources\Coupons\Schemas;

use Awcodes\Curator\Components\Forms\CuratorPicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;

class CouponForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Tabs')
                    ->tabs([
                        Tabs\Tab::make('Cơ bản & Giá trị')
                            ->icon('heroicon-o-ticket')
                            ->schema([
                                Section::make('Thông tin mã giảm giá')
                                    ->schema([
                                        TextInput::make('code')
                                            ->label('Mã giảm giá')
                                            ->helperText('Mã để khách hàng nhập khi thanh toán (VD: SUMMER2026)')
                                            ->required()
                                            ->unique(ignoreRecord: true),
                                        ToggleButtons::make('type')
                                            ->label('Loại giảm giá')
                                            ->helperText('Hình thức trừ tiền của mã này')
                                            ->inline()
                                            ->options([
                                                'free_shipping' => 'Miễn phí vận chuyển (Freeship 0đ)',
                                                'shipping' => 'Giảm phí ship (VND)',
                                                'percentage' => 'Phần trăm (%)',
                                                'fixed' => 'Cố định (VND)',
                                            ])
                                            ->colors([
                                                'free_shipping' => 'success',
                                                'shipping' => 'warning',
                                                'percentage' => 'info',
                                                'fixed' => 'primary',
                                            ])
                                            ->live()
                                            ->afterStateUpdated(function ($state, callable $set) {
                                                if ($state === 'free_shipping') {
                                                    $set('value', 0);
                                                }
                                            })
                                            ->required(),
                                        TextInput::make('value')
                                            ->label(fn ($get) => match ($get('type')) {
                                                'free_shipping' => 'Mức giảm phí ship (0đ)',
                                                'shipping' => 'Số tiền giảm ship (VND)',
                                                'percentage' => 'Phần trăm giảm (%)',
                                                'fixed' => 'Số tiền giảm (VND)',
                                                default => 'Giá trị giảm',
                                            })
                                            ->helperText(fn ($get) => match ($get('type')) {
                                                'free_shipping' => 'Mã này sẽ miễn phí 100% phí giao hàng (phí ship = 0đ)',
                                                'shipping' => 'Nhập số tiền phí ship được giảm (VD: 20000 để giảm 20.000đ; hoặc nhập 100 để miễn phí 100%)',
                                                'percentage' => 'Nhập số % muốn giảm (VD: 10 để giảm 10% đơn hàng)',
                                                'fixed' => 'Nhập số tiền VNĐ muốn giảm (VD: 50000 để giảm 50.000đ)',
                                                default => 'Nhập số % hoặc số tiền tùy vào Loại giảm giá',
                                            })
                                            ->numeric()
                                            ->default(0)
                                            ->required(),
                                    ])->columns(2),
                            ]),

                        Tabs\Tab::make('Hình ảnh & Hiển thị')
                            ->icon('heroicon-o-photo')
                            ->schema([
                                Section::make('Hình ảnh & Thông tin bổ sung')
                                    ->schema([
                                        CuratorPicker::make('image_url')
                                            ->label('Ảnh voucher')
                                            ->directory('vouchers')
                                            ->helperText('Ảnh minh họa voucher (hiển thị trên trang chủ, giỏ hàng và trang thanh toán)')
                                            ->columnSpanFull(),
                                        TextInput::make('title')
                                            ->label('Tiêu đề hiển thị')
                                            ->helperText('VD: Giảm 25K phí vận chuyển, Giảm 50K cho đơn từ 500K...'),
                                        TextInput::make('badge')
                                            ->label('Huy hiệu (Badge)')
                                            ->helperText('VD: FREESHIP, HOT DEAL, DNV MALL...'),
                                        TextInput::make('color')
                                            ->label('Màu sắc / Nhãn màu')
                                            ->helperText('VD: #f97316 hoặc orange'),
                                        Textarea::make('description')
                                            ->label('Mô tả ngắn')
                                            ->helperText('Mô tả điều kiện hoặc lợi ích mã giảm giá')
                                            ->columnSpanFull(),
                                    ])->columns(2),
                            ]),

                        Tabs\Tab::make('Điều kiện áp dụng')
                            ->icon('heroicon-o-clipboard-document-check')
                            ->schema([
                                Section::make('Giới hạn đơn hàng & Thời gian')
                                    ->schema([
                                        TextInput::make('min_order_amount')
                                            ->label('Đơn tối thiểu (VND)')
                                            ->helperText('Giá trị đơn hàng tối thiểu để được áp dụng mã')
                                            ->numeric()
                                            ->prefix('₫'),
                                        TextInput::make('max_discount')
                                            ->label('Giảm tối đa (VND)')
                                            ->helperText('Chỉ áp dụng nếu Loại giảm giá là Phần trăm (%)')
                                            ->numeric()
                                            ->prefix('₫'),
                                        DateTimePicker::make('valid_from')
                                            ->label('Từ ngày')
                                            ->helperText('Ngày bắt đầu có hiệu lực'),
                                        DateTimePicker::make('valid_until')
                                            ->label('Đến ngày')
                                            ->helperText('Ngày hết hạn sử dụng mã'),
                                    ])->columns(2),
                            ]),

                        Tabs\Tab::make('Trạng thái & Lượt dùng')
                            ->icon('heroicon-o-chart-bar')
                            ->schema([
                                Section::make('Quản lý lượt sử dụng')
                                    ->schema([
                                        Toggle::make('is_active')
                                            ->label('Kích hoạt')
                                            ->helperText('Bật để mã giảm giá có thể sử dụng được')
                                            ->default(true),
                                        TextInput::make('usage_limit')
                                            ->label('Giới hạn số lượt dùng')
                                            ->helperText('Tổng số lần mã này có thể được nhập (để trống nếu không giới hạn)')
                                            ->numeric(),
                                        TextInput::make('used_count')
                                            ->label('Đã dùng')
                                            ->helperText('Số lượt mã này đã được sử dụng thành công')
                                            ->numeric()
                                            ->default(0)
                                            ->disabled(),
                                        TextInput::make('sort_order')
                                            ->label('Thứ tự hiển thị')
                                            ->helperText('Số thứ tự ưu tiên')
                                            ->numeric()
                                            ->default(0),
                                    ])->columns(2),
                            ]),
                    ])->columnSpanFull()
            ]);
    }
}
