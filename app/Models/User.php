<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'phone', 'address', 'password', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable , HasRoles;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return (bool) $this->is_active && ($this->hasRole('super_admin') || $this->roles()->exists() || $this->permissions()->exists());
    }

    /**
     * Ngăn chặn việc xóa tài khoản đang có quyền truy cập
     */
    protected static function booted(): void
    {
        static::deleting(function (User $user) {
            if ($user->is_active) {
                throw new \Exception('Vui lòng Tắt [Cấp quyền Bảng điều khiển] của tài khoản này trước khi Xóa!');
            }
        });
    }
}
