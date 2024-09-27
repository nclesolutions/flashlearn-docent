<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudyGuide extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_id',
        'class_id',
        'title'
    ];

    public function availabilities()
    {
        return $this->hasMany(Availability::class);
    }
}
