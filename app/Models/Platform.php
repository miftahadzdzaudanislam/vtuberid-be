<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Platform extends Model
{
    use HasFactory;

    protected $table = 'platforms';

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'base_url'
    ];

    // Relationships
    public function socialAccounts()
    {
        return $this->hasMany(SocialAccount::class);
    }

    // Assessors
    protected function iconUrl()
    {
        return Attribute::make(
            get: fn() => $this->icon
                ? asset('storage/' . $this->icon)
                : null
        );
    }

    // Scopes
    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', "%{$search}%");
    }
}
