<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class BlogPost extends Model
{
    public const CATEGORIES = [
        'Masterclass & Technique',
        'Classical Repertoire',
        'Music Theory & Ear Training',
        'Conservatory Life & News',
        'Virtuoso Spotlights',
    ];

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'body',
        'cover_image',
        'category',
        'tags',
        'author_id',
        'is_published',
        'published_at',
        'read_time_minutes',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'read_time_minutes' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function comments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(BlogComment::class, 'blog_post_id');
    }

    public function approvedComments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(BlogComment::class, 'blog_post_id')->where('status', 'approved')->latest();
    }

    public function getTagsListAttribute(): array
    {
        if (empty($this->tags)) {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $this->tags))));
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)->whereNotNull('published_at');
    }

    public function coverUrl(): string
    {
        if ($this->cover_image && (str_starts_with($this->cover_image, 'http') || file_exists(public_path($this->cover_image)))) {
            return str_starts_with($this->cover_image, 'http') ? $this->cover_image : asset($this->cover_image);
        }

        // Beautiful SVG fallback based on category
        if (str_contains(strtolower($this->category), 'piano')) {
            return asset('images/courses/piano.svg');
        } elseif (str_contains(strtolower($this->category), 'string') || str_contains(strtolower($this->category), 'violin')) {
            return asset('images/courses/violin.svg');
        } elseif (str_contains(strtolower($this->category), 'vocal')) {
            return asset('images/courses/vocal.svg');
        }

        return asset('images/courses/guitar.svg');
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($post) {
            if (empty($post->slug)) {
                $post->slug = Str::slug($post->title);
                // Ensure unique slug
                $count = static::where('slug', 'like', $post->slug.'%')->count();
                if ($count > 0) {
                    $post->slug .= '-'.($count + 1);
                }
            }
            if (empty($post->published_at) && $post->is_published) {
                $post->published_at = now();
            }
        });
    }
}
