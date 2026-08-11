<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBadge extends Model
{
    use HasFactory;

    protected $table = 'user_badge';
    // Tambahkan id_activity di sini
    protected $fillable = ['id_student', 'id_badge', 'id_class', 'id_activity'];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_student');
    }

    public function badge()
    {
        return $this->belongsTo(Badge::class, 'id_badge');
    }
    
    public function kelas()
    {
        return $this->belongsTo(Classes::class, 'id_class');
    }
}