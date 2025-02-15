<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Report extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'project_id',
        'title',
        'description',
        'status',
        'generated_by',
        'generated_at'
    ];

    protected $casts = [
        'description' => 'array',
        'generated_at' => 'datetime'
    ];

    protected function description(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                $value = is_string($value) ? json_decode($value, true) : $value;
                return $value;
            },
            set: function ($value) {
                if (is_string($value)) {
                    return [
                        'content' => $value,
                        'plainText' => strip_tags($value)
                    ];
                }
                return $value;
            }
        );
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(ReportSection::class);
    }

    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function seoLogs(): BelongsToMany
    {
        return $this->belongsToMany(SeoLog::class, 'report_seo_log');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('section_images');
    }
} 