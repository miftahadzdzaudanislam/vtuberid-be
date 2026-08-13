<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationSocialAccount extends Model
{
    use HasFactory;

    protected $table = 'organization_social_accounts';

    protected $fillable = [
        'organization_id',
        'platform_id',
        'username',
        'url'
    ];

    // Relationships
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function platform()
    {
        return $this->belongsTo(Platform::class);
    }
}
