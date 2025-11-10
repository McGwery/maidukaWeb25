<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasUuid, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
    ];


    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}