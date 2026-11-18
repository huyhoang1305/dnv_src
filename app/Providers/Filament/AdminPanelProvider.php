<?php

namespace App\Providers\Filament;

use App\Filament\Pages\InterfaceSettings;
use App\Models\Setting;
use App\Filament\Resources\AttributeTypes\AttributeTypeResource;
use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\Contacts\ContactResource;
use App\Filament\Resources\ContentCategories\ContentCategoryResource;
use App\Filament\Resources\Contents\ContentResource;
use App\Filament\Resources\ContentTypes\ContentTypeResource;
use App\Filament\Resources\Coupons\CouponResource;
use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Products\ProductResource;
use App\Filament\Resources\Reviews\ReviewResource;
use App\Filament\Resources\Users\UserResource;
use App\Filament\Widgets\DnvInfoWidget;
use Awcodes\Curator\CuratorPlugin;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use BezhanSalleh\FilamentShield\Resources\Roles\RoleResource;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use App\Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('hg-admin')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->brandName(fn () => rescue(fn () => Setting::getSystemSetting()?->general_settings['site_name'], 'DOANH NGHIỆP VIỆT', false))
            ->favicon(fn () => rescue(fn () => curator_url(Setting::getSystemSetting()?->general_settings['site_icon']), asset('storage/logo/favicon-dnv.png'), false))
            ->spa()
            ->login(\App\Filament\Pages\Auth\Login::class)
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                DnvInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->navigation(function (NavigationBuilder $builder): NavigationBuilder {
                $user = auth()->user();

                return $builder->groups([
                    NavigationGroup::make('Bảng điều khiển')
                        ->items([
                            NavigationItem::make('Tổng quan')
                                ->icon('heroicon-s-home')
                                ->isActiveWhen(fn (): bool => request()->routeIs('filament.admin.pages.dashboard'))
                                ->url(fn (): string => Dashboard::getUrl()),
                        ]),
                    NavigationGroup::make('Bán hàng')
                        ->items([
                            ...($user?->can('ViewAny:Order') ? OrderResource::getNavigationItems() : []),
                            ...($user?->can('ViewAny:Coupon') ? CouponResource::getNavigationItems() : []),
                        ]),
                    NavigationGroup::make('Kho hàng & Sản phẩm')
                        ->items([
                            ...($user?->can('ViewAny:Category') ? CategoryResource::getNavigationItems() : []),
                            ...($user?->can('ViewAny:Product') ? ProductResource::getNavigationItems() : []),
                            ...(($user?->can('ViewAny:AttributeType') || $user?->hasRole('super_admin')) ? AttributeTypeResource::getNavigationItems() : []),
                        ]),
                    NavigationGroup::make('Bài viết & CMS')
                        ->items([
                            ...($user?->can('ViewAny:Content') ? ContentResource::getNavigationItems() : []),
                        ]),
                    NavigationGroup::make('Tương tác')
                        ->items([
                            ...($user?->can('ViewAny:Contact') ? ContactResource::getNavigationItems() : []),
                            ...(($user?->can('ViewAny:Review') || $user?->hasRole('super_admin')) ? ReviewResource::getNavigationItems() : []),
                        ]),
                    NavigationGroup::make('Hệ thống')
                        ->items([
                            ...($user?->can('ViewAny:User') ? UserResource::getNavigationItems() : []),
                            NavigationItem::make('Cài đặt Hệ thống')
                                ->icon('heroicon-s-cog-6-tooth')
                                ->visible(fn (): bool => $user?->can('View:InterfaceSettings') || $user?->hasRole('super_admin'))
                                ->isActiveWhen(fn (): bool => request()->routeIs('filament.admin.pages.interface-settings'))
                                ->url(fn (): string => InterfaceSettings::getUrl()),
                        ]),
                ]);
            })
            ->plugins([FilamentShieldPlugin::make(),
                CuratorPlugin::make()
                    ->label('Media')
                    ->pluralLabel('Media')
                    ->navigationIcon('heroicon-s-photo')
                    ->navigationGroup('Content')
                    ->navigationSort(3)
                    ->showBadge(true)
                    ->registerNavigation(true)
                    ->curations(true)
                    ->fileSwap(true),  ]);
    }
}
