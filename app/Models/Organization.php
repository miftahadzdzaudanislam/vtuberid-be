<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    use HasFactory;

    protected $table = 'organizations';

    protected $fillable = [
        'name',
        'slug',
        'yt_username',
        'type',
        'description',
        'logo',
        'website',
        'status'
    ];

    // Relationships
    public function vtubers()
    {
        return $this->belongsToMany(
            Vtuber::class,
            'organization_members',
        )->withPivot([
            'generation',
            'joined_at',
            'left_at',
            'status'
        ]);
    }

    public function orgSocialAccounts()
    {
        return $this->hasMany(OrganizationSocialAccount::class);
    }

    // Scope
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Assessors
    protected function logoUrl()
    {
        return Attribute::make(
            get: fn() => $this->logo
                ? asset('storage/' . $this->logo)
                : null
        );
    }

    // Helper Methods
    public function isAgency()
    {
        return $this->type === 'agency';
    }

    public function isGroup()
    {
        return $this->type === 'group';
    }

    public function syncVtuberMembership()
    {
        if ($this->status !== 'liquidated') {
            return;
        }

        $this->vtubers()
            ->wherePivot('status', 'active')
            ->get()
            ->each(function ($vtuber) {

                $this->vtubers()->updateExistingPivot(
                    $vtuber->id,
                    [
                        'status' => 'graduated',
                    ]
                );

                $vtuber->updateCurrentAffiliation();
            });
    }
}
