<!DOCTYPE html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Your makeup guide · GlowAI</title><link rel="stylesheet" href="{{ asset('css/makeup-guide.css') }}"></head>
<body><main class="guide-page">
<nav class="no-print"><a href="{{ route('customer.ai.index') }}">← Upload another photo</a><span>GLOWAI / PERSONAL MAKEUP GUIDE</span></nav>
@if(session('success'))<p class="notice" role="status">{{ session('success') }}</p>@endif

@php($guide = $guide ?? app(\App\Services\MakeupShadeGuide::class)->build($analysis))
<section class="photo-analysis" aria-labelledby="photo-analysis-title">
<figure class="customer-photo"><img src="{{ asset('storage/'.$analysis->image_path) }}" alt="Your uploaded photo" width="420" decoding="async"><figcaption>Your uploaded photo · Original image</figcaption></figure>
<div class="analysis-content"><p class="eyebrow">YOUR PHOTO, YOUR STARTING POINT</p><h2 id="photo-analysis-title">Photo analysis details</h2>@include('customer.ai.analysis-details')</div>
</section>
<header class="guide-hero">

    <div class="guide-hero-content">
        <p class="eyebrow">
            A little colour, a look that's yours
        </p>

        <h1>
            Your makeup<br>
            <em>colour story.</em>
        </h1>

        <p>
            A visual starting point, shaped by your photo estimates
            and the look you choose.
        </p>
    </div>

    <div class="guide-mark">
        <img
            src="{{ asset('images/glowai-logo.png') }}"
            alt="GlowAI Logo"
        >
    </div>

</header>

<div class="guide-choices no-print">
<h2>Choose your look</h2>
@if($errors->any())<div role="alert">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
<form action="{{ route('customer.ai.recommend',$analysis) }}" method="POST">@csrf
<label>Occasion<select name="occasion" required><option value="">Choose an occasion</option>@foreach(config('makeup.occasions') as $value=>$label)<option value="{{ $value }}" @selected(old('occasion',$analysis->occasion)===$value)>{{ $label }}</option>@endforeach</select></label>
<label>Makeup style<select name="preferred_style" required><option value="">Choose a style</option>@foreach(config('makeup.styles') as $value=>$label)<option value="{{ $value }}" @selected(old('preferred_style',$analysis->preferred_style)===$value)>{{ $label }}</option>@endforeach</select></label>
@if(count($analysis->shapeCandidates()) === 2)
<label>Face shape for placement (your choice)<select name="selected_face_shape"><option value="">Keep both possibilities / no selection</option>@foreach($analysis->shapeCandidates() as $candidate)<option value="{{ $candidate }}" @selected(old('selected_face_shape',$analysis->face_shape_details['user_selected_shape'] ?? null)===$candidate)>{{ $candidate }}</option>@endforeach</select></label>
@endif
<button type="submit">{{ $analysis->recommendation_details ? 'Update my guide' : 'Create my guide' }}</button>
</form></div>
<section class="look-banner"><span class="eyebrow">YOUR MAKEUP LOOK</span><h2>{{ $analysis->recommended_makeup_look ?? 'Choose your occasion & style' }}</h2>
<p>{{ $analysis->recommendation_details ? 'Your selected look guides the finish and intensity below.' : 'Explore your colour families below, then choose a look to complete your guide.' }}</p></section>

<div class="section-heading"><h2>Your shade palette</h2><span>ILLUSTRATIVE COLOUR FAMILIES</span></div>
<section class="shade-grid">
@foreach($guide['groups'] as $index=>$group)
<article class="shade-card"><div class="card-heading"><span>{{ str_pad($index+1,2,'0',STR_PAD_LEFT) }}</span><h3>{{ $group['title'] }}</h3></div>
<div class="swatch-row">@forelse($group['swatches'] as [$name,$hex])<figure>@if($group['title'] === 'Lip colour')<svg class="lip-swatch" viewBox="0 0 100 66" role="img" aria-label="{{ $name }} lipstick shade"><path d="M5 32 C22 28 31 9 43 19 Q50 26 57 19 C69 9 78 28 95 32 C77 60 23 60 5 32Z" fill="{{ $hex }}"/><path d="M5 32 Q28 29 50 33 Q72 29 95 32" fill="none" stroke="#402f31" stroke-opacity=".4" stroke-width="1.5"/><path d="M28 43 Q47 50 69 43" fill="none" stroke="white" stroke-opacity=".22" stroke-width="3" stroke-linecap="round"/></svg>@else<div class="swatch" style="--shade:{{ $hex }}" role="img" aria-label="{{ $name }} colour sample"></div>@endif<figcaption>{{ $name }}</figcaption></figure>@empty<p class="unavailable">Shade family unavailable</p>@endforelse</div>
<p>{{ $group['note'] }}</p></article>
@endforeach
</section>
<p class="shade-note">Screen colours are illustrations, not measured skin colours or exact foundation matches. Shade names vary by brand; swatch on the jawline in natural light.</p>
<section class="placement-section" aria-labelledby="placement-title"><div class="section-heading"><h2 id="placement-title">Your face-shape placement guide</h2><span>{{ $guide['shape'] ?? 'ESTIMATE UNAVAILABLE' }}</span></div>
@if($guide['shape_guide'])
<p class="placement-intro">Optional techniques for the {{ isset($analysis->face_shape_details['user_selected_shape']) ? 'user-selected' : 'estimated' }} {{ strtolower($guide['shape']) }} shape. Coloured areas show where to start; blend softly at the edges.</p>
<div class="placement-grid">@foreach(['contour'=>'Contouring technique','blush'=>'Blush placement','highlight'=>'Highlighting placement'] as $mode=>$title)
<article class="placement-card"><h3>{{ $title }}</h3>@include('customer.ai.face-illustration', ['mode'=>$mode])<p>{{ $guide['shape_guide'][$mode.'_text'] }}</p></article>
@endforeach</div><p class="shade-note">These are generic face illustrations, not a map of your face. Placement is a style choice; adjust it to your features and preferences.</p>
@else<p>{{ count($analysis->shapeCandidates()) === 2 ? 'Choose one of the two possible shapes under Choose your look to see placement tips.' : 'No face-shape placement guide is available for this photo. Upload one clear, front-facing photo to try again.' }}</p>@endif
</section>
@if($analysis->recommendation_details)
<section class="technique-grid"><article><p class="eyebrow">THE FINISH</p><h3>Build your look</h3><p>{{ $analysis->recommendation_details['base'] ?? '' }}</p><p>{{ $analysis->recommendation_details['eyes'] ?? '' }}</p><p>{{ \App\Support\SkinToneLabel::display($guide['look_colours']['lip_tip'] ?? $analysis->recommendation_details['lips'] ?? '') }}</p></article>
<article><p class="eyebrow">THE PLACEMENT</p><h3>Blush & balance</h3><p>{{ \App\Support\SkinToneLabel::display($guide['look_colours']['blush_tip'] ?? $analysis->recommendation_details['blush'] ?? '') }}</p><p>{{ $analysis->recommendation_details['placement'] ?? '' }}</p></article>
<article><p class="eyebrow">THE OCCASION</p><h3>A finishing thought</h3><p>{{ $analysis->recommendation_details['occasion_tip'] ?? '' }}</p></article></section>
<details class="no-print"><summary>Why this suggestion:</summary><p>{{ \App\Support\SkinToneLabel::display($analysis->recommendation_reason) }}</p>@if(!empty($analysis->recommendation_details['missing_analysis']))<p>Not used because unavailable: {{ implode(', ',$analysis->recommendation_details['missing_analysis']) }}.</p>@endif</details>
@endif

<footer><strong>Made for exploration. Chosen by you.</strong><p>Photo estimates can change with lighting and pose. Use these ideas as a starting point; your preferences come first.</p><span>GLOWAI · MAKEUP GUIDE #{{ $analysis->id }}</span></footer>
</main></body></html>
