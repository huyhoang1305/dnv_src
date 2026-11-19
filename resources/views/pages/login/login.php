<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

new #[Layout('layouts.app')] class extends Component
{
    public $setting;

    public $mode = 'login'; // 'login' or 'register'

    public $tab = 'orders'; // 'orders' or 'profile'

    #[Url]
    public $status = 'all'; // 'all', 'pending', 'processing', 'delivering', 'completed', 'cancelled'

    // Guest Auth Form State
    public $phone = '';
    public $name = '';
    public $registerEmail = '';
    public $registerAddress = '';
    public $password = '';
    public $password_confirmation = '';
    public $showPassword = false;
    public $showConfirmPassword = false;
    public $errorMessage = '';
    public $successMessage = '';

    // Logged in Account State
    public $accountName = '';
    public $accountPhone = '';
    public $accountEmail = '';
    public $accountAddress = '';
    public $currentPassword = '';
    public $newPassword = '';
    public $newPasswordConfirmation = '';
    public $profileMessage = '';
    public $profileError = '';

    public function mount()
    {
        $this->setting = Setting::getSystemSetting();
        if (request()->routeIs('register') || request()->routeIs('register.vi') || request()->query('mode') === 'register') {
            $this->mode = 'register';
        } else {
            $this->mode = 'login';
        }

        if (request()->routeIs('account') || request()->query('tab') === 'profile') {
            $this->tab = 'profile';
        } else {
            $this->tab = 'orders';
        }

        $this->loadAccountData();
    }

    public function switchMode($targetMode)
    {
        return redirect()->route($targetMode === 'register' ? 'register.vi' : 'login.vi');
    }

    protected function loadAccountData()
    {
        if (Auth::check()) {
            $user = Auth::user();
            $this->accountName = $user->name ?? '';
            $this->accountEmail = $user->email ?? '';
            $this->accountPhone = $user->phone ?? '';
            $this->accountAddress = $user->address ?? '';

            if (empty($this->accountPhone) && (str_ends_with($user->email ?? '', '@thv.vn') || str_ends_with($user->email ?? '', '@dnv1.biz'))) {
                $this->accountPhone = preg_replace('/@(thv\.vn|dnv1\.biz)$/', '', $user->email);
            }

            // Look up latest order for contact phone & shipping address if still empty
            if (empty($this->accountPhone) || empty($this->accountAddress)) {
                $latestOrder = Order::where('user_id', $user->id)
                    ->orWhere('customer_email', $user->email)
                    ->latest()
                    ->first();

                if ($latestOrder) {
                    if (empty($this->accountPhone)) {
                        $this->accountPhone = $latestOrder->customer_phone ?? '';
                    }
                    if (empty($this->accountAddress)) {
                        $this->accountAddress = $latestOrder->shipping_address ?? '';
                    }
                } elseif (empty($this->accountAddress)) {
                    $this->accountAddress = session('customer_address', '');
                }
            }
        }
    }

    public function setTab($t)
    {
        $this->tab = $t;
        $this->profileMessage = '';
        $this->profileError = '';
    }

    public function setStatus($s)
    {
        $this->status = $s;
    }

    public function updateProfile()
    {
        $this->profileMessage = '';
        $this->profileError = '';

        if (!Auth::check()) return;

        $this->accountName = trim($this->accountName);
        if (empty($this->accountName)) {
            $this->profileError = 'Vui lòng nhập họ và tên.';
            return;
        }

        $user = Auth::user();
        $user->name = $this->accountName;
        $user->address = trim($this->accountAddress) ?: null;

        $newEmail = strtolower(trim($this->accountEmail));
        if (!empty($newEmail)) {
            if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
                $this->profileError = 'Địa chỉ email không đúng định dạng.';
                return;
            }
            $emailExists = User::where('email', $newEmail)->where('id', '!=', $user->id)->exists();
            if ($emailExists) {
                $this->profileError = 'Địa chỉ email này đã được sử dụng bởi tài khoản khác.';
                return;
            }
            $user->email = $newEmail;
        }

        $newPhone = preg_replace('/[^0-9]/', '', trim($this->accountPhone));
        if (!empty($newPhone)) {
            if (strlen($newPhone) !== 10 || !str_starts_with($newPhone, '0')) {
                $this->profileError = 'Số điện thoại phải có đúng 10 chữ số và bắt đầu bằng số 0.';
                return;
            }
            $phoneExists = User::where('phone', $newPhone)->where('id', '!=', $user->id)->exists();
            if ($phoneExists) {
                $this->profileError = 'Số điện thoại này đã được sử dụng bởi tài khoản khác.';
                return;
            }
            $user->phone = $newPhone;
        }

        $user->save();

        session(['customer_address' => $this->accountAddress]);
        session(['customer_phone' => $this->accountPhone]);

        $this->profileMessage = 'Cập nhật thông tin tài khoản thành công!';
    }

    public function changePassword()
    {
        $this->profileMessage = '';
        $this->profileError = '';

        if (!Auth::check()) return;

        $throttleKey = 'change_pwd:' . Auth::id() . ':' . request()->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $minutes = ceil(RateLimiter::availableIn($throttleKey) / 60);
            $this->profileError = "Bạn đã nhập sai mật khẩu quá nhiều lần. Vui lòng thử lại sau {$minutes} phút.";
            return;
        }

        if (empty($this->currentPassword)) {
            $this->profileError = 'Vui lòng nhập mật khẩu hiện tại.';
            return;
        }

        if (!Hash::check($this->currentPassword, Auth::user()->password)) {
            RateLimiter::hit($throttleKey, 300);
            $this->profileError = 'Mật khẩu hiện tại không chính xác.';
            return;
        }

        if (strlen($this->newPassword) < 8) {
            $this->profileError = 'Mật khẩu mới phải có ít nhất 8 ký tự.';
            return;
        }

        if ($this->newPassword !== $this->newPasswordConfirmation) {
            $this->profileError = 'Mật khẩu xác nhận không khớp.';
            return;
        }

        RateLimiter::clear($throttleKey);
        $user = Auth::user();
        $user->password = Hash::make($this->newPassword);
        $user->save();

        $this->currentPassword = '';
        $this->newPassword = '';
        $this->newPasswordConfirmation = '';
        $this->profileMessage = 'Đổi mật khẩu bảo mật thành công!';
    }

    public function cancelOrder($orderId)
    {
        if (!Auth::check()) return;

        $user = Auth::user();
        $order = Order::where('id', $orderId)
            ->where('user_id', $user->id)
            ->first();

        if ($order && in_array($order->status, ['pending', 'Chờ xử lý'])) {
            $order->update(['status' => 'cancelled']);
            $this->profileMessage = 'Đã hủy đơn hàng #' . $order->order_number;
        }
    }

    public function logout()
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return $this->redirect('/', navigate: true);
    }

    protected function findUserByPhoneOrEmail($input)
    {
        $input = trim($input);
        if (empty($input)) {
            return null;
        }

        if (filter_var($input, FILTER_VALIDATE_EMAIL)) {
            return User::where('email', strtolower($input))->first();
        }

        $cleanPhone = preg_replace('/[^0-9]/', '', $input);
        if (empty($cleanPhone)) {
            return null;
        }

        return User::where('phone', $cleanPhone)
            ->orWhere('phone', $input)
            ->orWhere('email', $cleanPhone . '@dnv1.biz')
            ->orWhere('email', $cleanPhone . '@thv.vn')
            ->first();
    }

    public function submitLogin()
    {
        $this->errorMessage = '';
        $input = trim($this->phone);

        if (empty($input)) {
            $this->errorMessage = 'Vui lòng nhập số điện thoại hoặc email đăng nhập.';
            return;
        }

        if (empty($this->password)) {
            $this->errorMessage = 'Vui lòng nhập mật khẩu.';
            return;
        }

        $throttleKey = 'login_attempts:' . request()->ip() . ':' . Str::lower($input);
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $minutes = ceil($seconds / 60);
            $this->errorMessage = "Bạn đã đăng nhập sai quá nhiều lần. Vui lòng thử lại sau {$minutes} phút.";
            return;
        }

        $user = $this->findUserByPhoneOrEmail($input);

        if (!$user || !Hash::check($this->password, $user->password)) {
            RateLimiter::hit($throttleKey, 300);
            $this->errorMessage = 'Số điện thoại/Email hoặc mật khẩu không chính xác.';
            return;
        }

        if (!$user->is_active) {
            $this->errorMessage = 'Tài khoản này hiện đang tạm khóa. Vui lòng liên hệ bộ phận hỗ trợ DNV.';
            return;
        }

        RateLimiter::clear($throttleKey);
        Auth::login($user, true);
        request()->session()->regenerate();
        return $this->redirect('/tai-khoan', navigate: true);
    }

    public function submitRegister()
    {
        $this->errorMessage = '';

        $throttleKey = 'register_attempts:' . request()->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $minutes = ceil(RateLimiter::availableIn($throttleKey) / 60);
            $this->errorMessage = "Bạn đã thao tác quá nhiều lần. Vui lòng thử lại sau {$minutes} phút.";
            return;
        }

        $name = trim($this->name);
        $cleanPhone = preg_replace('/[^0-9]/', '', trim($this->phone));
        $emailInput = strtolower(trim($this->registerEmail));
        $addressInput = trim($this->registerAddress);

        if (empty($name)) {
            $this->errorMessage = 'Vui lòng nhập họ và tên thành viên.';
            return;
        }

        if (empty($cleanPhone) || strlen($cleanPhone) !== 10 || !str_starts_with($cleanPhone, '0')) {
            $this->errorMessage = 'Số điện thoại không hợp lệ (yêu cầu đúng 10 chữ số và bắt đầu bằng số 0).';
            return;
        }

        if (!empty($emailInput) && !filter_var($emailInput, FILTER_VALIDATE_EMAIL)) {
            $this->errorMessage = 'Địa chỉ email không đúng định dạng.';
            return;
        }

        if (empty($this->password) || strlen($this->password) < 8) {
            $this->errorMessage = 'Mật khẩu bảo mật phải có ít nhất 8 ký tự.';
            return;
        }

        if ($this->password !== $this->password_confirmation) {
            $this->errorMessage = 'Mật khẩu xác nhận không khớp.';
            return;
        }

        $finalEmail = !empty($emailInput) ? $emailInput : ($cleanPhone . '@dnv1.biz');

        $existingUser = User::where('phone', $cleanPhone)
            ->orWhere('email', $finalEmail)
            ->orWhere('email', $cleanPhone . '@thv.vn')
            ->first();

        if ($existingUser) {
            RateLimiter::hit($throttleKey, 300);
            $this->errorMessage = 'Số điện thoại hoặc Email này đã được đăng ký. Vui lòng chuyển sang tab Đăng nhập.';
            return;
        }

        RateLimiter::hit($throttleKey, 300);
        $user = User::create([
            'name' => $name,
            'phone' => $cleanPhone,
            'email' => $finalEmail,
            'address' => $addressInput ?: null,
            'password' => Hash::make($this->password),
            'is_active' => true,
        ]);

        Auth::login($user, true);
        request()->session()->regenerate();
        return $this->redirect('/tai-khoan', navigate: true);
    }

    public function getOrdersProperty()
    {
        if (!Auth::check()) return collect();

        $user = Auth::user();
        $query = Order::with('items.product')
            ->where('user_id', $user->id);

        if ($this->status !== 'all') {
            $statusMap = [
                'pending' => ['pending', 'Chờ xử lý'],
                'processing' => ['processing', 'Soạn hàng'],
                'shipping' => ['shipping', 'Đang giao', 'delivering'],
                'completed' => ['completed', 'Hoàn thành'],
                'cancelled' => ['cancelled', 'Đã hủy'],
            ];

            if (isset($statusMap[$this->status])) {
                $query->whereIn('status', $statusMap[$this->status]);
            } else {
                $query->where('status', $this->status);
            }
        }

        return $query->latest()->get();
    }
};
