<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebsiteUsers extends Model
{
    use HasFactory;

    protected $fillable = ['unique_id', 'websites_id', 'status', 'storage'];

    public function website(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(websites::class);
    }
}
