<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class SeoLog extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'project_id',
        'user_id',
        'log_type',
        'title',
        'content',
        'action_items',
        'recommendations',
        'date',
        'work_description',
        'keywords_targeted',
        'backlinks_created',
        'rankings_improvement',
        'additional_notes',
    ];

    protected $casts = [
        'date' => 'date',
        'content' => 'array',
        'action_items' => 'array',
        'recommendations' => 'array',
        'keywords_targeted' => 'array',
        'backlinks_created' => 'array',
        'rankings_improvement' => 'array',
    ];

    const TYPES = [
        'technical' => 'Technical SEO',
        'analytics' => 'SEO Analytics & Reporting',
        'off_page' => 'Off-Page SEO',
        'on_page' => 'On-Page SEO',
        'local' => 'Local SEO',
        'content' => 'Content Optimization',
        'social' => 'Social Media',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reports(): BelongsToMany
    {
        return $this->belongsToMany(Report::class, 'report_seo_logs')
            ->withTimestamps();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('attachments')
            ->useDisk('public')
            ->registerMediaConversions(function (Media $media) {
                $this->addMediaConversion('thumbnail')
                    ->width(200)
                    ->height(200)
                    ->sharpen(10)
                    ->nonQueued();

                $this->addMediaConversion('preview')
                    ->width(800)
                    ->height(800)
                    ->sharpen(10)
                    ->nonQueued();
            });
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if (is_string($model->content)) {
                $model->content = json_decode($model->content, true);
            }
        });
    }
} 