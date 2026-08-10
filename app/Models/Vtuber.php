<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vtuber extends Model
{
    use HasFactory;

    protected $table = 'vtubers';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'gender',
        'debut_date',
        'birthday',
        'status',
        'avatar',
        'banner'
    ];

    protected $casts = [
        'debut_date' => 'date'
    ];

    // Relationships
    public function organizations()
    {
        return $this->belongsToMany(
            Organization::class,
            'organization_members'
        )->withPivot([
            'generation',
            'joined_at',
            'left_at',
            'status'
        ])->wherePivot('status', 'active');
    }

    public function socialAccounts()
    {
        return $this->hasMany(SocialAccount::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'vtuber_tags');
    }
}
