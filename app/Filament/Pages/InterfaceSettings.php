<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Awcodes\Curator\Components\Forms\CuratorPicker;
use Awcodes\Curator\Components\Forms\RichEditor\AttachCuratorMediaPlugin;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions\Action;
use Filament\Actions\Action as HeaderAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Mail;

class InterfaceSettings extends Page implements HasForms
{
    use HasPageShield,InteractsWithForms;

    protected string $view = 'filament.pages.interface-settings';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return 'Cấu hình';
    }

    public function getTitle(): string|Htmlable
    {
        return 'Hệ thống Tuỳ chỉnh';
    }

    protected function getHeaderActions(): array
    {
        return [
            HeaderAction::make('save')
                ->label('Lưu lại Cấu hình')
                ->icon('heroicon-m-check-badge')
                ->color('primary')
                ->keyBindings(['mod+s'])
                ->action('save'),

            HeaderAction::make('testEmail')
                ->label('Test Gửi Mail')
                ->icon('heroicon-m-paper-airplane')
                ->color('warning')
                ->form([
                    TextInput::make('recipient')
                        ->label('Địa chỉ Email nhận thử nghiệm')
                        ->email()
                        ->default(fn () => data_get($this->data, 'general_settings.email.contact_email') ?? '2509roblox@gmail.com')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $this->sendTestEmail($data['recipient']);
                }),
        ];
    }

    public ?array $data = [];

    public function mount(): void
    {
        $setting = Setting::first();
        if ($setting) {
            $this->form->fill([
                'general_settings' => $setting->general_settings ?? [],
                'interface_settings' => $setting->interface_settings ?? [],
            ]);
        } else {
            $this->form->fill();
        }
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Cấu hình Hệ thống (General Settings)')
                    ->icon('heroicon-m-cog-6-tooth')
                    ->description('Bao gồm các thông số lõi, nhận diện thương hiệu và kết nối hệ thống bên thứ ba.')
                    ->collapsible()
                    ->schema([
                        Tabs::make('Hệ thống')
                            ->persistTabInQueryString('general_tabs')
                            ->tabs([
                                Tab::make('Thông tin website')
                                    ->icon('heroicon-m-information-circle')
                                    ->schema([
                                        TextInput::make('general_settings.site_name')
                                            ->label('Tên Doanh nghiệp / Thương hiệu')
                                            ->helperText('Sẽ hiển thị mặc định trên thẻ tiêu đề web')
                                            ->required(),
                                        CuratorPicker::make('general_settings.site_icon')
                                            ->label('Favicon (Ảnh thu nhỏ trên trình duyệt)')
                                            ->directory('logo')
                                            ->helperText('Biểu tượng nhỏ hiển thị trên tab trình duyệt (tỷ lệ 1:1)'),
                                    ])->columns(2),

                                Tab::make('Script')
                                    ->icon('heroicon-m-code-bracket')
                                    ->schema([
                                        Textarea::make('general_settings.scripts.header')
                                            ->label('Mã nhúng Header (Google Tag Manager, Facebook Pixel...)')
                                            ->helperText('Chèn thẻ <script> hoặc <link> vào cuối thẻ <head>')
                                            ->rows(5),
                                        Textarea::make('general_settings.scripts.body')
                                            ->label('Mã nhúng Body')
                                            ->helperText('Chèn mã nhúng vào ngay sau thẻ <body>')
                                            ->rows(5),
                                        Textarea::make('general_settings.scripts.footer')
                                            ->label('Mã nhúng Footer (Livechat, Tawk.to...)')
                                            ->helperText('Chèn mã nhúng vào trước thẻ đóng </body>')
                                            ->rows(5),
                                    ])->columns(1),

                                Tab::make('Bảo mật')
                                    ->icon('heroicon-m-shield-check')
                                    ->schema([
                                        Toggle::make('general_settings.security.block_inspect')
                                            ->label('Chặn F12 (Inspect Element) & Bôi đen văn bản')
                                            ->helperText('Hạn chế người dùng xem mã nguồn HTML'),
                                        Toggle::make('general_settings.security.block_copy')
                                            ->label('Chặn Chuột phải (Prevent Right-click)')
                                            ->helperText('Chống sao chép nội dung cơ bản'),
                                    ])->columns(2),

                                Tab::make('Email')
                                    ->icon('heroicon-m-envelope')
                                    ->schema([
                                        TextInput::make('general_settings.email.contact_email')
                                            ->label('Email Nhận Thông báo')
                                            ->email(),
                                        TextInput::make('general_settings.email.sender_email')
                                            ->label('Email Gửi Đi mặc định')
                                            ->email(),
                                        TextInput::make('general_settings.email.smtp_host')
                                            ->label('SMTP Host'),
                                        TextInput::make('general_settings.email.smtp_port')
                                            ->label('SMTP Port'),
                                        TextInput::make('general_settings.email.smtp_user')
                                            ->label('SMTP User'),
                                        TextInput::make('general_settings.email.smtp_password')
                                            ->label('SMTP Password')
                                            ->password(),
                                        Actions::make([
                                            Action::make('testSendEmailAction')
                                                ->label('⚡ Test Gửi Mail Thử Nghiệm')
                                                ->icon('heroicon-m-paper-airplane')
                                                ->color('warning')
                                                ->button()
                                                ->form([
                                                    TextInput::make('recipient')
                                                        ->label('Địa chỉ Email nhận thử nghiệm')
                                                        ->email()
                                                        ->default(fn () => data_get($this->data, 'general_settings.email.contact_email') ?? '2509roblox@gmail.com')
                                                        ->required(),
                                                ])
                                                ->action(function (array $data) {
                                                    $this->sendTestEmail($data['recipient']);
                                                }),
                                        ])->columnSpanFull(),
                                    ])->columns(2),

                                Tab::make('Thanh toán')
                                    ->icon('heroicon-m-currency-dollar')
                                    ->schema([
                                        TextInput::make('general_settings.payment.bank_name')
                                            ->label('Tên Ngân hàng (VD: Vietcombank)')
                                            ->helperText('Tên viết tắt hoặc tên đầy đủ của ngân hàng')
                                            ->columnSpan(1),
                                        TextInput::make('general_settings.payment.account_number')
                                            ->label('Số tài khoản')
                                            ->helperText('Số tài khoản nhận chuyển khoản')
                                            ->columnSpan(1),
                                        TextInput::make('general_settings.payment.account_name')
                                            ->label('Tên chủ tài khoản')
                                            ->helperText('Tên in hoa, không dấu (VD: NGUYEN VAN A)')
                                            ->columnSpan(1),
                                    ])->columns(2),

                                Tab::make('Hệ thống')
                                    ->icon('heroicon-m-wrench-screwdriver')
                                    ->schema([
                                        Toggle::make('general_settings.system.is_active')
                                            ->label('Kích hoạt Website (Bật để khách truy cập bình thường, Tắt để bảo trì)')
                                            ->default(true),
                                    ])->columns(1),
                            ]),
                    ]),

                Section::make('Cấu hình Giao diện (Interface Settings)')
                    ->icon('heroicon-m-paint-brush')
                    ->description('Cấu hình hiển thị trang chủ, SEO và trạng thái hiển thị web.')
                    ->collapsible()
                    ->schema([
                        Tabs::make('Giao diện')
                            ->persistTabInQueryString('interface_tabs')
                            ->tabs([
                                Tab::make('Header')
                                    ->icon('heroicon-m-bars-3-bottom-left')
                                    ->schema([
                                        Tabs::make('header_subtabs')
                                            ->tabs([
                                                Tab::make('Logo & Nhận diện')
                                                    ->icon('heroicon-m-photo')
                                                    ->schema([
                                                        CuratorPicker::make('interface_settings.header.logo')
                                                            ->label('Logo Header (Curator)')
                                                            ->directory('logo')
                                                            ->helperText('Chọn hoặc tải ảnh logo hiển thị trên thanh Header'),
                                                        TextInput::make('interface_settings.header.brand_name')
                                                            ->label('Tên thương hiệu')
                                                            ->placeholder('DOANH NGHIỆP VIỆT'),
                                                        TextInput::make('interface_settings.header.slogan')
                                                            ->label('Slogan phụ dưới Logo')
                                                            ->placeholder('Siêu thị nhập khẩu cao cấp'),
                                                        TextInput::make('interface_settings.header.search_placeholder')
                                                            ->label('Gợi ý thanh tìm kiếm')
                                                            ->placeholder('Tìm bánh kẹo, sữa tươi, trái cây, gia dụng...'),
                                                    ])->columns(2),

                                                Tab::make('Thanh thông báo (Top Bar)')
                                                    ->icon('heroicon-m-megaphone')
                                                    ->schema([
                                                        Toggle::make('interface_settings.header.topbar_active')
                                                            ->label('Kích hoạt hiển thị Topbar')
                                                            ->default(true),
                                                        TextInput::make('interface_settings.header.topbar_text')
                                                            ->label('Nội dung thông báo Topbar')
                                                            ->columnSpanFull(),
                                                        TextInput::make('interface_settings.header.topbar_hotline')
                                                            ->label('Hotline hiển thị Topbar')
                                                            ->placeholder('0834 400 400'),
                                                        TextInput::make('interface_settings.header.topbar_zalo')
                                                            ->label('Số Zalo hỗ trợ')
                                                            ->placeholder('037 424 0930'),
                                                        TextInput::make('interface_settings.header.topbar_zalo_link')
                                                            ->label('Đường dẫn Zalo trực tiếp')
                                                            ->placeholder('https://zalo.me/0374240930'),
                                                        TextInput::make('interface_settings.header.badge_1')
                                                            ->label('Cam kết 1')
                                                            ->placeholder('100% Chính hãng'),
                                                        TextInput::make('interface_settings.header.badge_2')
                                                            ->label('Cam kết 2')
                                                            ->placeholder('Hỏa tốc 1h'),
                                                    ])->columns(2),

                                                Tab::make('Menu điều hướng')
                                                    ->icon('heroicon-m-bars-3')
                                                    ->schema([
                                                        Repeater::make('interface_settings.header.nav_links')
                                                            ->label('Danh sách liên kết điều hướng Header')
                                                            ->helperText('Các mục menu hiển thị bên cạnh Danh mục và Thương hiệu')
                                                            ->schema([
                                                                TextInput::make('name')
                                                                    ->label('Tên menu')
                                                                    ->required(),
                                                                TextInput::make('url')
                                                                    ->label('Đường dẫn')
                                                                    ->required(),
                                                                TextInput::make('icon')
                                                                    ->label('Icon FontAwesome')
                                                                    ->placeholder('fa-solid fa-store'),
                                                                Toggle::make('target_blank')
                                                                    ->label('Mở tab mới'),
                                                            ])
                                                            ->columns(4)
                                                            ->columnSpanFull(),
                                                    ]),

                                                Tab::make('Hành động & CTA')
                                                    ->icon('heroicon-m-cursor-arrow-ripple')
                                                    ->schema([
                                                        Toggle::make('interface_settings.header.show_cart')
                                                            ->label('Hiển thị nút Giỏ hàng')
                                                            ->default(true),
                                                        Toggle::make('interface_settings.header.show_account')
                                                            ->label('Hiển thị nút Tài khoản / Đăng nhập')
                                                            ->default(true),
                                                        TextInput::make('interface_settings.header.cta_text')
                                                            ->label('Nút CTA Header (VD: Tư Vấn Báo Giá)'),
                                                        TextInput::make('interface_settings.header.cta_link')
                                                            ->label('Đường dẫn CTA Header'),
                                                    ])->columns(2),
                                            ]),
                                    ]),

                                Tab::make('Footer')
                                    ->icon('heroicon-m-document-arrow-down')
                                    ->schema([
                                        Tabs::make('footer_subtabs')
                                            ->tabs([
                                                Tab::make('Logo & Giới thiệu')
                                                    ->icon('heroicon-m-photo')
                                                    ->schema([
                                                        CuratorPicker::make('interface_settings.footer.logo')
                                                            ->label('Logo Footer (Curator)')
                                                            ->directory('logo')
                                                            ->helperText('Chọn ảnh logo cho chân trang')
                                                            ->columnSpanFull(),
                                                        Textarea::make('interface_settings.footer.intro_text')
                                                            ->label('Mô tả thương hiệu chân trang')
                                                            ->rows(3)
                                                            ->columnSpanFull(),
                                                        TextInput::make('interface_settings.footer.copyright')
                                                            ->label('Bản quyền Footer (Copyright)')
                                                            ->columnSpanFull(),
                                                    ])->columns(2),

                                                Tab::make('Thông tin liên hệ')
                                                    ->icon('heroicon-m-phone')
                                                    ->schema([
                                                        TextInput::make('interface_settings.footer.company_name')
                                                            ->label('Tên Cty')
                                                            ->columnSpanFull(),
                                                        TextInput::make('interface_settings.footer.tax_code')
                                                            ->label('Mã số thuế')
                                                            ->columnSpanFull(),
                                                        TextInput::make('interface_settings.footer.contact_address')
                                                            ->label('Địa chỉ trụ sở')
                                                            ->columnSpanFull(),
                                                        TextInput::make('interface_settings.footer.contact_office')
                                                            ->label('Địa chỉ liên hệ / VPGD')
                                                            ->columnSpanFull(),
                                                        TextInput::make('interface_settings.footer.contact_phone')
                                                            ->label('Hotline chăm sóc khách hàng'),
                                                        TextInput::make('interface_settings.footer.contact_email')
                                                            ->label('Email chăm sóc khách hàng')
                                                            ->email(),
                                                        TextInput::make('interface_settings.footer.contact_website')
                                                            ->label('Website chính thức (VD: https://dnv1.biz/)')
                                                            ->url(),
                                                        TextInput::make('interface_settings.footer.contact_hours')
                                                            ->label('Giờ mở cửa hoạt động')
                                                            ->columnSpanFull(),
                                                    ])->columns(2),

                                                Tab::make('Mạng xã hội')
                                                    ->icon('heroicon-m-share')
                                                    ->schema([
                                                        TextInput::make('interface_settings.footer.social_facebook')
                                                            ->label('Link Facebook'),
                                                        TextInput::make('interface_settings.footer.social_zalo')
                                                            ->label('Link Zalo OA / Cá nhân'),
                                                        TextInput::make('interface_settings.footer.social_instagram')
                                                            ->label('Link Instagram'),
                                                        TextInput::make('interface_settings.footer.social_phone')
                                                            ->label('Hotline gọi nhanh'),
                                                    ])->columns(2),

                                                Tab::make('Cột liên kết')
                                                    ->icon('heroicon-m-link')
                                                    ->schema([
                                                        Repeater::make('interface_settings.footer.quick_links')
                                                            ->label('Cột 3: Liên kết nhanh chân trang')
                                                            ->schema([
                                                                TextInput::make('label')->label('Tiêu đề liên kết')->required(),
                                                                TextInput::make('url')->label('Đường dẫn URL')->required(),
                                                            ])
                                                            ->columns(2)
                                                            ->columnSpanFull(),
                                                    ]),

                                                Tab::make('Đánh giá khách hàng')
                                                    ->icon('heroicon-m-star')
                                                    ->schema([
                                                        Repeater::make('interface_settings.footer.ratings')
                                                            ->label('Hộp đánh giá khách hàng (Ratings & Reviews)')
                                                            ->schema([
                                                                TextInput::make('platform')
                                                                    ->label('Nền tảng (VD: Google Review, TripAdvisor, Shopee Mall)')
                                                                    ->required(),
                                                                TextInput::make('score')
                                                                    ->label('Điểm số (VD: 4.8, 5.0)')
                                                                    ->required(),
                                                                TextInput::make('icon')
                                                                    ->label('Icon FontAwesome (VD: fa-brands fa-google, fa-solid fa-compass)')
                                                                    ->required(),
                                                            ])
                                                            ->columns(3)
                                                            ->columnSpanFull(),
                                                    ]),

                                                Tab::make('Nút liên hệ nổi')
                                                    ->icon('heroicon-m-chat-bubble-left-right')
                                                    ->schema([
                                                        Toggle::make('interface_settings.footer.floating_call_active')
                                                            ->label('Bật nút gọi Hotline nổi góc màn hình')
                                                            ->default(true),
                                                        TextInput::make('interface_settings.footer.floating_call_phone')
                                                            ->label('Số Hotline nổi')
                                                            ->placeholder('082 333 6868'),
                                                        Toggle::make('interface_settings.footer.floating_zalo_active')
                                                            ->label('Bật nút chat Zalo nổi góc màn hình')
                                                            ->default(true),
                                                        TextInput::make('interface_settings.footer.floating_zalo_link')
                                                            ->label('Link Zalo chat')
                                                            ->placeholder('https://zalo.me/0374240930'),
                                                    ])->columns(2),
                                            ]),
                                    ]),

                                Tab::make('Trang chủ')
                                    ->icon('heroicon-m-home')
                                    ->schema([
                                        Tabs::make('home_subtabs')
                                            ->tabs([
                                                Tab::make('Tiêu đề & Hero Banner')
                                                    ->icon('heroicon-m-photo')
                                                    ->schema([
                                                        TextInput::make('interface_settings.home.h1_title')
                                                            ->label('Tiêu đề H1 trang chủ')
                                                            ->placeholder('DOANH NGHIỆP VIỆT | Siêu Thị Thực Phẩm, Hàng Tiêu Dùng...')
                                                            ->columnSpanFull(),
                                                        Textarea::make('interface_settings.home.h1_subtitle')
                                                            ->label('Phụ đề mô tả H1')
                                                            ->rows(2)
                                                            ->columnSpanFull(),

                                                        Section::make('Slider Banners (Bên trái)')
                                                            ->description('Danh sách banner chạy tự động trong slider chính (8 cột)')
                                                            ->collapsible()
                                                            ->schema([
                                                                Repeater::make('interface_settings.home.slider_banners')
                                                                    ->label('Các Slide Banner chính')
                                                                    ->schema([
                                                                        CuratorPicker::make('image')
                                                                            ->label('Ảnh Banner (Curator)')
                                                                            ->directory('banners'),
                                                                        TextInput::make('title')
                                                                            ->label('Tiêu đề / Alt text')
                                                                            ->placeholder('VD: Táo Đỏ New Zealand THV')
                                                                            ->required(),
                                                                        TextInput::make('link')
                                                                            ->label('Đường dẫn khi click')
                                                                            ->placeholder('/danh-muc'),
                                                                    ])
                                                                    ->columns(3)
                                                                    ->columnSpanFull(),
                                                            ]),

                                                        Section::make('Banners phụ (Bên phải - 2 Banners)')
                                                            ->description('2 banner tĩnh xếp dọc bên cạnh slider chính (4 cột)')
                                                            ->collapsible()
                                                            ->schema([
                                                                CuratorPicker::make('interface_settings.home.side_banner_1_image')
                                                                    ->label('Ảnh Banner phụ 1')
                                                                    ->directory('banners'),
                                                                TextInput::make('interface_settings.home.side_banner_1_link')
                                                                    ->label('Link Banner phụ 1')
                                                                    ->placeholder('/danh-muc'),
                                                                CuratorPicker::make('interface_settings.home.side_banner_2_image')
                                                                    ->label('Ảnh Banner phụ 2')
                                                                    ->directory('banners'),
                                                                TextInput::make('interface_settings.home.side_banner_2_link')
                                                                    ->label('Link Banner phụ 2')
                                                                    ->placeholder('/danh-muc'),
                                                            ])->columns(2),
                                                    ]),

                                                Tab::make('DNV Mall & Săn Deal')
                                                    ->icon('heroicon-m-building-storefront')
                                                    ->schema([
                                                        Section::make('Cấu hình Khối DNV Mall')
                                                            ->description('Quản lý hiển thị gian hàng Mall, banner khuyến mãi lớn và 8 deals thương hiệu.')
                                                            ->collapsible()
                                                            ->schema([
                                                                Toggle::make('interface_settings.home.mall_active')
                                                                    ->label('Hiển thị khối DNV Mall trên trang chủ')
                                                                    ->default(true),
                                                                TextInput::make('interface_settings.home.mall_title')
                                                                    ->label('Tiêu đề thương hiệu Mall')
                                                                    ->default('DNV MALL')
                                                                    ->required(),
                                                                TextInput::make('interface_settings.home.mall_badge_1')
                                                                    ->label('Cam kết 1')
                                                                    ->default('Trả Hàng Miễn Phí 15 Ngày'),
                                                                TextInput::make('interface_settings.home.mall_badge_2')
                                                                    ->label('Cam kết 2')
                                                                    ->default('Hàng Chính Hãng 100%'),
                                                                TextInput::make('interface_settings.home.mall_badge_3')
                                                                    ->label('Cam kết 3')
                                                                    ->default('Miễn Phí Vận Chuyển'),
                                                                TextInput::make('interface_settings.home.mall_link_text')
                                                                    ->label('Chữ liên kết xem tất cả')
                                                                    ->default('Xem Tất Cả'),
                                                                TextInput::make('interface_settings.home.mall_link_url')
                                                                    ->label('Đường dẫn xem tất cả')
                                                                    ->default('/danh-muc'),
                                                            ])->columns(2),

                                                        Section::make('Banner Quảng Cáo Lớn (Cột bên trái)')
                                                            ->schema([
                                                                CuratorPicker::make('interface_settings.home.mall_banner')
                                                                    ->label('Ảnh Banner DNV Mall')
                                                                    ->directory('banners')
                                                                    ->helperText('Ảnh banner minh họa chương trình khuyến mãi Săn Deal DNV Mall'),
                                                                TextInput::make('interface_settings.home.mall_banner_link')
                                                                    ->label('Đường dẫn khi click vào banner')
                                                                    ->default('/danh-muc'),
                                                            ])->columns(2),

                                                        Section::make('Danh sách 8 Deal Thương Hiệu (Lưới bên phải)')
                                                            ->schema([
                                                                Repeater::make('interface_settings.home.mall_deals')
                                                                    ->label('Các Deal Thương Hiệu')
                                                                    ->schema([
                                                                        CuratorPicker::make('image')
                                                                            ->label('Ảnh sản phẩm deal')
                                                                            ->directory('banners'),
                                                                        TextInput::make('brand')
                                                                            ->label('Tên thương hiệu')
                                                                            ->placeholder('VD: DNV FOOD, VINAMILK...')
                                                                            ->required(),
                                                                        TextInput::make('promo')
                                                                            ->label('Khuyến mãi / Ưu đãi')
                                                                            ->placeholder('VD: Ưu đãi đến 50%, Mua 1 tặng 1...')
                                                                            ->required(),
                                                                        TextInput::make('link')
                                                                            ->label('Đường dẫn khi click')
                                                                            ->placeholder('/danh-muc/nong-san')
                                                                            ->required(),
                                                                    ])
                                                                    ->columns(2)
                                                                    ->columnSpanFull(),
                                                            ]),
                                                    ]),

                                                Tab::make('Mã Giảm Giá & Ưu Đãi')
                                                    ->icon('heroicon-m-ticket')
                                                    ->schema([
                                                        Section::make('Cấu hình Khối Mã Giảm Giá DNV')
                                                            ->schema([
                                                                Toggle::make('interface_settings.home.show_vouchers')
                                                                    ->label('Hiển thị khối mã giảm giá trên trang chủ')
                                                                    ->default(false),
                                                                TextInput::make('interface_settings.home.vouchers_title')
                                                                    ->label('Tiêu đề chính khối Voucher')
                                                                    ->default('MÃ GIẢM GIÁ & VOUCHER DNV'),
                                                                TextInput::make('interface_settings.home.vouchers_badge')
                                                                    ->label('Huy hiệu góc')
                                                                    ->default('SIÊU ƯU ĐÃI'),
                                                                TextInput::make('interface_settings.home.vouchers_subtitle')
                                                                    ->label('Dòng mô tả phụ')
                                                                    ->default('Lưu ngay mã giảm giá độc quyền để tự động áp dụng khi thanh toán đơn hàng')
                                                                    ->columnSpanFull(),
                                                                TextInput::make('interface_settings.home.vouchers_link_text')
                                                                    ->label('Chữ liên kết xem giỏ hàng / voucher')
                                                                    ->default('Xem Giỏ Hàng & Voucher'),
                                                            ])->columns(2),
                                                    ]),

                                                Tab::make('Banners Khối')
                                                    ->icon('heroicon-m-rectangle-stack')
                                                    ->schema([
                                                        Section::make('Khối Danh Mục Nổi Bật 📂')
                                                            ->collapsible()
                                                            ->schema([
                                                                Toggle::make('interface_settings.home.categories_active')
                                                                    ->label('Hiển thị khối danh mục nổi bật')
                                                                    ->default(true),
                                                                TextInput::make('interface_settings.home.categories_title')
                                                                    ->label('Tiêu đề')
                                                                    ->default('Danh Mục Nổi Bật'),
                                                                TextInput::make('interface_settings.home.categories_subtitle')
                                                                    ->label('Phụ đề mô tả')
                                                                    ->default('Khám phá danh mục hàng hóa thiết yếu và nông sản xuất khẩu')
                                                                    ->columnSpanFull(),
                                                            ])->columns(2),

                                                        Section::make('Khối Flash Sale 🔥')
                                                            ->collapsible()
                                                            ->schema([
                                                                Toggle::make('interface_settings.home.show_flash_sale_bg')
                                                                    ->label('Hiển thị Banner phía trên khối Flash Sale')
                                                                    ->default(false)
                                                                    ->columnSpanFull(),
                                                                CuratorPicker::make('interface_settings.home.flash_sale_bg')
                                                                    ->label('Ảnh Banner Flash Sale (Header Image)')
                                                                    ->directory('banners'),
                                                                TextInput::make('interface_settings.home.flash_sale_title')
                                                                    ->label('Tiêu đề')
                                                                    ->default('Flash Sale'),
                                                                TextInput::make('interface_settings.home.flash_sale_icon')
                                                                    ->label('Biểu tượng / Emoji')
                                                                    ->default('🔥'),
                                                                TextInput::make('interface_settings.home.flash_sale_link_text')
                                                                    ->label('Chữ liên kết xem thêm')
                                                                    ->default('Xem thêm'),
                                                                TextInput::make('interface_settings.home.flash_sale_link_url')
                                                                    ->label('Đường dẫn xem thêm')
                                                                    ->default('/danh-muc'),
                                                            ])->columns(2),

                                                        Section::make('Khối Dành riêng cho bạn (New Arrivals) ✨')
                                                            ->collapsible()
                                                            ->schema([
                                                                Toggle::make('interface_settings.home.show_new_arrivals_bg')
                                                                    ->label('Hiển thị Banner phía trên khối Gợi ý hôm nay')
                                                                    ->default(false)
                                                                    ->columnSpanFull(),
                                                                CuratorPicker::make('interface_settings.home.new_arrivals_bg')
                                                                    ->label('Ảnh Banner New Arrivals')
                                                                    ->directory('banners'),
                                                                TextInput::make('interface_settings.home.new_arrivals_title')
                                                                    ->label('Tiêu đề')
                                                                    ->default('Dành riêng cho bạn'),
                                                                TextInput::make('interface_settings.home.new_arrivals_icon')
                                                                    ->label('Biểu tượng / Emoji')
                                                                    ->default('✨'),
                                                                TextInput::make('interface_settings.home.new_arrivals_link_text')
                                                                    ->label('Chữ liên kết xem tất cả')
                                                                    ->default('Xem tất cả'),
                                                                TextInput::make('interface_settings.home.new_arrivals_link_url')
                                                                    ->label('Đường dẫn xem tất cả')
                                                                    ->default('/danh-muc'),
                                                            ])->columns(2),

                                                        Section::make('Khối Bài viết mới cho bạn (Blogs) 📰')
                                                            ->collapsible()
                                                            ->schema([
                                                                Toggle::make('interface_settings.home.show_blogs_bg')
                                                                    ->label('Hiển thị Banner phía trên khối Bài viết')
                                                                    ->default(false)
                                                                    ->columnSpanFull(),
                                                                CuratorPicker::make('interface_settings.home.blogs_bg')
                                                                    ->label('Ảnh Banner Bài viết')
                                                                    ->directory('banners'),
                                                                TextInput::make('interface_settings.home.blogs_title')
                                                                    ->label('Tiêu đề')
                                                                    ->default('Bài viết mới cho bạn'),
                                                                TextInput::make('interface_settings.home.blogs_icon')
                                                                    ->label('Biểu tượng / Emoji')
                                                                    ->default('📰'),
                                                                TextInput::make('interface_settings.home.blogs_link_text')
                                                                    ->label('Chữ liên kết xem tất cả')
                                                                    ->default('Xem tất cả'),
                                                                TextInput::make('interface_settings.home.blogs_link_url')
                                                                    ->label('Đường dẫn xem tất cả')
                                                                    ->default('/blog'),
                                                            ])->columns(2),
                                                    ]),

                                                Tab::make('Giới thiệu DNV cuối trang')
                                                    ->icon('heroicon-m-identification')
                                                    ->schema([
                                                        Toggle::make('interface_settings.home.about_active')
                                                            ->label('Hiển thị khối giới thiệu DNV cuối trang chủ')
                                                            ->default(true)
                                                            ->columnSpanFull(),
                                                        TextInput::make('interface_settings.home.about_badge')
                                                            ->label('Huy hiệu trên tiêu đề (VD: VỀ DỰ ÁN ASXH)'),
                                                        TextInput::make('interface_settings.home.about_title')
                                                            ->label('Tiêu đề khối giới thiệu'),
                                                        Textarea::make('interface_settings.home.about_description')
                                                            ->label('Đoạn văn giới thiệu thương hiệu')
                                                            ->rows(3)
                                                            ->columnSpanFull(),

                                                        Repeater::make('interface_settings.home.about_features')
                                                            ->label('4 Điểm nổi bật (Features Grid)')
                                                            ->schema([
                                                                TextInput::make('icon')
                                                                    ->label('Icon FontAwesome')
                                                                    ->placeholder('fa-solid fa-shield-halved')
                                                                    ->required(),
                                                                TextInput::make('title')
                                                                    ->label('Tiêu đề đặc điểm')
                                                                    ->required(),
                                                                Textarea::make('description')
                                                                    ->label('Mô tả đặc điểm')
                                                                    ->rows(2)
                                                                    ->columnSpanFull()
                                                                    ->required(),
                                                            ])
                                                            ->columns(2)
                                                            ->columnSpanFull(),

                                                        Section::make('Thanh thông tin liên hệ dưới cùng')
                                                            ->schema([
                                                                TextInput::make('interface_settings.home.about_bottom_address')
                                                                    ->label('Địa chỉ trụ sở')
                                                                    ->columnSpanFull(),
                                                                TextInput::make('interface_settings.home.about_bottom_hotline')
                                                                    ->label('Hotline hỗ trợ'),
                                                                TextInput::make('interface_settings.home.about_bottom_hours')
                                                                    ->label('Giờ mở cửa hàng ngày'),
                                                            ])->columns(2),
                                                    ]),

                                                Tab::make('Cấu hình SEO')
                                                    ->icon('heroicon-m-magnifying-glass')
                                                    ->schema([
                                                        TextInput::make('interface_settings.home.meta_title')->label('SEO Title')->columnSpanFull(),
                                                        Textarea::make('interface_settings.home.meta_description')->label('SEO Description')->rows(3)->columnSpanFull(),
                                                        TextInput::make('interface_settings.home.meta_keywords')->label('SEO Keywords (Cách nhau dấu phẩy)')->columnSpanFull(),
                                                        CuratorPicker::make('interface_settings.home.meta_image')
                                                            ->label('SEO Image')
                                                            ->directory('seo')
                                                            ->helperText('Hình nền đại diện khi share link qua Zalo, Facebook'),
                                                    ]),
                                            ]),
                                    ]),

                                Tab::make('Cửa hàng & Liên hệ')
                                    ->icon('heroicon-m-building-storefront')
                                    ->schema([
                                        Tabs::make('store_subtabs')
                                            ->tabs([
                                                Tab::make('Banner & Chi nhánh')
                                                    ->icon('heroicon-m-building-office-2')
                                                    ->schema([
                                                        CuratorPicker::make('interface_settings.store.banner_image')
                                                            ->label('Ảnh Hero Banner Cửa Hàng')
                                                            ->directory('banners')
                                                            ->columnSpanFull(),
                                                        TextInput::make('interface_settings.store.title')
                                                            ->label('Tiêu đề trang cửa hàng (VD: THV Organic Store)'),
                                                        Textarea::make('interface_settings.store.subtitle')
                                                            ->label('Đoạn mô tả ngắn dưới tiêu đề')
                                                            ->rows(2)
                                                            ->columnSpanFull(),

                                                        Section::make('Thông tin Thẻ Chi Nhánh')
                                                            ->schema([
                                                                TextInput::make('interface_settings.store.branch_name')
                                                                    ->label('Tên chi nhánh (VD: Chi Nhánh Quận 1 (Flagship))')
                                                                    ->columnSpanFull(),
                                                                TextInput::make('interface_settings.store.address')
                                                                    ->label('Địa chỉ chi nhánh')
                                                                    ->columnSpanFull(),
                                                                TextInput::make('interface_settings.store.hours')
                                                                    ->label('Giờ mở cửa hoạt động'),
                                                                TextInput::make('interface_settings.store.phone')
                                                                    ->label('Số điện thoại liên hệ'),
                                                                TextInput::make('interface_settings.store.email')
                                                                    ->label('Email chi nhánh')
                                                                    ->email(),
                                                                Textarea::make('interface_settings.store.delivery_text')
                                                                    ->label('Thông tin dịch vụ giao hàng')
                                                                    ->rows(2)
                                                                    ->columnSpanFull(),
                                                                TextInput::make('interface_settings.store.hotline_call_url')
                                                                    ->label('Link nút Gọi điện tư vấn (VD: tel:0823336868)'),
                                                                TextInput::make('interface_settings.store.google_map_url')
                                                                    ->label('Link nút Chỉ đường Maps (Google Maps URL)'),
                                                            ])->columns(2),
                                                    ]),

                                                Tab::make('Bản đồ Google Map')
                                                    ->icon('heroicon-m-map')
                                                    ->schema([
                                                        Textarea::make('interface_settings.store.map_iframe_url')
                                                            ->label('Đường dẫn Google Maps hoặc Mã nhúng Iframe')
                                                            ->helperText('Có thể dán trực tiếp link chia sẻ Google Maps (maps.app.goo.gl/...), link địa điểm, hoặc mã nhúng <iframe> — hệ thống sẽ tự động chuyển sang chuẩn nhúng Embed.')
                                                            ->rows(4)
                                                            ->dehydrateStateUsing(function ($state) {
                                                                if (empty($state)) {
                                                                    return $state;
                                                                }
                                                                $val = trim($state);
                                                                if (preg_match('/src=["\']([^"\']+)["\']/i', $val, $m)) {
                                                                    $val = html_entity_decode($m[1], ENT_QUOTES);
                                                                }
                                                                if (str_contains($val, '/maps/embed') || str_contains($val, 'output=embed')) {
                                                                    return $val;
                                                                }
                                                                // Resolve short links like maps.app.goo.gl or goo.gl/maps
                                                                if (preg_match('~^https?://(maps\.app\.goo\.gl|goo\.gl/maps)/~i', $val)) {
                                                                    try {
                                                                        $ch = curl_init($val);
                                                                        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                                                                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                                                        curl_setopt($ch, CURLOPT_NOBODY, true);
                                                                        curl_setopt($ch, CURLOPT_TIMEOUT, 6);
                                                                        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
                                                                        curl_exec($ch);
                                                                        $effective = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
                                                                        if (!empty($effective)) {
                                                                            $val = $effective;
                                                                        }
                                                                    } catch (\Throwable $e) {
                                                                        // Ignore curl errors
                                                                    }
                                                                }
                                                                // Convert /maps/place/... or /maps/search/... URL to embed URL
                                                                if (preg_match('~/maps/place/([^/@?]+)~i', $val, $pm)) {
                                                                    $place = urldecode(str_replace('+', ' ', $pm[1]));
                                                                    return 'https://maps.google.com/maps?q=' . urlencode($place) . '&hl=vi&z=17&output=embed';
                                                                }
                                                                if (preg_match('/@(-?\d+\.\d+),(-?\d+\.\d+)/', $val, $cm)) {
                                                                    return 'https://maps.google.com/maps?q=' . $cm[1] . ',' . $cm[2] . '&hl=vi&z=17&output=embed';
                                                                }
                                                                if (!str_starts_with($val, 'http')) {
                                                                    return 'https://maps.google.com/maps?q=' . urlencode($val) . '&hl=vi&z=17&output=embed';
                                                                }
                                                                return $val;
                                                            })
                                                            ->columnSpanFull(),
                                                    ]),

                                                Tab::make('Trải nghiệm tại cửa hàng')
                                                    ->icon('heroicon-m-sparkles')
                                                    ->schema([
                                                        TextInput::make('interface_settings.store.features_title')
                                                            ->label('Tiêu đề khối trải nghiệm (VD: Trải nghiệm tại cửa hàng THV)')
                                                            ->columnSpanFull(),
                                                        Repeater::make('interface_settings.store.features')
                                                            ->label('Danh sách các điểm trải nghiệm')
                                                            ->schema([
                                                                TextInput::make('icon')
                                                                    ->label('Icon FontAwesome')
                                                                    ->placeholder('fa-solid fa-apple-whole')
                                                                    ->required(),
                                                                TextInput::make('title')
                                                                    ->label('Tiêu đề')
                                                                    ->required(),
                                                                Textarea::make('description')
                                                                    ->label('Mô tả chi tiết')
                                                                    ->rows(2)
                                                                    ->columnSpanFull()
                                                                    ->required(),
                                                            ])
                                                            ->columns(2)
                                                            ->columnSpanFull(),
                                                    ]),

                                                Tab::make('Form Liên Hệ Trực Tuyến')
                                                    ->icon('heroicon-m-envelope')
                                                    ->schema([
                                                        Section::make('Cấu hình Form Gửi Yêu Cầu Liên Hệ & Hợp Tác')
                                                            ->description('Hiển thị form cho đối tác, doanh nghiệp hoặc người dùng gửi thông tin liên hệ trực tiếp trên trang /lien-he và /cua-hang.')
                                                            ->collapsible()
                                                            ->schema([
                                                                Toggle::make('interface_settings.store.contact_form_active')
                                                                    ->label('Kích hoạt Form liên hệ trực tuyến')
                                                                    ->default(true),
                                                                TextInput::make('interface_settings.store.contact_form_title')
                                                                    ->label('Tiêu đề Form liên hệ')
                                                                    ->default('GỬI YÊU CẦU LIÊN HỆ & HỢP TÁC')
                                                                    ->columnSpanFull(),
                                                                Textarea::make('interface_settings.store.contact_form_subtitle')
                                                                    ->label('Mô tả phụ hướng dẫn người gửi')
                                                                    ->default('Quý khách hàng, đối tác hoặc Ban Chấp hành CĐCS vui lòng để lại thông tin, chúng tôi sẽ phản hồi trong vòng 24h.')
                                                                    ->rows(2)
                                                                    ->columnSpanFull(),
                                                                TextInput::make('interface_settings.store.contact_form_button_text')
                                                                    ->label('Chữ trên nút gửi')
                                                                    ->default('Gửi Thông Tin Liên Hệ'),
                                                                TextInput::make('interface_settings.store.contact_form_success_message')
                                                                    ->label('Nội dung thông báo khi gửi thành công')
                                                                    ->default('Cảm ơn bạn đã gửi liên hệ! Ban Dự Án DNV sẽ liên hệ lại với bạn trong thời gian sớm nhất.')
                                                                    ->columnSpanFull(),
                                                            ])->columns(2),
                                                    ]),

                                                Tab::make('Cấu hình SEO')
                                                    ->icon('heroicon-m-magnifying-glass')
                                                    ->schema([
                                                        TextInput::make('interface_settings.store.meta_title')->label('SEO Title')->columnSpanFull(),
                                                        Textarea::make('interface_settings.store.meta_description')->label('SEO Description')->rows(3)->columnSpanFull(),
                                                        TextInput::make('interface_settings.store.meta_keywords')->label('SEO Keywords (Cách nhau dấu phẩy)')->columnSpanFull(),
                                                        CuratorPicker::make('interface_settings.store.meta_image')
                                                            ->label('SEO Image')
                                                            ->directory('seo')
                                                            ->helperText('Hình nền đại diện khi share link qua Zalo, Facebook'),
                                                    ]),
                                            ]),
                                    ]),

                                Tab::make('Về chúng tôi')
                                    ->icon('heroicon-m-user-group')
                                    ->schema([
                                        Tabs::make('about_subtabs')
                                            ->tabs([
                                                Tab::make('Sơ đồ Quy trình FDA')
                                                    ->icon('heroicon-m-photo')
                                                    ->schema([
                                                        CuratorPicker::make('interface_settings.about.quy_trinh_image')
                                                            ->label('Ảnh Sơ đồ Quy trình FDA khép kín (Curator)')
                                                            ->directory('about')
                                                            ->helperText('Ảnh sơ đồ quy trình hiển thị trong khối Sơ đồ quy trình FDA')
                                                            ->columnSpanFull(),
                                                        TextInput::make('interface_settings.about.quy_trinh_badge')
                                                            ->label('Huy hiệu sơ đồ')
                                                            ->default('QUY TRÌNH TIÊU CHUẨN'),
                                                        TextInput::make('interface_settings.about.quy_trinh_title')
                                                            ->label('Tiêu đề sơ đồ quy trình')
                                                            ->default('SƠ ĐỒ QUY TRÌNH SẢN XUẤT KHÉP KÍN ĐẠT CHUẨN FDA'),
                                                        Textarea::make('interface_settings.about.quy_trinh_subtitle')
                                                            ->label('Mô tả phụ cho sơ đồ quy trình')
                                                            ->default('Từ nông trại nguyên liệu sạch đến quy trình chế biến hiện đại và kiểm định chất lượng xuất khẩu')
                                                            ->rows(2)
                                                            ->columnSpanFull(),
                                                    ]),

                                                Tab::make('Sứ mệnh & Tiêu chí vàng')
                                                    ->icon('heroicon-m-flag')
                                                    ->schema([
                                                        Section::make('Khối Tiêu Chí Vàng (Hộp nổi bật cạnh sơ đồ)')
                                                            ->schema([
                                                                TextInput::make('interface_settings.about.tieu_chi_title')
                                                                    ->label('Tiêu đề chính')
                                                                    ->placeholder('Tiêu Chí Vàng'),
                                                                TextInput::make('interface_settings.about.tieu_chi_subtitle')
                                                                    ->label('Phụ đề mô tả')
                                                                    ->placeholder('Thực phẩm an toàn – Chất lượng – Giá ưu đãi'),
                                                            ])->columns(2),

                                                        Section::make('Khối Sứ Mệnh An Sinh (Cột bên phải)')
                                                            ->schema([
                                                                TextInput::make('interface_settings.about.mission_badge')
                                                                    ->label('Huy hiệu nhỏ trên cùng')
                                                                    ->placeholder('SỨ MỆNH AN SINH'),
                                                                TextInput::make('interface_settings.about.mission_title')
                                                                    ->label('Tiêu đề Sứ mệnh lớn')
                                                                    ->placeholder('Đồng Hành Cùng Người Lao Động & Tổ Chức Công Đoàn')
                                                                    ->columnSpanFull(),
                                                                TextInput::make('interface_settings.about.mission_quote')
                                                                    ->label('Trích dẫn slogan đóng khung')
                                                                    ->placeholder('“THỰC PHẨM AN TOÀN, CHẤT LƯỢNG, GIÁ ƯU ĐÃI”')
                                                                    ->columnSpanFull(),
                                                                Textarea::make('interface_settings.about.mission_desc_1')
                                                                    ->label('Đoạn văn mô tả 1 (Trước câu trích dẫn)')
                                                                    ->rows(3)
                                                                    ->columnSpanFull(),
                                                                Textarea::make('interface_settings.about.mission_desc_2')
                                                                    ->label('Đoạn văn mô tả 2 (Sau câu trích dẫn)')
                                                                    ->rows(3)
                                                                    ->columnSpanFull(),
                                                            ])->columns(1),
                                                    ]),

                                                Tab::make('4 Giá trị cốt lõi')
                                                    ->icon('heroicon-m-sparkles')
                                                    ->schema([
                                                        TextInput::make('interface_settings.about.core_values_badge')
                                                            ->label('Huy hiệu khối')
                                                            ->placeholder('MỤC TIÊU PHÁT TRIỂN'),
                                                        TextInput::make('interface_settings.about.core_values_title')
                                                            ->label('Tiêu đề khối')
                                                            ->placeholder('4 Giá Trị Cốt Lõi Và Bền Vững'),
                                                        Textarea::make('interface_settings.about.core_values_subtitle')
                                                            ->label('Phụ đề mô tả')
                                                            ->placeholder('Những định hướng hành động xuyên suốt trong mọi hoạt động của Dự án')
                                                            ->rows(2)
                                                            ->columnSpanFull(),
                                                        Repeater::make('interface_settings.about.core_values')
                                                            ->label('Danh sách các thẻ Giá trị cốt lõi')
                                                            ->schema([
                                                                TextInput::make('number')
                                                                    ->label('Số thứ tự')
                                                                    ->placeholder('1')
                                                                    ->required(),
                                                                TextInput::make('title')
                                                                    ->label('Tiêu đề')
                                                                    ->required(),
                                                                TextInput::make('highlight')
                                                                    ->label('Tag cam kết dưới cùng (VD: Trợ giá trực tiếp)'),
                                                                Textarea::make('description')
                                                                    ->label('Mô tả nội dung')
                                                                    ->rows(2)
                                                                    ->columnSpanFull()
                                                                    ->required(),
                                                            ])
                                                            ->columns(3)
                                                            ->columnSpanFull(),
                                                    ]),

                                                Tab::make('Hồ sơ Năng lực DNV')
                                                    ->icon('heroicon-m-document-text')
                                                    ->schema([
                                                        TextInput::make('interface_settings.about.story_title')
                                                            ->label('Tiêu đề khối giới thiệu DNV')
                                                            ->placeholder('GIỚI THIỆU DOANH NGHIỆP VIỆT (DNV)')
                                                            ->columnSpanFull(),
                                                        Textarea::make('interface_settings.about.story_lead')
                                                            ->label('Đoạn mở đầu giới thiệu thành lập')
                                                            ->rows(3)
                                                            ->columnSpanFull(),
                                                        RichEditor::make('interface_settings.about.story_content')
                                                            ->label('Nội dung chi tiết bài viết Hồ sơ năng lực (Tùy chỉnh)')
                                                            ->helperText('Chỉnh sửa trực tiếp nội dung văn bản và hình ảnh hồ sơ năng lực của DNV. Có thể chèn ảnh từ Thư viện Media hoặc tải ảnh mới.')
                                                            ->fileAttachmentsDisk('public')
                                                            ->fileAttachmentsDirectory('about')
                                                            ->fileAttachmentsMaxSize(51200)
                                                            ->plugins([
                                                                AttachCuratorMediaPlugin::make(),
                                                            ])
                                                            ->columnSpanFull(),
                                                    ]),

                                                Tab::make('Sản phẩm xuất khẩu chủ lực')
                                                    ->icon('heroicon-m-globe-americas')
                                                    ->schema([
                                                        Section::make('Khối Các Dòng Sản Phẩm Xuất Khẩu Chủ Lực DNV')
                                                            ->description('Quản lý danh mục các dòng sản phẩm nông sản chế biến chủ lực xuất khẩu sang Hoa Kỳ và quốc tế.')
                                                            ->collapsible()
                                                            ->schema([
                                                                Toggle::make('interface_settings.about.export_products_active')
                                                                    ->label('Hiển thị khối sản phẩm xuất khẩu')
                                                                    ->default(true),
                                                                TextInput::make('interface_settings.about.export_products_badge')
                                                                    ->label('Huy hiệu nhỏ trên cùng')
                                                                    ->default('DANH MỤC XUẤT KHẨU HOA KỲ & QUỐC TẾ'),
                                                                TextInput::make('interface_settings.about.export_products_title')
                                                                    ->label('Tiêu đề chính')
                                                                    ->default('CÁC DÒNG SẢN PHẨM XUẤT KHẨU CHỦ LỰC CỦA DNV')
                                                                    ->columnSpanFull(),
                                                                TextInput::make('interface_settings.about.export_products_subtitle')
                                                                    ->label('Phụ đề tiếng Anh')
                                                                    ->default('DNV SIGNATURE EXPORT PRODUCT LINES DISTRIBUTED IN THE U.S MARKET')
                                                                    ->columnSpanFull(),

                                                                Repeater::make('interface_settings.about.export_products_list')
                                                                    ->label('Danh sách các khối sản phẩm xuất khẩu')
                                                                    ->schema([
                                                                        TextInput::make('badge')
                                                                            ->label('Tên tiếng Anh (Badge)')
                                                                            ->placeholder('VD: CRISPY SNACK PRODUCTS')
                                                                            ->required(),
                                                                        TextInput::make('title')
                                                                            ->label('Tên dòng sản phẩm')
                                                                            ->placeholder('VD: CÁC LOẠI SẤY GIÒN')
                                                                            ->required(),
                                                                        CuratorPicker::make('image')
                                                                            ->label('Hình ảnh sản phẩm (chính)')
                                                                            ->directory('about'),
                                                                        CuratorPicker::make('image_2')
                                                                            ->label('Hình ảnh phụ thứ 2 (Tùy chọn cho layout 2 ảnh như Khô cá)')
                                                                            ->directory('about'),
                                                                        Textarea::make('caption')
                                                                            ->label('Mô tả chú thích chi tiết')
                                                                            ->placeholder('Mít sấy giòn, chuối sấy, khoai lang sấy chiên chân không...')
                                                                            ->rows(2)
                                                                            ->columnSpanFull(),
                                                                    ])
                                                                    ->columns(2)
                                                                    ->columnSpanFull(),
                                                            ]),
                                                    ]),

                                                Tab::make('Thông tin Cty (#contact)')
                                                    ->icon('heroicon-m-building-office-2')
                                                    ->schema([
                                                        TextInput::make('interface_settings.about.contact_company')
                                                            ->label('Tên Cty')
                                                            ->placeholder('CÔNG TY CP ĐẦU TƯ & XÚC TIẾN THƯƠNG MẠI DOANH NGHIỆP VIỆT (DNV)')
                                                            ->columnSpanFull(),
                                                        TextInput::make('interface_settings.about.contact_address')
                                                            ->label('Địa chỉ Trụ sở')
                                                            ->columnSpanFull(),
                                                        TextInput::make('interface_settings.about.contact_office')
                                                            ->label('Địa chỉ Văn phòng giao dịch (VPGD)')
                                                            ->columnSpanFull(),
                                                        TextInput::make('interface_settings.about.contact_phone')
                                                            ->label('Hotline Cty'),
                                                        TextInput::make('interface_settings.about.contact_email')
                                                            ->label('Email Cty')
                                                            ->email(),
                                                        TextInput::make('interface_settings.about.contact_website')
                                                            ->label('Website chính thức (VD: https://dnv1.biz)')
                                                            ->url()
                                                            ->columnSpanFull(),
                                                        TextInput::make('interface_settings.about.contact_cta_text')
                                                            ->label('Chữ trên nút đỏ hành động')
                                                            ->default('LIÊN HỆ ĐẶT HÀNG')
                                                            ->placeholder('LIÊN HỆ ĐẶT HÀNG'),
                                                        TextInput::make('interface_settings.about.contact_cta_url')
                                                            ->label('Đường dẫn nút đỏ (VD: /lien-he hoặc link Zalo)')
                                                            ->default('/lien-he')
                                                            ->placeholder('/lien-he'),
                                                    ])->columns(2),

                                                Tab::make('Cấu hình SEO')
                                                    ->icon('heroicon-m-magnifying-glass')
                                                    ->schema([
                                                        TextInput::make('interface_settings.about.meta_title')->label('SEO Title')->columnSpanFull(),
                                                        Textarea::make('interface_settings.about.meta_description')->label('SEO Description')->rows(3)->columnSpanFull(),
                                                        TextInput::make('interface_settings.about.meta_keywords')->label('SEO Keywords (Cách nhau dấu phẩy)')->columnSpanFull(),
                                                        CuratorPicker::make('interface_settings.about.meta_image')
                                                            ->label('SEO Image')
                                                            ->directory('seo')
                                                            ->helperText('Hình nền đại diện khi share link qua Zalo, Facebook'),
                                                    ]),
                                            ]),
                                    ]),

                                Tab::make('Sản phẩm')
                                    ->icon('heroicon-m-shopping-bag')
                                    ->schema([
                                        Tabs::make('products_subtabs')
                                            ->tabs([
                                                Tab::make('Hiển thị & Bộ lọc')
                                                    ->icon('heroicon-m-adjustments-horizontal')
                                                    ->schema([
                                                        Select::make('interface_settings.products.default_per_page')
                                                            ->label('Số sản phẩm mặc định mỗi trang')
                                                            ->options([
                                                                12 => '12 sản phẩm',
                                                                24 => '24 sản phẩm',
                                                                48 => '48 sản phẩm',
                                                            ])
                                                            ->default(24),
                                                        TextInput::make('interface_settings.products.toast_message_hint')
                                                            ->label('Ghi chú thông báo giỏ hàng (Toast)')
                                                            ->placeholder('Đã cập nhật giỏ hàng'),
                                                        Toggle::make('interface_settings.products.enable_brand_filter')
                                                            ->label('Bật bộ lọc Thương hiệu')
                                                            ->default(true),
                                                        Toggle::make('interface_settings.products.enable_price_sort')
                                                            ->label('Bật tùy chọn Sắp xếp theo giá')
                                                            ->default(true),
                                                    ])->columns(2),

                                                Tab::make('Trang Chi tiết Sản phẩm')
                                                    ->icon('heroicon-m-shield-check')
                                                    ->schema([
                                                        TextInput::make('interface_settings.products.commitment_note')
                                                            ->label('Dòng cam kết cuối mô tả sản phẩm')
                                                            ->columnSpanFull(),
                                                        TextInput::make('interface_settings.products.promo_badge')
                                                            ->label('Huy hiệu khối Giao hàng (Cột phải)'),
                                                        TextInput::make('interface_settings.products.promo_title')
                                                            ->label('Tiêu đề khối Giao hàng (Cột phải)'),
                                                        Textarea::make('interface_settings.products.promo_desc')
                                                            ->label('Mô tả khối Giao hàng (Cột phải)')
                                                            ->rows(2)
                                                            ->columnSpanFull(),
                                                        Repeater::make('interface_settings.products.guarantee_badges')
                                                            ->label('3 Thẻ Cam Kết dưới nút Mua Hàng')
                                                            ->schema([
                                                                TextInput::make('icon')->label('Icon FontAwesome')->required(),
                                                                TextInput::make('title')->label('Tiêu đề')->required(),
                                                                TextInput::make('subtitle')->label('Mô tả ngắn')->required(),
                                                            ])
                                                            ->columns(3)
                                                            ->columnSpanFull(),
                                                    ])->columns(2),

                                                Tab::make('Cấu hình SEO')
                                                    ->icon('heroicon-m-magnifying-glass')
                                                    ->schema([
                                                        TextInput::make('interface_settings.products.meta_title')->label('SEO Title')->columnSpanFull(),
                                                        Textarea::make('interface_settings.products.meta_description')->label('SEO Description')->rows(3)->columnSpanFull(),
                                                        TextInput::make('interface_settings.products.meta_keywords')->label('SEO Keywords (Cách nhau dấu phẩy)')->columnSpanFull(),
                                                        CuratorPicker::make('interface_settings.products.meta_image')
                                                            ->label('SEO Image')
                                                            ->directory('seo')
                                                            ->helperText('Hình nền đại diện khi share link qua Zalo, Facebook'),
                                                    ]),
                                            ]),
                                    ]),

                                Tab::make('Blog')
                                    ->icon('heroicon-m-newspaper')
                                    ->schema([
                                        Tabs::make('blog_subtabs')
                                            ->tabs([
                                                Tab::make('Cấu hình hiển thị')
                                                    ->icon('heroicon-m-adjustments-horizontal')
                                                    ->schema([
                                                        TextInput::make('interface_settings.blog.popular_title')
                                                            ->label('Tiêu đề khối bài viết nổi bật')
                                                            ->placeholder('Bài Viết Nổi Bật'),
                                                        Select::make('interface_settings.blog.per_page')
                                                            ->label('Số bài viết hiển thị mỗi trang')
                                                            ->options([
                                                                4 => '4 bài viết',
                                                                6 => '6 bài viết',
                                                                8 => '8 bài viết',
                                                                12 => '12 bài viết',
                                                            ])
                                                            ->default(6),
                                                        TextInput::make('interface_settings.blog.author_name')
                                                            ->label('Tên Ban biên tập / Tác giả hiển thị'),
                                                        TextInput::make('interface_settings.blog.author_role')
                                                            ->label('Chức danh / Vai trò'),
                                                        Textarea::make('interface_settings.blog.author_bio')
                                                            ->label('Mô tả ngắn giới thiệu Ban biên tập (Sidebar bài viết)')
                                                            ->rows(2)
                                                            ->columnSpanFull(),
                                                    ])->columns(2),

                                                Tab::make('Cấu hình SEO')
                                                    ->icon('heroicon-m-magnifying-glass')
                                                    ->schema([
                                                        TextInput::make('interface_settings.blog.meta_title')->label('SEO Title')->columnSpanFull(),
                                                        Textarea::make('interface_settings.blog.meta_description')->label('SEO Description')->rows(3)->columnSpanFull(),
                                                        TextInput::make('interface_settings.blog.meta_keywords')->label('SEO Keywords (Cách nhau dấu phẩy)')->columnSpanFull(),
                                                        CuratorPicker::make('interface_settings.blog.meta_image')
                                                            ->label('SEO Image')
                                                            ->directory('seo')
                                                            ->helperText('Hình nền đại diện khi share link qua Zalo, Facebook'),
                                                    ]),
                                            ]),
                                    ]),

                                Tab::make('Giỏ hàng')
                                    ->icon('heroicon-m-shopping-cart')
                                    ->schema([
                                        Tabs::make('cart_subtabs')
                                            ->tabs([
                                                Tab::make('Chính sách & Mốc Freeship')
                                                    ->icon('heroicon-m-truck')
                                                    ->schema([
                                                        TextInput::make('interface_settings.cart.title')
                                                            ->label('Tiêu đề trang giỏ hàng')
                                                            ->placeholder('Giỏ hàng của bạn'),
                                                        TextInput::make('interface_settings.cart.freeship_threshold')
                                                            ->label('Định mức đạt Freeship (VNĐ)')
                                                            ->numeric()
                                                            ->prefix('₫')
                                                            ->placeholder('500000'),
                                                        TextInput::make('interface_settings.cart.freeship_text_progress')
                                                            ->label('Thông báo khi chưa đủ mốc Freeship')
                                                            ->helperText('Dùng {remain} để tự động điền số tiền còn thiếu')
                                                            ->columnSpanFull(),
                                                        TextInput::make('interface_settings.cart.freeship_text_reached')
                                                            ->label('Thông báo khi đã đủ điều kiện Freeship')
                                                            ->columnSpanFull(),
                                                    ])->columns(2),

                                                Tab::make('Cam kết dịch vụ')
                                                    ->icon('heroicon-m-shield-check')
                                                    ->schema([
                                                        Repeater::make('interface_settings.cart.trust_badges')
                                                            ->label('Hộp cam kết chất lượng & dịch vụ (Cột phải)')
                                                            ->schema([
                                                                TextInput::make('icon')->label('Icon FontAwesome')->required(),
                                                                TextInput::make('title')->label('Tiêu đề cam kết')->required(),
                                                                Textarea::make('description')->label('Mô tả chi tiết')->required()->columnSpanFull(),
                                                            ])
                                                            ->columns(2)
                                                            ->columnSpanFull(),
                                                    ]),

                                                Tab::make('Giỏ hàng trống & Hỗ trợ')
                                                    ->icon('heroicon-m-archive-box-x-mark')
                                                    ->schema([
                                                        TextInput::make('interface_settings.cart.empty_title')
                                                            ->label('Tiêu đề khi giỏ trống'),
                                                        TextInput::make('interface_settings.cart.empty_btn_text')
                                                            ->label('Chữ trên nút quay lại mua hàng')
                                                            ->placeholder('TIẾP TỤC MUA SẮM'),
                                                        Textarea::make('interface_settings.cart.empty_description')
                                                            ->label('Mô tả khi giỏ trống')
                                                            ->rows(2)
                                                            ->columnSpanFull(),
                                                        TextInput::make('interface_settings.cart.empty_btn_link')
                                                            ->label('Đường dẫn link nút')
                                                            ->placeholder('/danh-muc')
                                                            ->columnSpanFull(),
                                                    ])->columns(2),

                                                Tab::make('Cấu hình SEO')
                                                    ->icon('heroicon-m-magnifying-glass')
                                                    ->schema([
                                                        TextInput::make('interface_settings.cart.meta_title')->label('SEO Title')->columnSpanFull(),
                                                        Textarea::make('interface_settings.cart.meta_description')->label('SEO Description')->rows(3)->columnSpanFull(),
                                                        TextInput::make('interface_settings.cart.meta_keywords')->label('SEO Keywords (Cách nhau dấu phẩy)')->columnSpanFull(),
                                                        CuratorPicker::make('interface_settings.cart.meta_image')
                                                            ->label('SEO Image')
                                                            ->directory('seo')
                                                            ->helperText('Hình nền đại diện khi share link qua Zalo, Facebook'),
                                                    ]),
                                            ]),
                                    ]),

                                Tab::make('Thanh toán')
                                    ->icon('heroicon-m-credit-card')
                                    ->schema([
                                        Tabs::make('checkout_subtabs')
                                            ->tabs([
                                                Tab::make('Tiêu đề & Hướng dẫn')
                                                    ->icon('heroicon-m-document-text')
                                                    ->schema([
                                                        TextInput::make('interface_settings.checkout.title')
                                                            ->label('Tiêu đề trang')
                                                            ->placeholder('Đặt hàng & Thanh toán'),
                                                        TextInput::make('interface_settings.checkout.subtitle')
                                                            ->label('Mô tả phụ')
                                                            ->placeholder('Hoàn tất đơn hàng hỏa tốc trong 1 giờ nội thành TP.HCM'),
                                                        TextInput::make('interface_settings.checkout.shipping_note')
                                                            ->label('Ghi chú vận chuyển hỏa tốc')
                                                            ->columnSpanFull(),
                                                        TextInput::make('interface_settings.checkout.note_placeholder')
                                                            ->label('Gợi ý ô ghi chú đơn hàng')
                                                            ->columnSpanFull(),
                                                    ])->columns(2),

                                                Tab::make('Phương thức thanh toán')
                                                    ->icon('heroicon-m-banknotes')
                                                    ->schema([
                                                        TextInput::make('interface_settings.checkout.cod_title')
                                                            ->label('Tiêu đề phương thức COD'),
                                                        TextInput::make('interface_settings.checkout.cod_desc')
                                                            ->label('Mô tả ngắn COD'),
                                                        TextInput::make('interface_settings.checkout.bank_title')
                                                            ->label('Tiêu đề phương thức VietQR'),
                                                        TextInput::make('interface_settings.checkout.bank_desc')
                                                            ->label('Mô tả ngắn VietQR'),
                                                        TextInput::make('interface_settings.checkout.submit_btn_text')
                                                            ->label('Chữ trên nút Đặt hàng')
                                                            ->placeholder('HOÀN TẤT ĐẶT HÀNG')
                                                            ->columnSpanFull(),
                                                    ])->columns(2),

                                                Tab::make('Cấu hình SEO')
                                                    ->icon('heroicon-m-magnifying-glass')
                                                    ->schema([
                                                        TextInput::make('interface_settings.checkout.meta_title')->label('SEO Title')->columnSpanFull(),
                                                        Textarea::make('interface_settings.checkout.meta_description')->label('SEO Description')->rows(3)->columnSpanFull(),
                                                        TextInput::make('interface_settings.checkout.meta_keywords')->label('SEO Keywords (Cách nhau dấu phẩy)')->columnSpanFull(),
                                                        CuratorPicker::make('interface_settings.checkout.meta_image')
                                                            ->label('SEO Image')
                                                            ->directory('seo')
                                                            ->helperText('Hình nền đại diện khi share link qua Zalo, Facebook'),
                                                    ]),
                                            ]),
                                    ]),

                                Tab::make('Đơn hàng thành công')
                                    ->icon('heroicon-m-check-badge')
                                    ->schema([
                                        Tabs::make('order_success_subtabs')
                                            ->tabs([
                                                Tab::make('Thông báo & Lời cảm ơn')
                                                    ->icon('heroicon-m-sparkles')
                                                    ->schema([
                                                        TextInput::make('interface_settings.order_success.title')
                                                            ->label('Tiêu đề chúc mừng')
                                                            ->placeholder('Đặt Hàng Thành Công!'),
                                                        Textarea::make('interface_settings.order_success.thank_you_message')
                                                            ->label('Lời cảm ơn & thông điệp')
                                                            ->rows(3)
                                                            ->columnSpanFull(),
                                                        TextInput::make('interface_settings.order_success.support_hotline')
                                                            ->label('Hotline hỗ trợ đơn hàng'),
                                                        TextInput::make('interface_settings.order_success.support_hours')
                                                            ->label('Khung giờ hỗ trợ'),
                                                        TextInput::make('interface_settings.order_success.support_zalo_link')
                                                            ->label('Link Chat Zalo hỗ trợ đơn'),
                                                        TextInput::make('interface_settings.order_success.continue_btn_text')
                                                            ->label('Chữ trên nút tiếp tục mua sắm'),
                                                        TextInput::make('interface_settings.order_success.continue_btn_link')
                                                            ->label('Đường dẫn nút tiếp tục mua sắm')
                                                            ->columnSpanFull(),
                                                    ])->columns(2),

                                                Tab::make('Quy trình xử lý đơn')
                                                    ->icon('heroicon-m-numbered-list')
                                                    ->schema([
                                                        Repeater::make('interface_settings.order_success.timeline_steps')
                                                            ->label('4 Trạng thái tiến trình giao hàng')
                                                            ->schema([
                                                                TextInput::make('step')->label('Bước số')->numeric()->required(),
                                                                TextInput::make('title')->label('Tên trạng thái')->required(),
                                                                TextInput::make('sub')->label('Thời gian / Chú thích phụ')->required(),
                                                            ])
                                                            ->columns(3)
                                                            ->columnSpanFull(),
                                                    ]),

                                                Tab::make('Cấu hình SEO')
                                                    ->icon('heroicon-m-magnifying-glass')
                                                    ->schema([
                                                        TextInput::make('interface_settings.order_success.meta_title')->label('SEO Title')->columnSpanFull(),
                                                        Textarea::make('interface_settings.order_success.meta_description')->label('SEO Description')->rows(3)->columnSpanFull(),
                                                        TextInput::make('interface_settings.order_success.meta_keywords')->label('SEO Keywords (Cách nhau dấu phẩy)')->columnSpanFull(),
                                                        CuratorPicker::make('interface_settings.order_success.meta_image')
                                                            ->label('SEO Image')
                                                            ->directory('seo')
                                                            ->helperText('Hình nền đại diện khi share link qua Zalo, Facebook'),
                                                    ]),
                                            ]),
                                    ]),

                                Tab::make('Tài khoản')
                                    ->icon('heroicon-m-user')
                                    ->schema([
                                        Tabs::make('login_subtabs')
                                            ->tabs([
                                                Tab::make('Màn hình Đăng nhập')
                                                    ->icon('heroicon-m-arrow-right-on-rectangle')
                                                    ->schema([
                                                        TextInput::make('interface_settings.login.login_title')
                                                            ->label('Tiêu đề form đăng nhập')
                                                            ->placeholder('Đăng nhập hoặc Tạo tài khoản'),
                                                        TextInput::make('interface_settings.login.login_btn_text')
                                                            ->label('Chữ trên nút Tiếp tục')
                                                            ->placeholder('Tiếp tục'),
                                                        TextInput::make('interface_settings.login.login_hint')
                                                            ->label('Gợi ý nhập số điện thoại hoặc email')
                                                            ->columnSpanFull(),
                                                    ])->columns(2),

                                                Tab::make('Trang Quản lý & Hỗ trợ')
                                                    ->icon('heroicon-m-user-circle')
                                                    ->schema([
                                                        TextInput::make('interface_settings.login.customer_badge')
                                                            ->label('Huy hiệu phân hạng khách hàng')
                                                            ->placeholder('Khách hàng DNV'),
                                                        TextInput::make('interface_settings.login.support_title')
                                                            ->label('Tiêu đề khung hỗ trợ')
                                                            ->placeholder('Cần hỗ trợ đơn hàng?'),
                                                        Textarea::make('interface_settings.login.support_desc')
                                                            ->label('Nội dung hỗ trợ')
                                                            ->rows(2)
                                                            ->columnSpanFull(),
                                                        TextInput::make('interface_settings.login.support_phone')
                                                            ->label('Hotline hỗ trợ'),
                                                        TextInput::make('interface_settings.login.support_zalo')
                                                            ->label('Link Chat Zalo hỗ trợ'),
                                                    ])->columns(2),

                                                Tab::make('Cấu hình SEO')
                                                    ->icon('heroicon-m-magnifying-glass')
                                                    ->schema([
                                                        TextInput::make('interface_settings.login.meta_title')->label('SEO Title')->columnSpanFull(),
                                                        Textarea::make('interface_settings.login.meta_description')->label('SEO Description')->rows(3)->columnSpanFull(),
                                                        TextInput::make('interface_settings.login.meta_keywords')->label('SEO Keywords (Cách nhau dấu phẩy)')->columnSpanFull(),
                                                        CuratorPicker::make('interface_settings.login.meta_image')
                                                            ->label('SEO Image')
                                                            ->directory('seo')
                                                            ->helperText('Hình nền đại diện khi share link qua Zalo, Facebook'),
                                                    ]),
                                            ]),
                                    ]),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $setting = Setting::first() ?? new Setting;

        $setting->general_settings = $data['general_settings'];
        $setting->interface_settings = $data['interface_settings'];
        $setting->save();

        Notification::make()
            ->title('Đã lưu Cấu hình Thành công!')
            ->success()
            ->send();
    }

    public function sendTestEmail(string $recipientEmail): void
    {
        $formData = $this->form->getState();
        $emailSettings = data_get($formData, 'general_settings.email', []);
        $siteName = data_get($formData, 'general_settings.site_name', 'DOANH NGHIỆP VIỆT');

        $host = data_get($emailSettings, 'smtp_host');
        $port = data_get($emailSettings, 'smtp_port', 465);
        $user = data_get($emailSettings, 'smtp_user');
        $pass = data_get($emailSettings, 'smtp_password');
        $senderEmail = data_get($emailSettings, 'sender_email') ?: 'ecom@asxh.org.vn';

        if (! empty($host)) {
            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.host' => $host,
                'mail.mailers.smtp.port' => (int) $port,
                'mail.mailers.smtp.encryption' => ((int) $port === 465) ? 'ssl' : 'tls',
                'mail.mailers.smtp.username' => $user,
                'mail.mailers.smtp.password' => $pass,
                'mail.from.address' => $senderEmail,
                'mail.from.name' => $siteName,
            ]);
        }

        try {
            Mail::raw(
                "Đây là email thử nghiệm gửi từ hệ thống {$siteName} vào lúc ".now()->format('H:i:s d/m/Y').".\n\nNếu bạn nhận được email này, cấu hình SMTP của hệ thống đang hoạt động hoàn toàn bình thường!",
                function ($message) use ($recipientEmail, $senderEmail, $siteName) {
                    $message->to($recipientEmail)
                        ->from($senderEmail, $siteName)
                        ->subject('[TEST] Thư thử nghiệm cấu hình Email SMTP - '.$siteName);
                }
            );

            Notification::make()
                ->title('Gửi mail thử nghiệm thành công!')
                ->body("Đã gửi email test tới địa chỉ: {$recipientEmail}")
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Lỗi gửi mail!')
                ->body('Chi tiết lỗi: '.$e->getMessage())
                ->danger()
                ->send();
        }
    }
}
