<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityGroupMember extends Model
{
    protected $table = 'activity_group_members';

    protected $fillable = [
        'id_group',
        'id_user',
    ];

    /**
     * Kelompok tempat siswa berada.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(
            ActivityGroup::class,
            'id_group'
        );
    }

    /**
     * User/siswa yang menjadi anggota.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'id_user'
        );
    }
}