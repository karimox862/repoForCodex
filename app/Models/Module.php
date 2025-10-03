<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Module extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'title',
        'professor_id',
    ];

    public function professor(): BelongsTo
    {
        return $this->belongsTo(Professor::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class)
            ->withPivot('label')
            ->withTimestamps();
    }

    public function marks(): HasMany
    {
        return $this->hasMany(Mark::class);
    }
}
