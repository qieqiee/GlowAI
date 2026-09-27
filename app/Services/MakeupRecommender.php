<?php
namespace App\Services;

use App\Models\FaceAnalysis;
use InvalidArgumentException;

class MakeupRecommender
{
    public function recommend(FaceAnalysis $analysis, string $occasion, string $style): array
    {
        $occasions = config('makeup.occasions');
        $styles = config('makeup.styles');
        if (! isset($occasions[$occasion], $styles[$style])) {
            throw new InvalidArgumentException('Unsupported makeup preferences.');
        }
        // Ambiguous/group photos must not produce a personalised facial claim.
        $single = $analysis->face_detected === true && $analysis->face_count === 1;
        $tone = $single ? $analysis->skin_tone : null;
        $undertone = $single ? $analysis->undertone : null;
        $shape = $single ? $analysis->guideShape() : null;
        $palettes = [
            'Warm' => ['eyes' => 'soft bronze or golden brown', 'cheeks' => 'peach or warm rose', 'lips' => 'peach-brown or terracotta'],
            'Cool' => ['eyes' => 'taupe or muted plum', 'cheeks' => 'rose or mauve', 'lips' => 'rosewood or berry'],
            'Neutral' => ['eyes' => 'neutral brown or champagne', 'cheeks' => 'rose or peach', 'lips' => 'rose-brown or a balanced red'],
        ];
        $palette = $palettes[$undertone ?? ''] ?? ['eyes' => 'a brown you enjoy wearing', 'cheeks' => 'your preferred rose or peach', 'lips' => 'a shade you already enjoy'];
        $why = ["Your selected {$styles[$style]} style sets the intensity; {$occasions[$occasion]} sets the occasion tips."];
        $missing = [];
        if (isset($palettes[$undertone ?? ''])) {
            $why[] = "The estimated {$undertone} undertone suggests the starting colour palette. You can choose other colours.";
        } else {
            $missing[] = 'Undertone';
            $why[] = 'No usable undertone estimate: the palette stays flexible rather than assuming Warm, Cool or Neutral.';
        }
        $depth = match ($tone) {
            'Very Fair', 'Fair', 'Light' => 'For a soft effect, start with a small amount of blush and build gradually.',
            'Medium' => 'Start with a sheer layer, then build colour until it is visible at your preferred intensity.',
            'Tan', 'Deep', 'Very Deep' => 'Try a colour with enough pigment to show at your preferred intensity; build in thin layers.',
            default => 'Swatch the colour and build gradually to the intensity you prefer.',
        };
        if (in_array($tone, ['Very Fair','Fair','Light','Medium','Tan','Deep','Very Deep'], true)) {
            $why[] = "The estimated {$tone} skin depth informs the colour-intensity tip, not a foundation shade number.";
        } else {
            $missing[] = 'Skin tone';
        }
        $placement = match ($shape) {
            'Round' => 'For an optional lifted effect, place blush slightly above the cheek apples and blend toward the temples.',
            'Oval' => 'Try blush on the cheek apples and blend softly outward along the cheekbones.',
            'Square' => 'Try softly rounded blush placement on the cheek apples with well-blended edges.',
            'Heart' => 'Try blush just below the cheek apples, blending outward with a light hand.',
            'Diamond' => 'Blend blush softly across the cheeks; keep the widest cheekbone area subtle if desired.',
            'Triangle' => 'Place blush on the upper cheeks and blend toward the temples.',
            'Rectangle' => 'Blend blush horizontally across the cheeks with soft edges.',
            'Oblong' => 'Try blending blush outward across the cheeks rather than extending it far upward.',
            default => 'Start on the cheeks and adjust the placement to the effect you prefer; no shape-specific contour is assumed.',
        };
        if (in_array($shape, ['Round','Oval','Square','Heart','Oblong','Diamond','Triangle','Rectangle'], true)) {
            $why[] = "The {$shape} shape used for your guide provides an optional blush-placement idea.";
        } else {
            $missing[] = 'Face shape';
        }
        $base = match ($style) {
            'natural' => 'Use a sheer base or spot concealer where wanted, keeping the finish light.',
            'soft_glam' => 'Build a light-to-medium base in thin layers and blend the edges well.',
            'bold' => 'Build coverage only where wanted; keep the base blended so the statement colour stands out.',
        };
        $eyes = match ($style) {
            'natural' => "Use a light wash of {$palette['eyes']} with a little mascara if desired.",
            'soft_glam' => "Blend {$palette['eyes']} with a softly defined lash line and optional subtle shimmer.",
            'bold' => "Build {$palette['eyes']} into a more defined eye, with optional liner or shimmer.",
        };
        $lips = $style === 'natural' ? "Try a sheer tint in {$palette['lips']}." :
            ($style === 'soft_glam' ? "Try a softly defined satin lip in {$palette['lips']}." : "Try a more defined lip in {$palette['lips']}; soften the eyes if you prefer one focal point.");
        $eventTip = match ($occasion) {
            'everyday' => 'Keep the routine easy to touch up; use only the steps you enjoy.',
            'work' => 'Check the look in your usual classroom or workplace lighting and adjust the intensity to your preference.',
            'wedding' => 'Do a trial before the event and keep your chosen lip colour available for touch-ups.',
            'graduation' => 'Test the look with a photo in similar lighting before the ceremony and plan simple touch-ups.',
            'evening' => 'Check the colour in evening lighting; add definition only if you want it.',
        };
        $styledAnalysis = clone $analysis;
        $styledAnalysis->preferred_style = $style;
        $lookColours = app(MakeupShadeGuide::class)->lookColours($styledAnalysis);
        return [
            'recommended_makeup_look' => $styles[$style].' — '.$occasions[$occasion],
            'recommendation_reason' => implode(' ', $why),
            'recommendation_details' => [
                'version' => 'rules-v1',
                'base' => $base.' Match foundation with an in-person jawline swatch; a photo cannot select an exact shade.',
                'eyes' => $eyes,
                'blush' => $lookColours['blush_tip'] ?? ('Try '.$palette['cheeks'].'. '.$depth),
                'lips' => $lookColours['lip_tip'] ?? $lips,
                'placement' => $placement,
                'occasion_tip' => $eventTip,
                'missing_analysis' => $missing,
            ],
        ];
    }
}

