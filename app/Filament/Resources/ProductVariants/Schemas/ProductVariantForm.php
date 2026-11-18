<?php

namespace App\Filament\Resources\ProductVariants\Schemas;

use Awcodes\Curator\Components\Forms\CuratorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;

class ProductVariantForm
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
                                Section::make('Cấu hình Biến thể')
                                    ->schema([
                                        Select::make('product_id')
                                            ->label('Thuộc Sản phẩm')
                                            ->helperText('Sản phẩm gốc chứa biến thể này')
                                            ->relationship('product', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->columnSpanFull(),
                                        TextInput::make('sku')
                                            ->label('Mã phân loại (SKU)')
                                            ->helperText('Mã quản lý kho duy nhất cho biến thể này')
                                            ->unique(ignoreRecord: true)
                                            ->validationMessages([
                                                'unique' => 'Mã SKU biến thể này đã tồn tại, vui lòng nhập mã khác.',
                                            ]),
                                        TextInput::make('price')
                                            ->label('Giá bán phân loại (VND)')
                                            ->helperText('Phải lớn hơn 0đ (hoặc để trống nếu dùng chung giá với sản phẩm gốc)')
                                            ->numeric()
                                            ->minValue(1)
                                            ->gt(0)
                                            ->prefix('₫')
                                            ->validationMessages([
                                                'min' => 'Giá bán phân loại phải lớn hơn 0đ.',
                                                'gt' => 'Giá bán phân loại phải lớn hơn 0đ.',
                                            ]),
                                        TextInput::make('stock')
                                            ->label('Kho hàng')
                                            ->helperText('Số lượng tồn kho hiện tại')
                                            ->numeric()
                                            ->default(0),
                                        TextInput::make('sort_order')
                                            ->label('Thứ tự hiển thị')
                                            ->helperText('Số thứ tự ưu tiên')
                                            ->numeric()
                                            ->default(0),
                                    ])->columns(2),
                            ]),
                        Tabs\Tab::make('Hình ảnh')
                            ->icon('heroicon-o-photo')
                            ->schema([
                                Section::make('Ảnh phân loại')
                                    ->schema([
                                        CuratorPicker::make('image_url')
                                            ->label('Hình ảnh biến thể')
                                            ->directory('san-pham')
                                            ->helperText('Hình ảnh minh họa riêng cho tùy chọn này (VD: Ảnh màu áo)')
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                    ])->columnSpanFull()
            ]);
    }
}
