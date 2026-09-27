<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\FaceAnalysis;
use App\Services\FaceDetector;
use App\Services\MakeupRecommender;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CustomerAiController extends Controller
{
    public function index()
    {
        return view('customer.ai.index');
    }

    public function upload(Request $request, FaceDetector $detector)
    {
        $request->validate([
            'face_image' => 'required|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        try {
            $result = $detector->detect($request->file('face_image'));
        } catch (\Throwable $e) {
            throw ValidationException::withMessages([
                'face_image' => 'Face detection is unavailable or the image could not be processed. Make sure the local AI service is running and try a clear JPEG/PNG under 20 megapixels.',
            ]);
        }
        if (! $result['face_detected']) {
            throw ValidationException::withMessages([
                'face_image' => 'No face detected. Please upload a clear, front-facing photo in good lighting.',
            ]);
        }

        $path = $request->file('face_image')
            ->store('ai-temp', 'public');

        $analysis = FaceAnalysis::create([
            'customer_id' => Auth::id(),
            'image_path' => $path,
            'face_detected' => $result['face_detected'],
            'face_count' => $result['face_count'],
            'skin_tone' => $result['skin_tone'],
            'undertone' => $result['undertone'],
            'analysis_quality' => $result['analysis_quality'],
            'face_shape' => $result['face_shape'],
            'face_shape_details' => $result['face_shape_details'],
        ]);

        return redirect()
            ->route('customer.ai.show', $analysis->id)
            ->with('success', 'Face detected successfully.');
    }

    public function recommend(Request $request, FaceAnalysis $analysis, MakeupRecommender $recommender)
    {
        abort_unless($analysis->customer_id === Auth::id(), 403);
        $preferences = $request->validate([
            'occasion' => ['required', Rule::in(array_keys(config('makeup.occasions')))],
            'preferred_style' => ['required', Rule::in(array_keys(config('makeup.styles')))],
            'selected_face_shape' => ['nullable', Rule::in($analysis->shapeCandidates())],
        ]);
        if (array_key_exists('selected_face_shape', $preferences)) {
            $details = $analysis->face_shape_details ?? [];
            if ($preferences['selected_face_shape']) $details['user_selected_shape'] = $preferences['selected_face_shape'];
            else unset($details['user_selected_shape']);
            $analysis->face_shape_details = $details;
        }
        unset($preferences['selected_face_shape']);
        $result = $recommender->recommend($analysis, $preferences['occasion'], $preferences['preferred_style']);
        $analysis->update(array_merge($preferences, $result));
        return redirect()->route('customer.ai.show', $analysis)->with('success', 'Makeup recommendation saved.');
    }

    public function show(FaceAnalysis $analysis)
    {
        if ($analysis->customer_id !== Auth::id()) {
            abort(403);
        }

        $guide = app(\App\Services\MakeupShadeGuide::class)->build($analysis);
        return view('customer.ai.show', compact('analysis', 'guide'));
    }
}
