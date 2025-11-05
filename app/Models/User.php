<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    // ===================================================================
    // HELPER PERAN (Role Helpers)
    // Digunakan untuk meningkatkan keterbacaan kode otorisasi
    // ===================================================================

    /**
     * Cek apakah pengguna adalah Administrator.
     */
    public function isAdmin(): bool
    {
        return $this->peran === 'admin';
    }
    
    /**
     * Cek apakah pengguna adalah Approver Level 3 (Final / Manager).
     */
    public function isManager(): bool
    {
        return $this->peran === 'manager';
    }

    /**
     * Cek apakah pengguna adalah salah satu dari Approver Level 1 (Atasan Pemohon, Teknik, HSSE).
     */
    public function isL1Approver(): bool
    {
        return in_array($this->peran, ['atasan_pemohon', 'teknik', 'hsse']);
    }

    /**
     * Cek apakah pengguna adalah Security (Approver Level 2).
     */
    public function isSecurity(): bool
    {
        return $this->peran === 'security';
    }

    /**
     * Cek apakah pengguna adalah pemohon (basic user).
     */
    public function isPemohon(): bool
    {
        return $this->peran === 'pemohon';
    }


    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'peran', // PENTING: Harus ada untuk mass assignment dari AdminController
        'jabatan_default',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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
}