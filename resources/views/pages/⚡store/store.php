<?php

namespace App\Livewire;

use App\Models\Contact;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.app')] class extends Component
{
    public $setting;

    public $name = '';
    public $phone = '';
    public $email = '';
    public $subject = '';
    public $message = '';
    public $isSubmitted = false;
    public $successMessage = '';

    public function mount()
    {
        $this->setting = Setting::getSystemSetting();
    }

    public function submitContact()
    {
        $throttleKey = 'contact_submit:' . request()->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->addError('name', 'Bạn đã gửi liên hệ quá nhiều lần. Vui lòng chờ ' . ceil($seconds / 60) . ' phút.');
            return;
        }

        $this->validate([
            'name' => 'required|min:2|max:100',
            'phone' => 'required|min:9|max:15',
            'email' => 'nullable|email|max:100',
            'subject' => 'nullable|string|max:150',
            'message' => 'required|min:5|max:2000',
        ], [
            'name.required' => 'Vui lòng nhập họ và tên của bạn.',
            'name.min' => 'Họ và tên tối thiểu 2 ký tự.',
            'phone.required' => 'Vui lòng nhập số điện thoại liên hệ.',
            'phone.min' => 'Số điện thoại không hợp lệ.',
            'email.email' => 'Địa chỉ email không đúng định dạng.',
            'message.required' => 'Vui lòng nhập nội dung liên hệ hoặc nhu cầu tư vấn.',
            'message.min' => 'Nội dung tối thiểu 5 ký tự.',
        ]);

        RateLimiter::hit($throttleKey, 600);

        $contact = Contact::create([
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'subject' => $this->subject ?: 'Liên hệ từ website DNV (Trang Cửa hàng & Liên hệ)',
            'message' => $this->message,
            'status' => 'pending',
        ]);

        // Gửi email thông báo cho ban quản trị (Issue #15)
        try {
            $adminEmail = $this->setting?->general_settings['email']['admin_email']
                ?? $this->setting?->general_settings['email']['sender_email']
                ?? config('mail.from.address');

            if (!empty($adminEmail)) {
                $contactData = [
                    'name' => $this->name,
                    'phone' => $this->phone,
                    'email' => $this->email ?: 'Chưa cung cấp',
                    'subject' => $contact->subject,
                    'body' => $this->message,
                ];

                Mail::raw(
                    "Thông báo có liên hệ mới từ website DNV:\n\n" .
                    "- Họ tên: {$contactData['name']}\n" .
                    "- SĐT: {$contactData['phone']}\n" .
                    "- Email: {$contactData['email']}\n" .
                    "- Tiêu đề: {$contactData['subject']}\n" .
                    "- Nội dung:\n{$contactData['body']}\n\n" .
                    "Vào trang quản trị để xem và phản hồi khách hàng: " . url('/hg-admin/contacts'),
                    function ($m) use ($adminEmail) {
                        $m->to($adminEmail)->subject('[DNV] Thông báo: Khách hàng gửi liên hệ mới');
                    }
                );
            }
        } catch (\Throwable $e) {
            Log::warning('Send contact notification email error: ' . $e->getMessage());
        }

        $this->reset(['name', 'phone', 'email', 'subject', 'message']);

        $store = $this->setting?->interface_settings['store'] ?? [];
        $this->successMessage = !empty($store['contact_form_success_message']) 
            ? $store['contact_form_success_message'] 
            : 'Cảm ơn bạn đã gửi liên hệ! Ban Dự Án DNV sẽ liên hệ lại với bạn trong thời gian sớm nhất.';
        $this->isSubmitted = true;
    }

    public function resetForm()
    {
        $this->isSubmitted = false;
    }
};
