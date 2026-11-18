<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Section;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Tabs')
                    ->tabs([
                        Tabs\Tab::make('Thông tin Tài khoản')
                            ->icon('heroicon-o-user')
                            ->schema([
                                Section::make('Bảo mật & Liên hệ')
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Họ và tên')
                                            ->helperText('Tên hiển thị của thành viên')
                                            ->required(),
                                        TextInput::make('phone')
                                            ->label('Số điện thoại')
                                            ->helperText('Số điện thoại liên hệ / đăng nhập')
                                            ->tel()
                                            ->maxLength(25),
                                        TextInput::make('email')
                                            ->label('Địa chỉ Email')
                                            ->helperText('Dùng để đăng nhập hệ thống')
                                            ->email()
                                            ->required()
                                            ->unique(ignoreRecord: true),
                                        TextInput::make('password')
                                            ->label('Mật khẩu')
                                            ->helperText('Để trống nếu không muốn thay đổi mật khẩu')
                                            ->password()
                                            ->required(fn (string $context): bool => $context === 'create')
                                            ->dehydrated(fn ($state) => filled($state)),
                                        TextInput::make('address')
                                            ->label('Địa chỉ liên hệ / giao hàng')
                                            ->maxLength(500)
                                            ->columnSpanFull(),
                                    ])->columns(2),
                            ]),

                        Tabs\Tab::make('Phân quyền & Trạng thái')
                            ->icon('heroicon-o-shield-check')
                            ->schema([
                                Section::make('Quyền truy cập')
                                    ->schema([
                                        \Filament\Forms\Components\Select::make('roles')
                                            ->label('Quyền hạn (Roles)')
                                            ->helperText('Chọn một hoặc nhiều vai trò cho tài khoản này')
                                            ->relationship('roles', 'name')
                                            ->multiple()
                                            ->preload()
                                            ->searchable(),
                                        \Filament\Forms\Components\Toggle::make('is_active')
                                            ->label('Cấp quyền đăng nhập Bảng điều khiển')
                                            ->helperText('Nếu tắt, tài khoản này sẽ không thể đăng nhập vào Admin')
                                            ->default(true),
                                    ])->columns(2),
                            ]),
                    ])->columnSpanFull()
            ]);
    }
}
