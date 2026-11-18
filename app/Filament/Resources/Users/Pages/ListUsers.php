<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use BezhanSalleh\FilamentShield\Resources\Roles\RoleResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('roles')
                ->label('Vai trò & Phân quyền')
                ->icon('heroicon-o-shield-check')
                ->color('gray')
                ->badge(fn (): string => (string) \Spatie\Permission\Models\Role::count())
                ->visible(fn (): bool => auth()->user()?->can('ViewAny:Role') ?? true)
                ->url(RoleResource::getUrl('index')),

            CreateAction::make()
                ->label('Tạo người dùng'),
        ];
    }
}
