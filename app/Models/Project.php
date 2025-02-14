<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Project extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'name',
        'website_url',
        'customer_id',
        'status',
        'start_date',
        'details',
    ];

    protected $casts = [
        'start_date' => 'date',
        'details' => 'array', // For Quill editor content
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function seoLogs(): HasMany
    {
        return $this->hasMany(SeoLog::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function seoProviders(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_seo_provider', 'project_id', 'user_id')
            ->withTimestamps();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')
            ->singleFile()
            ->useDisk('public')
            ->registerMediaConversions(function (Media $media) {
                $this->addMediaConversion('thumbnail')
                    ->width(80)
                    ->height(80)
                    ->sharpen(10)
                    ->nonQueued();
                
                $this->addMediaConversion('preview')
                    ->width(400)
                    ->height(300)
                    ->sharpen(10)
                    ->nonQueued();
            });
    }
} 