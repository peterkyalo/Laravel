<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Instrument extends Model
{
    protected $fillable = ['name', 'slug', 'icon', 'description'];

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
