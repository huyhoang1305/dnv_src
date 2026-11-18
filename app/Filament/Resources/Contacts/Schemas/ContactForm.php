<?php

namespace App\Filament\Resources\Contacts\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ToggleButtons;

class ContactForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Tabs')
                    ->tabs([
                        Tabs\Tab::make('Thông tin liên hệ')
                            ->icon('heroicon-o-user')
                            ->schema([
                                Section::make('Thông tin khách hàng')
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Họ và tên')
                                            ->helperText('Tên người gửi liên hệ')
                                            ->maxLength(255),
                                        TextInput::make('phone')
                                            ->label('Số điện thoại')
                                            ->helperText('Số điện thoại liên lạc')
                                            ->tel()
                                            ->maxLength(255),
                                        TextInput::make('email')
                                            ->label('Email')
                                            ->helperText('Địa chỉ email phản hồi')
                                            ->email()
                                            ->maxLength(255),
                                        TextInput::make('subject')
                                            ->label('Tiêu đề')
                                            ->helperText('Vấn đề khách hàng cần liên hệ')
                                            ->maxLength(255),
                                    ])->columns(2),
                            ]),

                        Tabs\Tab::make('Nội dung & Trạng thái')
                            ->icon('heroicon-o-chat-bubble-bottom-center-text')
                            ->schema([
                                Section::make('Trạng thái xử lý')
                                    ->schema([
                                        ToggleButtons::make('status')
                                            ->label('Trạng thái')
                                            ->helperText('Cập nhật tiến độ hỗ trợ khách hàng')
                                            ->inline()
                                            ->options([
                                                'pending' => 'Chờ xử lý',
                                                'processing' => 'Đang xử lý',
                                                'resolved' => 'Đã giải quyết',
                                            ])
                                            ->colors([
                                                'pending' => 'warning',
                                                'processing' => 'info',
                                                'resolved' => 'success',
                                            ])
                                            ->default('pending')
                                            ->required()
                                            ->columnSpanFull(),
                                    ]),
                                Section::make('Nội dung')
                                    ->schema([
                                        Textarea::make('message')
                                            ->label('Nội dung tin nhắn')
                                            ->helperText('Nội dung chi tiết khách hàng gửi')
                                            ->columnSpanFull()
                                            ->rows(5),
                                        Textarea::make('notes')
                                            ->label('Ghi chú (Nội bộ)')
                                            ->helperText('Ghi chú dành riêng cho quản trị viên theo dõi (khách không thấy)')
                                            ->columnSpanFull()
                                            ->rows(3),
                                    ]),
                            ]),
                    ])->columnSpanFull()
            ]);
    }
}
