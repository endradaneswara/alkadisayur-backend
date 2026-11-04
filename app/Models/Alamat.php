<?php

namespace App\Models;

use Database\Factories\AlamatFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read User|null $user
 */
class Alamat extends Model
{
    /** @use HasFactory<AlamatFactory> */
    use HasFactory;

    protected $table = 'alamat';

    protected $fillable = [
        'user_id',
        'nama_alamat',
        'nama_penerima',
        'no_hp',
        'alamat_lengkap',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
