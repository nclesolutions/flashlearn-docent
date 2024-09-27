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
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
    // Definieer de relatie met het SchoolClass model (klassen)
    public function schoolClass() // Wijzig relatie naam naar schoolClass
    {
        return $this->belongsTo(SchoolClass::class, 'class_id'); // Zorg ervoor dat je de juiste vreemde sleutel gebruikt
    }
}
