<?php

namespace App\Filament\Resources\ContentTypes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Illuminate\Support\Str;

class ContentTypeForm
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
                                Section::make('Thông tin Phân loại nội dung')
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Tên phân loại')
                                            ->helperText('Tên hiển thị của nhóm phân loại (VD: Bài viết, Tuyển dụng)')
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn ($set, ?string $state) => $set('slug', Str::slug($state ?? ''))),
                                        TextInput::make('slug')
                                            ->label('Đường dẫn (Slug)')
                                            ->helperText('Đường dẫn thân thiện SEO')
                                            ->required()
                                            ->unique(ignoreRecord: true),
                                        Textarea::make('description')
                                            ->label('Mô tả chung')
                                            ->helperText('Giới thiệu ngắn về nhóm phân loại này')
                                            ->columnSpanFull(),
                                        Toggle::make('is_active')
                                            ->label('Hiển thị')
                                            ->helperText('Bật để kích hoạt nhóm phân loại')
                                            ->default(true)
                                            ->required(),
                                        TextInput::make('sort_order')
                                            ->label('Thứ tự sắp xếp')
                                            ->helperText('Số càng nhỏ xếp càng lên trên')
                                            ->required()
                                            ->numeric()
                                            ->default(0),
                                    ])->columns(2),
                            ]),
                    ])->columnSpanFull()
            ]);
    }
}
