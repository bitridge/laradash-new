<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }
} 