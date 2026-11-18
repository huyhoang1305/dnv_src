<?php

namespace App\Filament\Resources\ContentCategories\Schemas;

use Awcodes\Curator\Components\Forms\CuratorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Illuminate\Support\Str;

class ContentCategoryForm
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
                                        TextInput::make('name')
                                            ->label('Tên danh mục')
                                            ->helperText('Tên hiển thị của danh mục bài viết')
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn ($set, ?string $state) => $set('slug', Str::slug($state ?? ''))),
                                        TextInput::make('slug')
                                            ->label('Đường dẫn (Slug)')
                                            ->helperText('Đường dẫn thân thiện SEO (tự động tạo)')
                                            ->required()
                                            ->unique(ignoreRecord: true),
                                        \Filament\Forms\Components\Select::make('content_type_id')
                                            ->label('Thuộc Nhóm Phân Loại')
                                            ->helperText('Chọn nhóm phân loại lớn cho danh mục này')
                                            ->relationship('contentType', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->required(),
                                        TextInput::make('parent_id')
                                            ->label('Danh mục cha ID')
                                            ->helperText('ID của danh mục cấp trên (nếu có)')
                                            ->numeric(),
                                        Textarea::make('description')
                                            ->label('Mô tả danh mục')
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
                                            ->directory('danh-muc-bai-viet')
                                            ->helperText('Ảnh đại diện cho danh mục')
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
