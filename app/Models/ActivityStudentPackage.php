<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityStudentPackage extends Model
{
    use HasFactory;

    protected $table = 'activity_student_packages';

    protected $fillable = [
        'id_activity',
        'id_user',
        'started_at',
        'submitted_at',
        'deadline_at',
        'status',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
        'deadline_at' => 'datetime',
    ];

    /**
     * Aktivitas yang dimiliki package ini.
     */
    public function activity()
    {
        return $this->belongsTo(
            Activity::class,
            'id_activity'
        );
    }

    /**
     * Siswa pemilik package.
     */
    public function user()
    {
        return $this->belongsTo(
            User::class,
            'id_user'
        );
    }

    /**
     * Soal-soal dalam package.
     */
    public function questions()
    {
        return $this->hasMany(
            ActivityStudentQuestion::class,
            'id_package'
        )->orderBy('question_order');
    }
}