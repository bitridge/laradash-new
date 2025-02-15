<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Customer extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'company',
        'address',
    ];

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function seoProviders(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'customer_user')
            ->where('role', 'seo_provider')
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
            });
    }

    protected static function boot()
    {
        parent::boot();

        // When a customer is assigned to a SEO provider, assign all projects to them
        static::updated(function ($customer) {
            $seoProviderIds = $customer->seoProviders->pluck('id');
            foreach ($customer->projects as $project) {
                $project->seoProviders()->sync($seoProviderIds);
            }
        });
    }
} 