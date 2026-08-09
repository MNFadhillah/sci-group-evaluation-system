<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityGroupAnswer extends Model
{
    protected $table = 'activity_group_answers';

    protected $fillable = [
        'id_activity',
        'id_group',
        'id_question',
        'id_user',
        'answer',
    ];

    /**
     * Aktivitas.
     */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(
            Activity::class,
            'id_activity'
        );
    }

    /**
     * Kelompok.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(
            ActivityGroup::class,
            'id_group'
        );
    }

    /**
     * Soal.
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(
            Question::class,
            'id_question'
        );
    }

    /**
     * Siswa yang memberikan jawaban.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'id_user'
        );
    }
}