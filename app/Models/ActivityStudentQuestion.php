<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityStudentQuestion extends Model
{
    use HasFactory;

    protected $table = 'activity_student_questions';

    protected $fillable = [
        'id_package',
        'id_question',
        'question_order',
    ];

    /**
     * Package milik siswa.
     */
    public function package()
    {
        return $this->belongsTo(
            ActivityStudentPackage::class,
            'id_package'
        );
    }

    /**
     * Soal yang diberikan.
     */
    public function question()
    {
        return $this->belongsTo(
            Question::class,
            'id_question'
        );
    }
}