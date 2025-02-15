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

    /**
     * Sync all projects with the customer's SEO providers
     */
    public function syncProjectsWithSeoProviders(): void
    {
        $seoProviderIds = $this->seoProviders()->pluck('users.id')->toArray();
        $this->load('projects'); // Ensure projects are loaded
        foreach ($this->projects as $project) {
            $project->seoProviders()->sync($seoProviderIds);
        }
    }

    protected static function boot()
    {
        parent::boot();

        // When a customer is updated
        static::updated(function ($customer) {
            $customer->syncProjectsWithSeoProviders();
        });

        // When SEO providers are attached or detached
        static::saved(function ($customer) {
            if ($customer->wasChanged()) {
                $customer->load('seoProviders'); // Ensure we have fresh data
                $customer->syncProjectsWithSeoProviders();
            }
        });

        // Listen to the pivot table events
        static::$dispatcher->listen('eloquent.saved: *', function ($event, $models) {
            if (isset($models[0]) && $models[0] instanceof \Illuminate\Database\Eloquent\Relations\Pivot) {
                $pivot = $models[0];
                if ($pivot->getTable() === 'customer_user') {
                    $customer = Customer::with(['projects', 'seoProviders'])->find($pivot->customer_id);
                    if ($customer) {
                        $customer->syncProjectsWithSeoProviders();
                    }
                }
            }
        });
    }
} 