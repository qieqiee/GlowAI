<p>
    @if ($analysis->face_detected === true)
        Face detected. Faces found: {{ $analysis->face_count }}.
    @else
        This photo has not been checked for faces. Upload it again to run detection.
    @endif

</p>

<h2>Estimated skin tone</h2>
@if ($analysis->skin_tone)
    <p><strong>{{ \App\Support\SkinToneLabel::display($analysis->skin_tone) }}</strong></p>
    <p>This is an estimate of skin lightness in this photo. Lighting, camera settings,
        filters and makeup can change the result. It is not a foundation shade match.</p>
@else
    <p>No skin tone estimate is available for this photo.</p>
    <p>{{ config('analysis_quality.'.($analysis->analysis_quality['skin_tone'] ?? ''),
        'No detailed reason was saved for this older analysis. Upload the photo again to use the updated sampler.') }}</p>
@endif
<h2>Estimated undertone (photo-based)</h2>
@if ($analysis->undertone)
    <p><strong>{{ $analysis->undertone }}</strong></p>
    <p>{{ match ($analysis->undertone) {
        'Warm' => 'The sampled cheek colour appears more yellow or golden.',
        'Cool' => 'The sampled cheek colour appears more pink or rosy.',
        'Neutral' => 'The sampled cheek colour appears balanced between pink and yellow.',
        default => '',
    } }}</p>
    <p>This preliminary colour estimate cannot confirm your natural undertone.
        Lighting, camera white balance, redness and makeup can affect it.
        Confirm with makeup swatches in natural light.</p>
@else
    <p>No undertone estimate is available for this photo.</p>
    <p>{{ config('analysis_quality.'.($analysis->analysis_quality['undertone'] ?? ''),
        'No detailed reason was saved for this older analysis. Upload the photo again to use the updated sampler.') }}</p>
@endif

<details>
    <summary>Skin tone and undertone guide</summary>
    <p>Skin tone describes how light or deep the skin appears: Very Fair, Fair, Light,
        Medium, Tan, Dark or Very Dark.</p>
    <p>Undertone describes its colour direction: Warm (yellow or golden),
        Cool (pink or rosy), or Neutral (a balance). Any depth can have any undertone.</p>
</details>
@if($guide['shape_guide'])<figure class="shape-example">@include('customer.ai.face-illustration', ['mode'=>'outline'])<figcaption>{{ $guide['shape'] }} · Generic shape illustration</figcaption></figure>@endif
<h2>Estimated face shape</h2>
@if ($analysis->face_shape === 'Heart')
<p class="shade-note">Heart includes an inverted-triangle-like outline.</p>
@endif
@if(count($analysis->shapeCandidates()) === 2)
    <p><strong>{{ implode(' or ', $analysis->shapeCandidates()) }}</strong></p>
    <p>Your measured proportions are close to both shapes. These are two possibilities, not a confirmed classification. Choose one under “Choose your look” for placement tips.</p>
    <div class="placement-grid">
    @foreach($analysis->shapeCandidates() as $candidate)
    <figure class="shape-example">
        @include('customer.ai.face-illustration', ['mode'=>'outline', 'guide'=>['shape'=>$candidate, 'shape_guide'=>config('face_makeup.'.$candidate)]])
        <figcaption>{{ $candidate }} · Possible shape</figcaption>
        <p>{{ match($candidate) {
            'Oval'=>'Longer than wide, with a gently rounded jaw.',
            'Oblong'=>'An elongated outline with relatively straight sides.',
            'Heart'=>'A wider upper face narrowing toward the chin.',
            'Round'=>'Similar length and width with a softly rounded jaw.',
            'Square'=>'Similar forehead and jaw widths with angular corners.',
            'Diamond'=>'Wider cheekbones with a narrower forehead and jaw.',
            'Triangle'=>'A wider jaw relative to the upper face.',
            'Rectangle'=>'An elongated outline with angular jaw corners.',
        } }}</p>
    </figure>
    @endforeach
    </div>
    @if($analysis->guideShape())<p><strong>User choice: {{ $analysis->guideShape() }}</strong> · Used for your placement guide; the AI estimate remains uncertain.</p>@endif
@elseif ($analysis->face_shape)
    <p><strong>{{ $analysis->face_shape }}</strong></p>
@else
    <p>{{ config('analysis_quality.'.($analysis->analysis_quality['face_shape'] ?? ''),
        'This photo has not been analysed for face shape yet. Upload it again to try the new feature.') }}</p>
@endif
<p>This is a preliminary estimate from facial proportions. A covered forehead or jaw,
    camera angle and expression can change the result. The model may infer hidden
    parts of the outline.</p>
@if($guide['shape_guide'])
<p>The illustration above represents a general shape, not your measured facial outline.</p>
@endif
