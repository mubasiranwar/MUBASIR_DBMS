<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = [
        'user_id',
        'admission_no',
        'gender',
        'date_of_birth',
        'phone',
        'address',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function enrollments()
    {
        return $this->hasMany(StudentEnrollment::class);
    }

    public function latestEnrollment()
    {
        return $this->hasOne(StudentEnrollment::class)->latest()->with('classSection.schoolClass');
    }

    // Accessor to get roll number from enrollment
    public function getRollNoAttribute()
    {
        $enrollment = $this->enrollments()->latest()->first();
        return $enrollment ? $enrollment->roll_no : null;
    }

    // Accessor to get schoolClass via latest enrollment
    public function getSchoolClassAttribute()
    {
        $enrollment = $this->enrollments()->latest()->with('classSection.schoolClass')->first();
        return $enrollment ? $enrollment->classSection->schoolClass : null;
    }

    public function marks()
    {
        return $this->hasMany(Mark::class);
    }
}