<?php

namespace App\Filament\Resources\Reviews\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;

class ReviewForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Tabs')
                    ->tabs([
                        Tabs\Tab::make('Thông tin đánh giá')
                            ->icon('heroicon-o-star')
                            ->schema([
                                Section::make('Khách hàng & Sản phẩm')
                                    ->schema([
                                        Select::make('product_id')
                                            ->label('Sản phẩm')
                                            ->helperText('Sản phẩm được đánh giá')
                                            ->relationship('product', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->required(),
                                        Select::make('user_id')
                                            ->label('Thành viên (Nếu có)')
                                            ->helperText('Liên kết với tài khoản thành viên trên hệ thống')
                                            ->relationship('user', 'name')
                                            ->searchable()
                                            ->preload(),
                                        TextInput::make('customer_name')
                                            ->label('Tên khách hiển thị')
                                            ->helperText('Tên sẽ hiển thị trên trang sản phẩm')
                                            ->required(),
                                        ToggleButtons::make('rating')
                                            ->label('Chấm điểm (Sao)')
                                            ->helperText('Mức độ hài lòng của khách hàng')
                                            ->inline()
                                            ->options([
                                                1 => '1 Sao',
                                                2 => '2 Sao',
                                                3 => '3 Sao',
                                                4 => '4 Sao',
                                                5 => '5 Sao',
                                            ])
                                            ->colors([
                                                1 => 'danger',
                                                2 => 'danger',
                                                3 => 'warning',
                                                4 => 'success',
                                                5 => 'success',
                                            ])
                                            ->required(),
                                    ])->columns(2),
                            ]),

                        Tabs\Tab::make('Nội dung & Trạng thái')
                            ->icon('heroicon-o-chat-bubble-bottom-center-text')
                            ->schema([
                                Section::make('Nhận xét chi tiết')
                                    ->schema([
                                        Textarea::make('content')
                                            ->label('Nội dung nhận xét')
                                            ->helperText('Chi tiết đánh giá của khách hàng')
                                            ->columnSpanFull()
                                            ->required(),
                                        Toggle::make('is_approved')
                                            ->label('Duyệt hiển thị')
                                            ->helperText('Bật để đánh giá này xuất hiện trên website')
                                            ->default(false),
                                    ]),
                            ]),
                    ])->columnSpanFull()
            ]);
    }
}
