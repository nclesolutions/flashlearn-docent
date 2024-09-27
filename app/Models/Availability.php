<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Availability extends Model
{
    use HasFactory;

    protected $fillable = [
        'study_guide_id',
        'student_id'
    ];

    public function studyGuide()
    {
        return $this->belongsTo(StudyGuide::class);
    }
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

}
