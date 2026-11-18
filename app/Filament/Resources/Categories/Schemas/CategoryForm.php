<?php

namespace App\Filament\Resources\Categories\Schemas;

use Awcodes\Curator\Components\Forms\CuratorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CategoryForm
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
                                Section::make('Thông tin cơ bản')
                                    ->schema([
                                        Select::make('parent_id')
                                            ->label('Danh mục cha')
                                            ->helperText('Chọn danh mục cấp trên (để trống nếu là danh mục gốc)')
                                            ->relationship('parent', 'name')
                                            ->searchable()
                                            ->preload(),
                                        TextInput::make('name')
                                            ->label('Tên danh mục')
                                            ->helperText('Tên hiển thị của danh mục')
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn (string $operation, ?string $state, $set) => $operation === 'create' ? $set('slug', Str::slug($state ?? '')) : null),
                                        TextInput::make('slug')
                                            ->label('Đường dẫn (Slug)')
                                            ->helperText('Đường dẫn thân thiện SEO')
                                            ->required()
                                            ->unique(ignoreRecord: true),
                                        Textarea::make('description')
                                            ->label('Mô tả')
                                            ->helperText('Mô tả ngắn gọn về danh mục này')
                                            ->columnSpanFull(),
                                    ])->columns(2),
                            ]),

                        Tabs\Tab::make('Hình ảnh & Cấu hình')
                            ->icon('heroicon-o-photo')
                            ->schema([
                                Section::make('Hình ảnh')
                                    ->schema([
                                        CuratorPicker::make('image_url')
                                            ->label('Hình đại diện')
                                            ->directory('danh-muc-san-pham')
                                            ->helperText('Hình ảnh minh họa cho danh mục')
                                            ->columnSpanFull(),
                                    ]),
                                Section::make('Cấu hình hiển thị')
                                    ->schema([
                                        Toggle::make('is_active')
                                            ->label('Hiển thị')
                                            ->helperText('Bật để hiển thị danh mục trên website')
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

                        Tabs\Tab::make('Tối ưu hóa SEO')
                            ->icon('heroicon-o-magnifying-glass')
                            ->schema([
                                Section::make('SEO')
                                    ->schema([
                                        TextInput::make('seo_title')
                                            ->label('Tiêu đề SEO')
                                            ->helperText('Tiêu đề tùy chỉnh cho thẻ meta title'),
                                        TextInput::make('seo_keywords')
                                            ->label('Từ khóa SEO')
                                            ->helperText('Các từ khóa cách nhau bằng dấu phẩy'),
                                        Textarea::make('seo_description')
                                            ->label('Mô tả SEO')
                                            ->helperText('Mô tả ngắn hiển thị trên kết quả tìm kiếm Google')
                                            ->columnSpanFull(),
                                    ])->columns(2),
                            ]),
                    ])->columnSpanFull()
            ]);
    }
}
