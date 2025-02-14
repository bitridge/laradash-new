<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Report extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'project_id',
        'title',
        'description',
        'generated_by',
        'status',
        'generated_at',
    ];

    protected $casts = [
        'generated_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function seoLogs(): BelongsToMany
    {
        return $this->belongsToMany(SeoLog::class, 'report_seo_logs')
            ->withTimestamps();
    }

    public function sections(): HasMany
    {
        return $this->hasMany(ReportSection::class)->orderBy('order');
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('report_images')
            ->useDisk('public');
    }
} 