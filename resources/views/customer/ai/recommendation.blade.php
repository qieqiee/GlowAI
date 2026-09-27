<section aria-labelledby="makeup-heading" style="max-width:720px;margin:24px 0">
    <h2 id="makeup-heading">Your makeup recommendation</h2>
    <p>Choose the occasion and the style you want. You can change these and generate a new suggestion using this photo.</p>
    @if ($errors->any())
        <div role="alert">@foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>
    @endif
    <form action="{{ route('customer.ai.recommend', $analysis) }}" method="POST">
        @csrf
        <p><label for="occasion">Occasion</label><br>
            <select id="occasion" name="occasion" required>
                <option value="">Choose an occasion</option>
                @foreach (config('makeup.occasions') as $value => $label)
                    <option value="{{ $value }}" @selected(old('occasion', $analysis->occasion) === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </p>
        <p><label for="preferred_style">Preferred makeup style</label><br>
            <select id="preferred_style" name="preferred_style" required>
                <option value="">Choose a style</option>
                @foreach (config('makeup.styles') as $value => $label)
                    <option value="{{ $value }}" @selected(old('preferred_style', $analysis->preferred_style) === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </p>
        <button type="submit">{{ $analysis->recommendation_details ? 'Update recommendation' : 'Generate recommendation' }}</button>
    </form>
    @if ($analysis->recommendation_details)
        <h3>{{ $analysis->recommended_makeup_look }}</h3>
        <p><strong>Why this suggestion:</strong> {{ $analysis->recommendation_reason }}</p>
        @if (! empty($analysis->recommendation_details['missing_analysis']))
            <p>Not used because no usable estimate is available: {{ implode(', ', $analysis->recommendation_details['missing_analysis']) }}.
                Your occasion and style still guide the suggestion.</p>
        @endif
        <dl>
            @foreach (['base' => 'Base', 'eyes' => 'Eyes', 'blush' => 'Blush', 'lips' => 'Lips', 'placement' => 'Blush placement', 'occasion_tip' => 'Occasion tip'] as $key => $label)
                <dt><strong>{{ $label }}</strong></dt>
                <dd style="margin:4px 0 16px">{{ $analysis->recommendation_details[$key] ?? '' }}</dd>
            @endforeach
        </dl>
        <p>These are optional starting ideas based on your preferences and preliminary photo estimates.
            They are not a confirmed shade match. Your preferences take priority.</p>
    @endif
</section>
