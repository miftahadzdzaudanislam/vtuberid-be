<?php

namespace App\Models;

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
}
