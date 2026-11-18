<?php

namespace App\Filament\Resources\Products\Schemas;

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

class ProductForm
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
                                        Select::make('categories')
                                            ->label('Danh mục')
                                            ->helperText('Chọn một hoặc nhiều danh mục phù hợp cho sản phẩm')
                                            ->relationship('categories', 'name')
                                            ->multiple()
                                            ->searchable()
                                            ->preload()
                                            ->required(),
                                        TextInput::make('name')
                                            ->label('Tên sản phẩm')
                                            ->helperText('Tên hiển thị chính của sản phẩm trên website')
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn (string $operation, ?string $state, $set) => $operation === 'create' ? $set('slug', Str::slug($state ?? '')) : null),
                                        TextInput::make('slug')
                                            ->label('Đường dẫn (Slug)')
                                            ->helperText('Đường dẫn thân thiện SEO (tự động tạo từ tên sản phẩm)')
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->validationMessages([
                                                'unique' => 'Đường dẫn (Slug) này đã tồn tại trên sản phẩm khác, vui lòng chỉnh lại.',
                                            ]),
                                        TextInput::make('sku')
                                            ->label('Mã sản phẩm (SKU)')
                                            ->helperText('Mã định danh duy nhất của sản phẩm trong kho (không được trùng với sản phẩm đã có)')
                                            ->unique(ignoreRecord: true)
                                            ->validationMessages([
                                                'unique' => 'Mã sản phẩm (SKU) này đã tồn tại trên hệ thống, vui lòng nhập mã khác.',
                                            ]),
                                        TextInput::make('unit')
                                            ->label('Đơn vị tính')
                                            ->placeholder('VD: Túi, Gói, Hộp, Chai, Thùng, Kg...')
                                            ->helperText('Đơn vị tính hiển thị của sản phẩm (VD: Túi, Gói, Hộp, Chai, Cái...)'),
                                        TextInput::make('weight')
                                            ->label('Trọng lượng / Thể tích')
                                            ->placeholder('VD: 500g, 5kg, 1 Lít, 350ml...')
                                            ->helperText('Trọng lượng hoặc thể tích đóng gói của sản phẩm'),
                                        Textarea::make('description')
                                            ->label('Mô tả ngắn')
                                            ->helperText('Mô tả ngắn gọn về sản phẩm (thường hiển thị ở thẻ sản phẩm hoặc đầu trang chi tiết)')
                                            ->columnSpanFull(),
                                        RichEditor::make('content')
                                            ->label('Nội dung chi tiết (Bài viết)')
                                            ->helperText('Thông tin chi tiết, thông số kỹ thuật, bài viết giới thiệu về sản phẩm')
                                            ->columnSpanFull()
                                            ->fileAttachmentsDisk('public')
                                            ->fileAttachmentsDirectory('san-pham')
                                            ->fileAttachmentsMaxSize(51200)
                                            ->plugins([
                                                AttachCuratorMediaPlugin::make(),
                                            ]),
                                    ])->columns(2),
                            ]),

                        Tabs\Tab::make('Hình ảnh')
                            ->icon('heroicon-o-photo')
                            ->schema([
                                Section::make('Hình ảnh sản phẩm')
                                    ->schema([
                                        CuratorPicker::make('image_url')
                                            ->label('Hình đại diện chính')
                                            ->directory('san-pham')
                                            ->helperText('Hình ảnh minh họa chính của sản phẩm (tỷ lệ 1:1)'),
                                        CuratorPicker::make('gallery')
                                            ->label('Bộ sưu tập ảnh sản phẩm (Gallery)')
                                            ->directory('san-pham')
                                            ->helperText('Các hình ảnh ở góc độ khác của sản phẩm')
                                            ->multiple()
                                            ->columnSpanFull(),
                                    ])->columns(2),
                            ]),

                        Tabs\Tab::make('Cấu hình & Giá cả')
                            ->icon('heroicon-o-currency-dollar')
                            ->schema([
                                Section::make('Giá cả & Cấu hình')
                                    ->schema([
                                        TextInput::make('base_price')
                                            ->label('Giá gốc (VND)')
                                            ->helperText('Bắt buộc lớn hơn 0đ (hệ thống chặn lưu và bật bán sản phẩm giá 0đ)')
                                            ->numeric()
                                            ->minValue(1)
                                            ->gt(0)
                                            ->prefix('₫')
                                            ->placeholder('VD: 45000')
                                            ->required()
                                            ->validationMessages([
                                                'required' => 'Vui lòng nhập giá gốc cho sản phẩm.',
                                                'min' => 'Không cho phép lưu sản phẩm giá 0đ. Vui lòng nhập giá lớn hơn 0đ.',
                                                'gt' => 'Không cho phép lưu sản phẩm giá 0đ. Vui lòng nhập giá lớn hơn 0đ.',
                                            ]),
                                        TextInput::make('sale_price')
                                            ->label('Giá khuyến mãi (VND)')
                                            ->helperText('Giá sau khi đã giảm (lớn hơn 0đ và nhỏ hơn hoặc bằng giá gốc; để trống nếu không khuyến mãi)')
                                            ->numeric()
                                            ->minValue(1)
                                            ->gt(0)
                                            ->lte('base_price')
                                            ->prefix('₫')
                                            ->validationMessages([
                                                'min' => 'Giá khuyến mãi phải lớn hơn 0đ (hoặc để trống nếu không giảm giá).',
                                                'gt' => 'Giá khuyến mãi phải lớn hơn 0đ (hoặc để trống nếu không giảm giá).',
                                                'lte' => 'Giá khuyến mãi không được lớn hơn giá gốc.',
                                            ]),
                                        TextInput::make('stock_quantity')
                                            ->label('Hàng tồn (Số lượng tồn kho)')
                                            ->helperText('Số lượng sản phẩm hiện còn trong kho')
                                            ->numeric()
                                            ->minValue(0)
                                            ->default(100)
                                            ->required(),
                                        TextInput::make('sold_count')
                                            ->label('Hàng bán được (Số lượng đã bán)')
                                            ->helperText('Tổng số lượng sản phẩm đã bán ra')
                                            ->numeric()
                                            ->minValue(0)
                                            ->default(0),
                                        Toggle::make('is_active')
                                            ->label('Kích hoạt')
                                            ->helperText('Bật để hiển thị sản phẩm trên website')
                                            ->default(true),
                                        Toggle::make('is_featured')
                                            ->label('Nổi bật')
                                            ->helperText('Đánh dấu để hiển thị trong mục sản phẩm nổi bật')
                                            ->default(false),
                                        Toggle::make('is_flash_sale')
                                            ->label('Flash Sale (Trang chủ)')
                                            ->helperText('Bật để đưa sản phẩm vào khu vực Flash Sale trên trang chủ')
                                            ->default(false),
                                        TextInput::make('sort_order')
                                            ->label('Thứ tự hiển thị')
                                            ->helperText('Số càng nhỏ thì sản phẩm càng được xếp lên trên')
                                            ->numeric()
                                            ->default(0),
                                        TextInput::make('view_count')
                                            ->label('Lượt xem')
                                            ->helperText('Số lượt khách hàng đã xem sản phẩm này')
                                            ->numeric()
                                            ->default(0)
                                            ->disabled(),
                                    ])->columns(2),
                            ]),

                        Tabs\Tab::make('Tối ưu hóa SEO')
                            ->icon('heroicon-o-magnifying-glass')
                            ->schema([
                                Section::make('SEO')
                                    ->schema([
                                        TextInput::make('seo_title')
                                            ->label('Tiêu đề SEO')
                                            ->helperText('Tiêu đề tùy chỉnh cho thẻ meta title (để trống sẽ dùng tên sản phẩm)'),
                                        TextInput::make('seo_keywords')
                                            ->label('Từ khóa SEO')
                                            ->helperText('Các từ khóa tìm kiếm, cách nhau bằng dấu phẩy'),
                                        Textarea::make('seo_description')
                                            ->label('Mô tả SEO')
                                            ->helperText('Mô tả ngắn hiển thị trên kết quả tìm kiếm Google (tối đa 160 ký tự)')
                                            ->columnSpanFull(),
                                    ])->columns(2),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
