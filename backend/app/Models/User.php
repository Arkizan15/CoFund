<?php

namespace App\Models;

use App\Enums\RoleEnum;
use App\Mail\NotifikasiEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'balance',
        'avatar_url',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'role' => RoleEnum::class,
        'balance' => 'decimal:2',
        'suspended_at' => 'datetime',
    ];

    /**
     * Send the password reset notification using the CoFund email template.
     */
    public function sendPasswordResetNotification($token): void
    {
        $frontendUrl = config('app.frontend_url');
        $resetUrl = $frontendUrl . '/reset-password?token=' . $token . '&email=' . urlencode($this->email);

        Mail::to($this->email)->send(new NotifikasiEmail(
            subject: 'Reset Password — CoFund',
            greeting: 'Halo ' . ($this->name ?? 'Pengguna') . '!',
            messageContent: "Kami menerima permintaan reset password untuk akun CoFund Anda.\n\nKlik tombol di bawah untuk membuat password baru. Link ini akan kedaluwarsa dalam 60 menit.\n\nJika Anda tidak meminta reset password, abaikan email ini.",
            actionText: 'Reset Password',
            actionUrl: $resetUrl,
        ));
    }

    /**
     * Send the email verification notification using the CoFund email template.
     */
    public function sendEmailVerificationNotification(): void
    {
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $this->id,
                'hash' => sha1($this->getEmailForVerification()),
            ]
        );

        Mail::to($this->email)->send(new NotifikasiEmail(
            subject: 'Verifikasi Email — CoFund',
            greeting: 'Halo ' . ($this->name ?? 'Pengguna') . '!',
            messageContent: "Terima kasih telah mendaftar di CoFund!\n\nSilakan klik tombol di bawah untuk memverifikasi alamat email Anda. Link ini akan kedaluwarsa dalam 60 menit.\n\nJika Anda tidak mendaftar di CoFund, abaikan email ini.",
            actionText: 'Verifikasi Email',
            actionUrl: $verificationUrl,
        ));
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }

    public function creatorRequests(): HasMany
    {
        return $this->hasMany(CreatorRequest::class);
    }

    public function walletTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function backings(): HasMany
    {
        return $this->hasMany(Backing::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
