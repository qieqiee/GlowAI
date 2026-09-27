<?php
namespace App\Services;
use App\Models\FaceAnalysis;

class MakeupShadeGuide
{
    public function lookColours(FaceAnalysis $a): ?array
    {
        if ($a->face_detected !== true || $a->face_count !== 1) return null;
        $depth = match ($a->skin_tone) { 'Very Fair', 'Fair', 'Light'=>0, 'Medium'=>1, 'Tan', 'Deep', 'Very Deep'=>2, default=>null };
        $style = ['natural'=>0, 'soft_glam'=>1, 'bold'=>2][$a->preferred_style ?? ''] ?? null;
        $families = config('look_colours', []);
        if ($depth === null || $style === null || !isset($families[$a->undertone ?? ''])) return null;
        [$blush, $blushHex, $lip, $lipHex] = $families[$a->undertone][$depth][$style];
        $finish = match ($a->preferred_style) { 'natural'=>'Apply as a sheer tint.', 'soft_glam'=>'Try a softly defined satin finish.', 'bold'=>'Build a defined statement lip; soften the eyes if you prefer one focal point.' };
        return ['Blush'=>[$blush,$blushHex], 'Lip colour'=>[$lip,$lipHex],
            'blush_tip'=>'Try '.$blush.'. Start with a small amount and blend; build to your preferred intensity.',
            'lip_tip'=>'Try '.$lip.'. '.$finish];
    }

    private function foundationNeighbours(array $foundation): array
    {
        [$name,$hex] = $foundation;
        $channels = array_map('hexdec', str_split(substr($hex,1),2));
        $lighter = array_map(fn($v)=>(int)round($v+(255-$v)*.08),$channels);
        $deeper = array_map(fn($v)=>(int)round($v*.90),$channels);
        return [[$name.' · lighter',sprintf('#%02x%02x%02x',...$lighter)],
            [$name,$hex],[$name.' · deeper',sprintf('#%02x%02x%02x',...$deeper)]];
    }

    public function build(FaceAnalysis $a): array
    {
        $single = $a->face_detected === true && $a->face_count === 1;
        $tone = $single ? $a->skin_tone : null;
        $under = $single ? $a->undertone : null;
        $foundations = [
            'Very Fair' => ['Warm'=>['Pale golden ivory','#f4dbc0'],'Cool'=>['Pale rosy ivory','#f3d6cc'],'Neutral'=>['Pale neutral ivory','#efdbcc']],
            'Very Deep' => ['Warm'=>['Golden espresso','#503220'],'Cool'=>['Rosy espresso','#503139'],'Neutral'=>['Neutral espresso','#49342b']],
            'Fair' => ['Warm'=>['Golden ivory','#efd0ae'],'Cool'=>['Rosy ivory','#f0c9be'],'Neutral'=>['Neutral ivory','#ead0bb']],
            'Light' => ['Warm'=>['Golden beige','#dfb48b'],'Cool'=>['Rosy beige','#dfb5a7'],'Neutral'=>['Neutral beige','#d9b79e']],
            'Medium' => ['Warm'=>['Golden sand','#c89565'],'Cool'=>['Rosy medium beige','#c39481'],'Neutral'=>['Medium beige','#bd967b']],
            'Tan' => ['Warm'=>['Golden caramel','#ac774b'],'Cool'=>['Rosy tan','#a97562'],'Neutral'=>['Neutral tan','#a77d5e']],
            'Deep' => ['Warm'=>['Golden deep brown','#784c2d'],'Cool'=>['Rosy deep brown','#754b43'],'Neutral'=>['Neutral deep brown','#72503c']],
        ];
        $families = [
            'Warm' => [
                'Blush'=>[['Peach','#e8a080'],['Warm rose','#c87570'],['Terracotta','#b8664e']],
                'Eyeshadow'=>[['Soft gold','#c7a36a'],['Bronze','#957044'],['Warm brown','#6f4933']],
                'Lip colour'=>[['Peach brown','#bb7963'],['Terracotta','#aa5340'],['Warm rose','#af6666']],
            ],
            'Cool' => [
                'Blush'=>[['Soft rose','#d28c9d'],['Mauve','#ac748d'],['Berry rose','#a95777']],
                'Eyeshadow'=>[['Taupe','#a2928b'],['Muted plum','#86647d'],['Cool brown','#665255']],
                'Lip colour'=>[['Rosewood','#ab6b79'],['Mauve rose','#975f79'],['Berry','#813953']],
            ],
            'Neutral' => [
                'Blush'=>[['Rose','#ce8990'],['Peach','#dfa087'],['Rose brown','#b17772']],
                'Eyeshadow'=>[['Champagne','#d8c6a8'],['Neutral brown','#927560'],['Cocoa','#695247']],
                'Lip colour'=>[['Rose brown','#aa736d'],['Soft rose','#c18088'],['Balanced red','#ac454c']],
            ],
        ];
        $foundation = $foundations[$tone ?? ''][$under ?? ''] ?? null;
        $palette = $families[$under ?? ''] ?? $families['Neutral'];
        $groups = [
            ['title'=>'Foundation family','swatches'=>$foundation ? $this->foundationNeighbours($foundation) : [],
             'note'=>$foundation ? $tone.' depth · '.$under.' undertone. Three illustrative depths to compare on your jawline; no exact match is inferred.' : 'A skin depth and undertone estimate are both needed. Use an in-person swatch to select a shade.'],
            ['title'=>'Concealer family','swatches'=>$foundation ? [[$foundation[0],$foundation[1]]] : [],
             'note'=>'For spot coverage, start close to your foundation depth and undertone. These swatches do not determine an under-eye corrector.'],
        ];
        $lookColours = $this->lookColours($a);
        foreach ($palette as $title=>$swatches) {
            if (in_array($title, ['Blush','Lip colour'], true)) {
                $groups[] = ['title'=>$title, 'swatches'=>$lookColours ? [$lookColours[$title]] : [],
                    'note'=>$lookColours ? config('makeup.styles.'.$a->preferred_style).' · '.$tone.' depth · '.$under.' undertone. One optional shade for this look.' : 'Choose a makeup style and use a photo with skin tone and undertone estimates to see a shade for this look.'];
                continue;
            }
            $groups[]=['title'=>$title,'swatches'=>$swatches,'note'=>isset($families[$under ?? '']) ? 'Optional colour family for the estimated '.$under.' undertone.' : 'General colour ideas only; undertone was not available.'];
        }
        $groups[]=['title'=>'Eyeliner','swatches'=>[['Soft brown','#6b4b3c'],['Deep brown','#3f2c29'],['Charcoal','#3e3d40']],
            'note'=>'Choose a softer or more defined lash line to suit your selected look.'];
        foreach ($groups as &$group) {
            $group['note'] = \App\Support\SkinToneLabel::display($group['note']);
            foreach ($group['swatches'] as &$swatch) {
                $swatch[0] = \App\Support\SkinToneLabel::display($swatch[0]);
            }
            unset($swatch);
        }
        unset($group);
        if ($lookColours) {
            foreach (['blush_tip','lip_tip'] as $key) {
                $lookColours[$key] = \App\Support\SkinToneLabel::display($lookColours[$key]);
            }
        }
        $shape = $single ? $a->guideShape() : null;
        $shapeGuide = config('face_makeup', [])[$shape ?? ''] ?? null;
        return ['look_colours'=>$lookColours,'groups'=>$groups,'personalised'=>isset($families[$under ?? '']), 'shape'=>$shapeGuide ? $shape : null, 'shape_guide'=>$shapeGuide];
    }
}
