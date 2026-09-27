<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class FaceDetector
{
    public function detect(UploadedFile $image): array
    {
        $url = rtrim(config('face_detection.url'), '/');
        // Never allow face images to leave this machine, even through redirects/proxies.
        if (! preg_match('~^http://127\.0\.0\.1:[0-9]{1,5}$~D', $url)) {
            throw new RuntimeException('Face detection must use a loopback URL.');
        }
        $stream = fopen($image->getRealPath(), 'rb');
        try {
            $response = Http::acceptJson()->connectTimeout(3)->timeout(20)
                ->withOptions(['allow_redirects' => false, 'proxy' => ''])
                ->attach('image', $stream, 'photo.'.$image->extension())
                ->post($url.'/detect-face');
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }
        $result = $response->json();
        if (! $response->successful() || ! is_array($result)
            || ! is_bool($result['face_detected'] ?? null)
            || ! is_int($result['face_count'] ?? null)
            || $result['face_count'] < 0
            || $result['face_detected'] !== ($result['face_count'] > 0)) {
            throw new RuntimeException('Face detection returned an invalid response.');
        }
        // Older face-only services may omit this field; never fabricate a tone.
        $tone = $result['skin_tone'] ?? null;
        if (! in_array($tone, [null, 'Very Fair', 'Fair', 'Light', 'Medium', 'Tan', 'Deep', 'Very Deep'], true)
            || ($tone !== null && $result['face_count'] !== 1)) {
            throw new RuntimeException('Invalid skin tone result.');
        }
        $undertone = $result['undertone'] ?? null;
        if (! in_array($undertone, [null, 'Warm', 'Cool', 'Neutral'], true)
            || ($undertone !== null && ($result['face_count'] !== 1 || $tone === null))) {
            throw new RuntimeException('Invalid undertone result.');
        }
        $shape = $result['face_shape'] ?? null;
        if (! in_array($shape, [null, 'Oval', 'Round', 'Square', 'Heart', 'Oblong', 'Diamond', 'Triangle', 'Rectangle'], true)
            || ($shape !== null && $result['face_count'] !== 1)) {
            throw new RuntimeException('Invalid face shape response.');
        }
        $details = $result['face_shape_details'] ?? null;
        if ($details !== null) {
            if (! is_array($details) || ! is_array($details['outline'] ?? null) || count($details['outline']) !== 36) {
                throw new RuntimeException('Invalid face outline.');
            }
            foreach ($details['outline'] as $point) {
                if (! is_array($point) || count($point) !== 2) {
                    throw new RuntimeException('Invalid face point.');
                }
                foreach ($point as $coordinate) {
                    if (! is_numeric($coordinate) || ! is_finite((float) $coordinate) || $coordinate < 0 || $coordinate > 1) {
                        throw new RuntimeException('Invalid face coordinate.');
                    }
                }
            }
            $clean = ['outline' => $details['outline']];
            if (($details['method'] ?? null) === 'mesh-ratios-v2' && is_array($details['ratios'] ?? null)) {
                $ratios = [];
                foreach (['length','jaw','upper'] as $key) {
                    $value = $details['ratios'][$key] ?? null;
                    if (!is_numeric($value) || !is_finite((float)$value) || $value <= 0 || $value > 3) {
                        throw new RuntimeException('Invalid face proportions.');
                    }
                    $ratios[$key] = (float)$value;
                }
                $clean['method'] = 'mesh-ratios-v2';
                $clean['ratios'] = $ratios;
            }
            if (isset($details['candidates'])) {
                $candidates = $details['candidates'];
                if (!is_array($candidates) || !array_is_list($candidates) || count($candidates) !== 2
                    || count(array_unique($candidates, SORT_REGULAR)) !== 2
                    || $shape !== null || $result['face_count'] !== 1
                    || ($result['face_shape_reason'] ?? null) !== 'shape_uncertain'
                    || !isset($clean['ratios'])) {
                    throw new RuntimeException('Invalid shape alternatives.');
                }
                foreach ($candidates as $candidate) {
                    if (!is_string($candidate) || !in_array($candidate, ['Oval','Round','Square','Heart','Oblong','Diamond','Triangle','Rectangle'], true)) {
                        throw new RuntimeException('Invalid shape alternative.');
                    }
                }
                $clean['candidates'] = $candidates;
            }
            $details = $clean;
        }
        $result['face_shape'] = $shape;
        $result['face_shape_details'] = $details;
        $quality = [];
        foreach (['skin_tone', 'undertone', 'face_shape'] as $attribute) {
            $reason = $result[$attribute.'_reason'] ?? null;
            if ($reason !== null && (! is_string($reason) || ! array_key_exists($reason, config('analysis_quality')))) {
                throw new RuntimeException('Invalid analysis quality response.');
            }
            if ($reason !== null && ($result[$attribute] ?? null) !== null) {
                throw new RuntimeException('Contradictory analysis quality response.');
            }
            $quality[$attribute] = $reason;
        }
        $result['analysis_quality'] = $quality;
        $result['undertone'] = $undertone;
        $result['skin_tone'] = $tone;
        return $result;
    }
}
