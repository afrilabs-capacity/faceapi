<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class websites extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'unique_id'];

    public function websiteUsers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(WebsiteUsers::class);
    }
}
