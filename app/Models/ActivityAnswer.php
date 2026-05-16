<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityAnswer extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_activity',
        'id_user',
        'id_question',
        'user_answer',
        'is_correct',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    /**
     * =========================
     * RELATIONS
     * =========================
     */

    // ke activity
    public function activity()
    {
        return $this->belongsTo(Activity::class, 'id_activity');
    }

    // ke user (siswa)
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    // ke soal
    public function question()
    {
        return $this->belongsTo(Question::class, 'id_question');
    }
}
