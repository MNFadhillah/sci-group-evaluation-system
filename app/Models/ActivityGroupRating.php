<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityGroupRating extends Model
{
    protected $table = 'activity_group_ratings';

    protected $fillable = [
        'id_activity',
        'id_group',
        'id_evaluator',
        'id_evaluated',
        'score',
        'comment',
    ];

    /**
     * Aktivitas yang dinilai.
     */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(
            Activity::class,
            'id_activity'
        );
    }

    /**
     * Kelompok yang dinilai.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(
            ActivityGroup::class,
            'id_group'
        );
    }

    /**
     * User yang memberikan penilaian.
     */
    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'id_evaluator'
        );
    }

    /**
     * User yang menerima penilaian.
     */
    public function evaluated(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'id_evaluated'
        );
    }
}