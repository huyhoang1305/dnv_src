<?php

namespace App\Filament\Resources\AttributeTypes\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class AttributeTypeForm
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
                                Section::make('Cấu hình Thuộc tính')
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Tên loại thuộc tính')
                                            ->helperText('Ví dụ: Dung tích, Quy cách, Khối lượng, Màu sắc')
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn (string $operation, ?string $state, $set) => $operation === 'create' ? $set('slug', Str::slug($state ?? '')) : null),
                                        TextInput::make('slug')
                                            ->label('Mã (Slug)')
                                            ->helperText('Định danh duy nhất (tự động tạo)')
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->validationMessages([
                                                'unique' => 'Mã (Slug) thuộc tính này đã tồn tại.',
                                            ]),
                                        TextInput::make('sort_order')
                                            ->label('Thứ tự sắp xếp')
                                            ->helperText('Số nhỏ ưu tiên xếp trước')
                                            ->numeric()
                                            ->default(0),
                                    ])->columns(3),

                                Section::make('Danh sách Giá trị thuộc tính')
                                    ->description('Thêm các tùy chọn cho loại thuộc tính này (VD: 500ml, 800ml, 1 Lít, Thùng 12 chai...)')
                                    ->schema([
                                        Repeater::make('values')
                                            ->label('Giá trị')
                                            ->relationship('values')
                                            ->schema([
                                                TextInput::make('value')
                                                    ->label('Giá trị hiển thị')
                                                    ->placeholder('VD: 500ml, 1 Lít, Đỏ...')
                                                    ->required(),
                                                ColorPicker::make('color_code')
                                                    ->label('Mã màu (tùy chọn)'),
                                                TextInput::make('sort_order')
                                                    ->label('Thứ tự')
                                                    ->numeric()
                                                    ->default(0),
                                            ])
                                            ->columns(3)
                                            ->reorderable('sort_order')
                                            ->defaultItems(0)
                                            ->addActionLabel('Thêm giá trị thuộc tính'),
                                    ]),
                            ]),
                    ])->columnSpanFull()
            ]);
    }
}
