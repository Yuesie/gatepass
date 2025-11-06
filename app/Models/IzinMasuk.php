<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;
use App\Models\DetailBarang;

class IzinMasuk extends Model
{
    use HasFactory;

    protected $table = 'izin_masuk';

    protected $fillable = [
        'id_pembuat',
        'keperluan',
        'tanggal_masuk',
        'lokasi_tujuan',
        'status',
        'id_approver_l1',
        'id_approver_l2',
        'id_approver_l3',
        'l1_rejected',
        'l2_rejected',
        'l3_rejected',
    ];

    // Relasi ke User (pembuat gatepass)
    public function pemohon(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_pemohon');
    }

    // Relasi ke approver level 1–3
    public function approverL1(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_approver_l1');
    }

    public function approverL2(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_approver_l2');
    }

    public function approverL3(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_approver_l3');
    }

    // Relasi ke detail barang (pastikan sama dengan pemanggilan di controller)
    public function detailBarang(): HasMany
    {
        return $this->hasMany(DetailBarang::class, 'izin_masuk_id');
    }
}
