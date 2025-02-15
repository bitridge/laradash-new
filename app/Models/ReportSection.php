<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

class ReportSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_id',
        'title',
        'content',
        'order',
        'image_path'
    ];

    protected $casts = [
        'content' => 'array',
        'order' => 'integer'
    ];

    protected function content(): Attribute
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

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }
} 