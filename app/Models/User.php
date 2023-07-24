<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'nim',
        'role',
        'email',
        'faculty_id',
        'study_id',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }

    public function study()
    {
        return $this->belongsTo(Study::class);
    }

    public function classrooms()
    {
        return $this->belongsToMany(Classroom::class);
    }

    public function teachingon()
    {
        return $this->hasMany(Classroom::class, 'teacher_id')->whereHas('topics');
    }

    public function userResults()
    {
        return $this->hasMany(Result::class);
    }

    public function topics()
    {
        return $this->belongsToMany(Topic::class)
            ->withPivot(['access_time', 'stop_access'])
            ->withTimestamps();
    }
}
