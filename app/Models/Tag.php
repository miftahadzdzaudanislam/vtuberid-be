<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

    protected $table = 'tags';

    protected $fillable = [
        'name',
        'slug'
    ];

    protected $hidden = ['pivot'];

    // Relationships
    public function vtubers()
    {
        return $this->belongsToMany(Vtuber::class, 'vtuber_tags');
    }

    // Scopes
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($query) use ($search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%");
        });
    }

    // Assesors
    public function getVtubersCountAttribute()
    {
        return $this->vtubers()->count();
    }
}
