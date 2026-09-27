<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class FaceDetectionBase extends Tests\TestCase
{
    use Illuminate\Foundation\Testing\RefreshDatabase;
    protected function migrateFreshUsing()
    {
        // Scope this milestone's test database to its real dependent migrations.
        // Historical project migrations currently add the users.role column twice.
        return ['--path' => [
            'database/migrations/0001_01_01_000000_create_users_table.php',
            'database/migrations/2026_09_08_164941_create_face_analyses_table.php',
            'database/migrations/2026_09_09_000001_add_face_detection_to_face_analyses.php',
            'database/migrations/2026_09_09_000002_add_analysis_quality_to_face_analyses.php',
            'database/migrations/2026_09_09_000003_add_face_shape_details_to_face_analyses.php',
            'database/migrations/2026_09_09_000004_add_makeup_preferences_to_face_analyses.php',
        ]];
    }
}

pest()->extend(FaceDetectionBase::class);

beforeEach(function () {
    Storage::fake('public');
    Http::preventStrayRequests();
    $this->actingAs(User::forceCreate(['full_name' => 'Face Test', 'email' => 'face@example.test', 'password' => bcrypt('password'), 'role' => 'customer']));
});

it('stores a successful face detection', function () {
    Http::fake(['127.0.0.1:5001/*' => Http::response(['face_detected' => true, 'face_count' => 1])]);
    $this->post(route('customer.ai.upload'), ['face_image' => UploadedFile::fake()->image('face.jpg')])->assertSessionHasNoErrors()->assertRedirect();
    $this->assertDatabaseHas('face_analyses', ['face_detected' => true, 'face_count' => 1]);
    $analysis = App\Models\FaceAnalysis::first();
    Storage::disk('public')->assertExists($analysis->image_path);
    $this->get(route('customer.ai.show', $analysis))->assertSee('Face detected. Faces found: 1.');
});

it('rejects a photo without a detected face', function () {
    Http::fake(['*' => Http::response(['face_detected' => false, 'face_count' => 0])]);
    $this->post(route('customer.ai.upload'), ['face_image' => UploadedFile::fake()->image('blank.png')])->assertSessionHasErrors('face_image');
    $this->assertDatabaseCount('face_analyses', 0);
    expect(Storage::disk('public')->allFiles())->toBeEmpty();
});

it('handles an unavailable service', function () {
    Http::fake(['*' => Http::failedConnection()]);
    $this->post(route('customer.ai.upload'), ['face_image' => UploadedFile::fake()->image('face.jpg')])->assertSessionHasErrors('face_image');
    $this->assertDatabaseCount('face_analyses', 0);
});

it('rejects inconsistent results', function () {
    Http::fake(['*' => Http::response(['face_detected' => true, 'face_count' => 0])]);
    $this->post(route('customer.ai.upload'), ['face_image' => UploadedFile::fake()->image('face.jpg')])->assertSessionHasErrors('face_image');
    $this->assertDatabaseCount('face_analyses', 0);
});

it('blocks nonlocal service addresses before sending an image', function () {
    config(['face_detection.url' => 'https://example.com']);
    Http::fake();
    $this->post(route('customer.ai.upload'), ['face_image' => UploadedFile::fake()->image('face.jpg')])->assertSessionHasErrors('face_image');
    Http::assertNothingSent();
});

it('persists and displays an estimated skin tone', function () {
    Http::fake(['*' => Http::response(['face_detected' => true, 'face_count' => 1, 'skin_tone' => 'Medium'])]);
    $this->post(route('customer.ai.upload'), ['face_image' => UploadedFile::fake()->image('face.jpg')])->assertSessionHasNoErrors();
    $analysis = App\Models\FaceAnalysis::firstOrFail();
    expect($analysis->skin_tone)->toBe('Medium');
    $this->get(route('customer.ai.show', $analysis))->assertSee('Estimated skin tone')->assertSee('Medium');
});

it('keeps face detection when tone is unavailable', function () {
    Http::fake(['*' => Http::response(['face_detected' => true, 'face_count' => 2, 'skin_tone' => null])]);
    $this->post(route('customer.ai.upload'), ['face_image' => UploadedFile::fake()->image('group.jpg')])->assertSessionHasNoErrors();
    $analysis = App\Models\FaceAnalysis::firstOrFail();
    expect($analysis->skin_tone)->toBeNull();
    $this->get(route('customer.ai.show', $analysis))->assertSee('No skin tone estimate');
});

it('rejects invalid or ambiguous tone results', function ($count, $tone) {
    Http::fake(['*' => Http::response(['face_detected' => true, 'face_count' => $count, 'skin_tone' => $tone])]);
    $this->post(route('customer.ai.upload'), ['face_image' => UploadedFile::fake()->image('face.jpg')])->assertSessionHasErrors('face_image');
    $this->assertDatabaseCount('face_analyses', 0);
})->with([[1, 'invalid'], [2, 'Medium']]);

it('stores and displays each undertone with the expanded depth categories', function ($tone, $undertone) {
    Http::fake(['*' => Http::response(['face_detected' => true, 'face_count' => 1, 'skin_tone' => $tone, 'undertone' => $undertone])]);
    $this->post(route('customer.ai.upload'), ['face_image' => UploadedFile::fake()->image('face.jpg')])->assertSessionHasNoErrors();
    $analysis = App\Models\FaceAnalysis::firstOrFail();
    expect($analysis->skin_tone)->toBe($tone);
    expect($analysis->undertone)->toBe($undertone);
    $this->get(route('customer.ai.show', $analysis))->assertSee('Estimated undertone (photo-based)')->assertSee($undertone);
})->with([['Fair', 'Warm'], ['Tan', 'Cool'], ['Deep', 'Neutral']]);

it('keeps a tone estimate when undertone is unknown', function () {
    Http::fake(['*' => Http::response(['face_detected' => true, 'face_count' => 1, 'skin_tone' => 'Medium', 'undertone' => null])]);
    $this->post(route('customer.ai.upload'), ['face_image' => UploadedFile::fake()->image('face.jpg')])->assertSessionHasNoErrors();
    $analysis = App\Models\FaceAnalysis::firstOrFail();
    expect($analysis->skin_tone)->toBe('Medium');
    expect($analysis->undertone)->toBeNull();
    $this->get(route('customer.ai.show', $analysis))->assertSee('No undertone estimate');
});

it('rejects malformed undertones', function ($tone, $undertone) {
    Http::fake(['*' => Http::response(['face_detected' => true, 'face_count' => 1, 'skin_tone' => $tone, 'undertone' => $undertone])]);
    $this->post(route('customer.ai.upload'), ['face_image' => UploadedFile::fake()->image('face.jpg')])->assertSessionHasErrors('face_image');
    $this->assertDatabaseCount('face_analyses', 0);
})->with([['Medium', 'invalid'], ['Medium', []], [null, 'Warm']]);

it('persists and shows the actual sampling failure', function () {
    Http::fake(['*' => Http::response(['face_detected' => true, 'face_count' => 1,
        'skin_tone' => null, 'undertone' => null, 'skin_tone_reason' => 'uneven_lighting', 'undertone_reason' => 'uneven_lighting'])]);
    $this->post(route('customer.ai.upload'), ['face_image' => UploadedFile::fake()->image('face.jpg')])->assertSessionHasNoErrors();
    $analysis = App\Models\FaceAnalysis::firstOrFail();
    expect($analysis->analysis_quality['skin_tone'])->toBe('uneven_lighting');
    $this->get(route('customer.ai.show', $analysis))->assertSee('The two sampled cheeks differ too much in brightness');
});

it('rejects invalid quality codes', function () {
    Http::fake(['*' => Http::response(['face_detected' => true, 'face_count' => 1, 'skin_tone_reason' => 'invented'])]);
    $this->post(route('customer.ai.upload'), ['face_image' => UploadedFile::fake()->image('face.jpg')])->assertSessionHasErrors('face_image');
});

it('saves and displays face shape and its outline', function () {
    Http::fake(['*' => Http::response(['face_detected' => true, 'face_count' => 1, 'face_shape' => 'Oval',
        'face_shape_details' => ['outline' => array_fill(0, 36, [0.5, 0.5])]])]);
    $this->post(route('customer.ai.upload'), ['face_image' => UploadedFile::fake()->image('face.jpg')])->assertSessionHasNoErrors();
    $a = App\Models\FaceAnalysis::firstOrFail();
    expect($a->face_shape)->toBe('Oval');
    expect($a->face_shape_details['outline'])->toHaveCount(36);
    $this->get(route('customer.ai.show', $a))->assertSee('Oval')->assertSee('Your makeup');
});
it('preserves colour results if shape model is unavailable', function () {
    Http::fake(['*' => Http::response(['face_detected' => true, 'face_count' => 1, 'skin_tone' => 'Medium',
        'face_shape' => null, 'face_shape_reason' => 'shape_service_unavailable'])]);
    $this->post(route('customer.ai.upload'), ['face_image' => UploadedFile::fake()->image('face.jpg')])->assertSessionHasNoErrors();
    $a = App\Models\FaceAnalysis::firstOrFail();
    expect($a->skin_tone)->toBe('Medium');
    $this->get(route('customer.ai.show', $a))->assertSee('local face shape model is unavailable');
});
it('rejects invalid shapes and outline coordinates', function ($shape, $details) {
    Http::fake(['*' => Http::response(['face_detected' => true, 'face_count' => 1, 'face_shape' => $shape, 'face_shape_details' => $details])]);
    $this->post(route('customer.ai.upload'), ['face_image' => UploadedFile::fake()->image('face.jpg')])->assertSessionHasErrors('face_image');
})->with([['Invalid', null], ['Oval', ['outline' => array_fill(0,36,[1.5,.5])]]]);

function recommendationAnalysis(array $attributes = []) {
    return App\Models\FaceAnalysis::create(array_merge(['customer_id' => auth()->id(), 'image_path' => 'ai-temp/test.jpg',
        'face_detected' => true, 'face_count' => 1, 'skin_tone' => 'Medium', 'undertone' => 'Warm', 'face_shape' => 'Round'], $attributes));
}
it('generates and persists a recommendation without an AI call', function () {
    $a = recommendationAnalysis();
    $this->post(route('customer.ai.recommend', $a), ['occasion' => 'graduation', 'preferred_style' => 'soft_glam'])->assertSessionHasNoErrors()->assertRedirect(route('customer.ai.show', $a));
    $a->refresh();
    expect($a->recommended_makeup_look)->toBe('Soft Glam — Graduation');
    expect($a->recommendation_details['eyes'])->toContain('bronze');
    expect($a->recommendation_details['placement'])->toContain('temples');
    $this->get(route('customer.ai.show', $a))->assertSee('Why this suggestion:')->assertSee('Soft Glam — Graduation');
    Http::assertNothingSent();
});
it('updates preferences on the same record', function () {
    $a = recommendationAnalysis();
    $this->post(route('customer.ai.recommend', $a), ['occasion' => 'work', 'preferred_style' => 'natural']);
    $this->post(route('customer.ai.recommend', $a), ['occasion' => 'evening', 'preferred_style' => 'bold'])->assertSessionHasNoErrors();
    expect($a->fresh()->recommended_makeup_look)->toBe('Bold Glam — Evening / Dinner');
    $this->assertDatabaseCount('face_analyses', 1);
});
it('does not invent missing facial attributes', function () {
    $a = recommendationAnalysis(['skin_tone' => null, 'undertone' => null, 'face_shape' => null]);
    $this->post(route('customer.ai.recommend', $a), ['occasion' => 'everyday', 'preferred_style' => 'natural'])->assertSessionHasNoErrors();
    $a->refresh();
    expect($a->recommendation_details['missing_analysis'])->toHaveCount(3);
    expect($a->recommendation_reason)->toContain('No usable undertone');
    expect($a->undertone)->toBeNull();
});
it('ignores facial attributes for group images', function () {
    $a = recommendationAnalysis(['face_count' => 2]);
    $r = app(App\Services\MakeupRecommender::class)->recommend($a, 'wedding', 'natural');
    expect($r['recommendation_details']['missing_analysis'])->toHaveCount(3);
    expect($r['recommended_makeup_look'])->toStartWith('Natural');
});
it('changes the palette with undertone', function () {
    $a = recommendationAnalysis(['undertone' => 'Cool']);
    $r = app(App\Services\MakeupRecommender::class)->recommend($a, 'work', 'soft_glam');
    expect($r['recommendation_details']['eyes'])->toContain('taupe')->not->toContain('bronze');
});
it('rejects invalid preferences without saving a recommendation', function () {
    $a = recommendationAnalysis();
    $this->post(route('customer.ai.recommend', $a), ['occasion' => 'invalid', 'preferred_style' => ''])->assertSessionHasErrors(['occasion','preferred_style']);
    expect($a->fresh()->recommendation_details)->toBeNull();
});
it('forbids changing another users recommendation', function () {
    $owner = User::forceCreate(['full_name' => 'Other', 'email' => 'other@example.test', 'password' => bcrypt('password')]);
    $a = recommendationAnalysis(['customer_id' => $owner->id]);
    $this->post(route('customer.ai.recommend', $a), ['occasion' => 'work', 'preferred_style' => 'natural'])->assertForbidden();
    expect($a->fresh()->recommendation_details)->toBeNull();
});
it('requires login to generate a recommendation', function () {
    $a = recommendationAnalysis();
    auth()->logout();
    $this->post(route('customer.ai.recommend', $a), ['occasion' => 'work', 'preferred_style' => 'natural'])->assertRedirect(route('login'));
});


it('renders a shade infographic without virtual preview', function () {
    $a=recommendationAnalysis();
    $this->get(route('customer.ai.show',$a))->assertSee('Your shade palette')->assertSee('Golden sand')->assertDontSee('Virtual makeup preview')->assertDontSee('data-eyeshadow');
    $this->post('/customer/ai-makeup/'.$a->id.'/preview')->assertNotFound();
});
it('does not invent foundation families without undertone', function () {
    $a=recommendationAnalysis(['undertone'=>null]);
    $guide=app(App\Services\MakeupShadeGuide::class)->build($a);
    expect($guide['groups'][0]['swatches'])->toBeEmpty();
    expect($guide['personalised'])->toBeFalse();
});

it('puts the original photo and analysis before the look and uses lip illustrations', function () {
    $a = recommendationAnalysis();
    $this->get(route('customer.ai.show', $a))->assertOk()
        ->assertSeeInOrder(['Your uploaded photo', 'Photo analysis details', 'Generic shape illustration', 'Estimated face shape', 'Choose your look'])
        ->assertSee('Contouring technique')->assertSee('Highlighting placement');
});
it('provides distinct placement guides for each supported shape', function () {
    $outlines=[];
    foreach (['Round','Oval','Square','Heart','Oblong','Diamond','Triangle','Rectangle'] as $shape) {
        $a=recommendationAnalysis(['face_shape'=>$shape]);
        $guide=app(App\Services\MakeupShadeGuide::class)->build($a);
        expect($guide['shape'])->toBe($shape);
        expect($guide['shape_guide']['contour_text'])->not->toBeEmpty();
        $outlines[]=$guide['shape_guide']['outline'];
        $this->get(route('customer.ai.show',$a))->assertOk()->assertSee($shape.' face: contour illustration');
    }
    expect(array_unique($outlines))->toHaveCount(8);
});
it('withholds shape illustrations when the estimate is missing or not single face', function () {
    foreach ([['face_shape'=>null], ['face_shape'=>'Unknown'], ['face_count'=>2]] as $attributes) {
        $a=recommendationAnalysis($attributes);
        $this->get(route('customer.ai.show',$a))->assertOk()->assertSee('No face-shape placement guide is available')->assertDontSee('Generic shape illustration');
    }
});

it('selects one blush and lip shade per style and preserves foundation', function () {
    $a=recommendationAnalysis(['skin_tone'=>'Light']);
    $lips=[]; $blushes=[];
    foreach (['natural','soft_glam','bold'] as $style) {
        $this->post(route('customer.ai.recommend',$a),['occasion'=>'work','preferred_style'=>$style])->assertSessionHasNoErrors();
        $a->refresh();
        $g=app(App\Services\MakeupShadeGuide::class)->build($a);
        $groups=collect($g['groups'])->keyBy('title');
        expect($groups['Lip colour']['swatches'])->toHaveCount(1);
        expect($groups['Blush']['swatches'])->toHaveCount(1);
        expect($groups['Foundation family']['swatches'][1][0])->toBe('Golden beige');
        $lips[]=$g['look_colours']['Lip colour'][0]; $blushes[]=$g['look_colours']['Blush'][0];
        expect($a->recommendation_details['lips'])->toContain($g['look_colours']['Lip colour'][0]);
        $this->get(route('customer.ai.show',$a))->assertSee('lip-swatch')->assertSee($g['look_colours']['Lip colour'][0]);
    }
    expect(array_unique($lips))->toHaveCount(3);
    expect(array_unique($blushes))->toHaveCount(3);
});
it('covers supported depths and undertones without inventing missing estimates', function () {
    $service=app(App\Services\MakeupShadeGuide::class);
    foreach (['Very Fair','Fair','Light','Medium','Tan','Deep','Very Deep'] as $tone) foreach (['Warm','Cool','Neutral'] as $under) foreach (['natural','soft_glam','bold'] as $style) {
        $a=recommendationAnalysis(['skin_tone'=>$tone,'undertone'=>$under,'preferred_style'=>$style]);
        expect($service->lookColours($a))->not->toBeNull();
    }
    foreach ([['skin_tone'=>null],['undertone'=>null],['preferred_style'=>null],['face_count'=>2]] as $missing) {
        $a=recommendationAnalysis(array_merge(['preferred_style'=>'natural'],$missing));
        expect($service->lookColours($a))->toBeNull();
    }
    $light=$service->lookColours(recommendationAnalysis(['skin_tone'=>'Light','preferred_style'=>'natural']));
    $deep=$service->lookColours(recommendationAnalysis(['skin_tone'=>'Deep','preferred_style'=>'natural']));
    $cool=$service->lookColours(recommendationAnalysis(['skin_tone'=>'Light','undertone'=>'Cool','preferred_style'=>'natural']));
    expect($light['Lip colour'])->not->toBe($deep['Lip colour'])->not->toBe($cool['Lip colour']);
});

it('offers three distinct foundation depths for every supported tone and undertone', function () {
    foreach (['Very Fair','Fair','Light','Medium','Tan','Deep','Very Deep'] as $tone) foreach (['Warm','Cool','Neutral'] as $undertone) {
        $a=recommendationAnalysis(['skin_tone'=>$tone,'undertone'=>$undertone]);
        $g=app(App\Services\MakeupShadeGuide::class)->build($a);
        expect($g['groups'][0]['swatches'])->toHaveCount(3);
        expect(array_unique(array_column($g['groups'][0]['swatches'],1)))->toHaveCount(3);
        $this->get(route('customer.ai.show',$a))->assertOk()->assertSee(\App\Support\SkinToneLabel::display($tone));
    }
});
it('accepts new AI categories over the Laravel contract', function () {
    $responses = Http::sequence();
    foreach (['Very Fair','Very Deep'] as $tone) foreach (['Diamond','Triangle','Rectangle'] as $shape) {
        $responses->push(['face_detected'=>true,'face_count'=>1,'skin_tone'=>$tone,'undertone'=>'Warm','face_shape'=>$shape]);
    }
    Http::fake(['*'=>$responses]);
    foreach (['Very Fair','Very Deep'] as $tone) foreach (['Diamond','Triangle','Rectangle'] as $shape) {
        $result=app(App\Services\FaceDetector::class)->detect(Illuminate\Http\UploadedFile::fake()->image('face.jpg'));
        expect($result['skin_tone'])->toBe($tone);
        expect($result['face_shape'])->toBe($shape);
    }
});
it('persists two possible shapes and keeps user choice separate from AI', function () {
    $details = ['outline'=>array_fill(0,36,[.5,.5]),'method'=>'mesh-ratios-v2','ratios'=>['length'=>1.25,'jaw'=>.78,'upper'=>.88],'candidates'=>['Oblong','Oval']];
    Http::fake(['*'=>Http::response(['face_detected'=>true,'face_count'=>1,'face_shape'=>null,'face_shape_reason'=>'shape_uncertain','face_shape_details'=>$details])]);
    $this->post(route('customer.ai.upload'),['face_image'=>UploadedFile::fake()->image('face.jpg')])->assertSessionHasNoErrors();
    $a=App\Models\FaceAnalysis::firstOrFail();
    expect($a->shapeCandidates())->toBe(['Oblong','Oval']);
    $this->get(route('customer.ai.show',$a))->assertSee('Oblong or Oval')->assertSee('Possible shape');
    $this->post(route('customer.ai.recommend',$a),['occasion'=>'work','preferred_style'=>'natural','selected_face_shape'=>'Oblong'])->assertSessionHasNoErrors();
    $a->refresh();
    expect($a->face_shape)->toBeNull();
    expect($a->guideShape())->toBe('Oblong');
    expect(app(App\Services\MakeupShadeGuide::class)->build($a)['shape'])->toBe('Oblong');
    $this->get(route('customer.ai.show',$a))->assertSee('User choice: Oblong')->assertSee('user-selected');
    $this->post(route('customer.ai.recommend',$a),['occasion'=>'work','preferred_style'=>'natural','selected_face_shape'=>'Square'])->assertSessionHasErrors('selected_face_shape');
    expect($a->fresh()->guideShape())->toBe('Oblong');
    $this->post(route('customer.ai.recommend',$a),['occasion'=>'work','preferred_style'=>'natural','selected_face_shape'=>''])->assertSessionHasNoErrors();
    expect($a->fresh()->guideShape())->toBeNull();
});
it('rejects alternatives for unusable geometry', function () {
    Http::fake(['*'=>Http::response(['face_detected'=>true,'face_count'=>1,'face_shape'=>null,'face_shape_reason'=>'shape_pose','face_shape_details'=>['outline'=>array_fill(0,36,[.5,.5]),'method'=>'mesh-ratios-v2','ratios'=>['length'=>1.25,'jaw'=>.78,'upper'=>.88],'candidates'=>['Oblong','Oval']]])]);
    $this->post(route('customer.ai.upload'),['face_image'=>UploadedFile::fake()->image('face.jpg')])->assertSessionHasErrors('face_image');
    $this->assertDatabaseCount('face_analyses',0);
});

