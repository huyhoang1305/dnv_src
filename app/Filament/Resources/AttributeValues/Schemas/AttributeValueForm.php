<?php

namespace App\Filament\Resources\AttributeValues\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ColorPicker;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;

class AttributeValueForm
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
                                Section::make('Chi tiết Giá trị thuộc tính')
                                    ->schema([
                                        Select::make('attribute_type_id')
                                            ->label('Thuộc nhóm')
                                            ->helperText('Chọn nhóm thuộc tính chứa giá trị này')
                                            ->relationship('attributeType', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->required(),
                                        TextInput::make('value')
                                            ->label('Giá trị')
                                            ->helperText('Ví dụ: Đỏ, Xanh, XL, XXL')
                                            ->required(),
                                        ColorPicker::make('color_code')
                                            ->label('Mã màu')
                                            ->helperText('Chỉ cần thiết nếu thuộc tính là dạng Màu sắc'),
                                        TextInput::make('sort_order')
                                            ->label('Thứ tự sắp xếp')
                                            ->helperText('Số nhỏ ưu tiên hiển thị trước')
                                            ->numeric()
                                            ->default(0),
                                    ])->columns(2),
                            ]),
                    ])->columnSpanFull()
            ]);
    }
}
