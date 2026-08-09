<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialAccount extends Model
{
    use HasFactory;

    protected $table = 'social_accounts';

    protected $fillable = [
        'vtuber_id',
        'platform_id',
        'username',
        'url'
    ];

    // Relationships
    public function vtuber()
    {
        return $this->belongsTo(Vtuber::class);
    }

    public function platform()
    {
        return $this->belongsTo(Platform::class);
    }
}
