<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FaceAnalysis extends Model
{
    protected $fillable = [
        'customer_id',
        'image_path',
        'face_detected',
        'face_count',
        'skin_tone',
        'undertone',
        'analysis_quality',
        'face_shape',
        'face_shape_details',
        'recommended_makeup_look',
        'recommendation_reason',
        'occasion',
        'preferred_style',
        'recommendation_details',
    ];

    protected function casts(): array
    {
        return ['face_detected' => 'boolean', 'face_count' => 'integer', 'analysis_quality' => 'array', 'face_shape_details' => 'array', 'recommendation_details' => 'array'];
    }

    public function shapeCandidates(): array
    {
        if (!$this->face_detected || $this->face_count !== 1 || $this->face_shape !== null
            || ($this->analysis_quality['face_shape'] ?? null) !== 'shape_uncertain') return [];
        $items = $this->face_shape_details['candidates'] ?? [];
        return is_array($items) && count($items) === 2 ? $items : [];
    }

    public function guideShape(): ?string
    {
        $selected = $this->face_shape_details['user_selected_shape'] ?? null;
        return in_array($selected, $this->shapeCandidates(), true) ? $selected : $this->face_shape;
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
}
