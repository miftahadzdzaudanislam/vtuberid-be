<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
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
        'current_affiliation',
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
        ]);
    }

    public function activeOrganizations()
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

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // Accessors
    protected function avatarUrl()
    {
        return Attribute::make(
            get: fn() => $this->avatar
                ? asset('storage/' . $this->avatar)
                : null
        );
    }

    protected function bannerUrl()
    {
        return Attribute::make(
            get: fn() => $this->banner
                ? asset('storage/' . $this->banner)
                : null
        );
    }

    // Helper Methods
    public function isIndependent()
    {
        return !$this->organizations()->wherePivot('status', 'active')->exists();
    }
}
