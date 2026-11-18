<?php

namespace App\Filament\Resources\Contents\Schemas;

use Awcodes\Curator\Components\Forms\CuratorPicker;
use Awcodes\Curator\Components\Forms\RichEditor\AttachCuratorMediaPlugin;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ContentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Tabs')
                    ->tabs([
                        Tabs\Tab::make('Nội dung bài viết')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Section::make('Thông tin cơ bản')
                                    ->schema([
                                        TextInput::make('title')
                                            ->label('Tiêu đề')
                                            ->helperText('Tiêu đề chính của bài viết')
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn (string $operation, $set, ?string $state) => $operation === 'create' ? $set('slug', Str::slug($state ?? '')) : null),
                                        TextInput::make('slug')
                                            ->label('URL Bài viết')
                                            ->helperText('Đường dẫn thân thiện SEO')
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->validationMessages([
                                                'unique' => 'Đường dẫn (Slug) bài viết này đã tồn tại, vui lòng chỉnh lại.',
                                            ]),
                                        Select::make('content_type_id')
                                            ->label('Phân loại')
                                            ->helperText('Chọn loại bài viết')
                                            ->relationship('contentType', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->required(),
                                        Select::make('content_category_id')
                                            ->label('Danh mục')
                                            ->helperText('Chọn danh mục chứa bài viết')
                                            ->relationship('contentCategory', 'name')
                                            ->searchable()
                                            ->preload(),
                                        Textarea::make('summary')
                                            ->label('Tóm tắt ngắn')
                                            ->helperText('Đoạn giới thiệu tóm tắt nội dung bài viết')
                                            ->columnSpanFull(),
                                    ])->columns(2),

                                Section::make('Nội dung Chính')
                                    ->schema([
                                        RichEditor::make('body')
                                            ->label('Soạn thảo nội dung')
                                            ->helperText('Trình soạn thảo bài viết chi tiết')
                                            ->columnSpanFull()
                                            ->fileAttachmentsDisk('public')
                                            ->fileAttachmentsDirectory('bai-viet')
                                            ->fileAttachmentsMaxSize(51200)
                                            ->plugins([
                                                AttachCuratorMediaPlugin::make(),
                                            ]),
                                    ]),
                            ]),

                        Tabs\Tab::make('Hình ảnh & Cấu hình')
                            ->icon('heroicon-o-photo')
                            ->schema([
                                Section::make('Hình ảnh')
                                    ->schema([
                                        CuratorPicker::make('image_url')
                                            ->label('Hình ảnh đại diện bài viết')
                                            ->directory('bai-viet')
                                            ->helperText('Hình ảnh thumbnail cho bài viết')
                                            ->columnSpanFull(),
                                    ]),
                                Section::make('Trạng thái')
                                    ->schema([
                                        Toggle::make('is_active')
                                            ->label('Đăng bài (Hiển thị)')
                                            ->helperText('Bật để công khai bài viết')
                                            ->default(true)
                                            ->required(),
                                        TextInput::make('view_count')
                                            ->label('Lượt xem')
                                            ->helperText('Số lượt đọc bài viết')
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
                    ])->columnSpanFull(),
            ]);
    }
}
