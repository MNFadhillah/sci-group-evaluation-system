<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ActivityGroup extends Model
{
    protected $table = 'activity_groups';

    protected $fillable = [
        'id_activity',
        'group_number',
        'name',
        'formation_method',
    ];

    /**
     * Aktivitas yang memiliki kelompok ini.
     */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class, 'id_activity');
    }

    /**
     * Anggota kelompok.
     */
    public function members(): HasMany
{
    return $this->hasMany(
        ActivityGroupMember::class,
        'id_group'
    );
}

public function answers(): HasMany
{
    return $this->hasMany(
        ActivityGroupAnswer::class,
        'id_group'
    );
}
public function ratings(): HasMany
{
    return $this->hasMany(
        ActivityGroupRating::class,
        'id_group'
    );
}
}