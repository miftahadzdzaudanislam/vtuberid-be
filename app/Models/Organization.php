<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    use HasFactory;

    protected $table = 'organizations';

    protected $fillable = [
        'name',
        'slug',
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
            'organization_members'
        )->withPivot([
            'generation',
            'joined_at',
            'left_at',
            'status'
        ]);
    }
}
