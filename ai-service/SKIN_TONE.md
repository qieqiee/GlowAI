# Skin tone and undertone prototype

POST /detect-face retains face_detected and face_count, and returns:
skin_tone: Very Fair / Fair / Light / Medium / Tan / Deep / Very Deep / null
undertone: Warm / Cool / Neutral / null
Both are saved in existing nullable Laravel columns; no migration is needed.
Old records retain their original result and need a new upload to be reanalysed.

All processing remains on loopback, using the existing dependencies. Images are
not sent to external APIs. Python does not persist photos. Laravel retains its
existing local public upload disk.

## Method and limitations
Two fixed inner-cheek rectangles from one frontal face are sampled. These are
not skin segmentation and can accidentally include hair, makeup or clothing.
Normalized RGB is converted to CIELAB. Prototype depth bins use median L*:
Very Fair >=82, Fair >=75, Light >=66, Medium >=55, Tan >=46, Deep >=32, Very Deep <32.
These are project heuristics, not a validated skin scale, melanin measurement,
Fitzpatrick phototype, or prediction of burning/tanning response.

Undertone is an experimental visible-colour cue, not a measurement of underlying
natural undertone. For each cheek the median b* minus a* balance gives Warm >=5,
Cool <=-5, Neutral between -2 and 2; gaps abstain. Both cheeks must agree.
Chroma must be 6..45 with positive a* and b*. Cheek median colour distance >8
or within-cheek 90th-percentile colour distance >12 produces no undertone.
All thresholds are unvalidated engineering choices. Reference articles define
beauty terminology; they do not establish or validate this classifier.

Shared gates: exactly one face at least 100px, >=70% non-clipped pixels, colour
image, within-cheek L* spread <=25 and between-cheek L* difference <=12.
Unknown is null, never a default Neutral. Skin tone may be available while
undertone is null. These gates cannot detect uniform colour casts, filters,
redness or makeup. A warm lamp can produce a Warm output on any natural
undertone. No confidence score or accuracy claim is provided. Confirm visually
with makeup swatches in natural light; evaluate on consented, controlled,
labelled photographs before presenting this as a validated FYP classifier.

Olive is not merged into Tan and is not classified in this version.
Fair/Deep categories do not imply sun sensitivity or sun protection.

## Tests and restart
From ai-service: .venv/Scripts/python.exe -m unittest discover -s tests
From Laravel: php artisan test --compact tests/FaceDetectionTest.php
Tests verify synthetic colour behavior, rejection conditions, API contracts and
database/display integration, not accuracy on real people's undertones.
Restart Python with start.ps1 (stop the old process first); no dependency change.

## References
CIELAB conversion: https://docs.opencv.org/4.12.0/de/d25/imgproc_color_conversions.html
Terminology: https://www.healthline.com/health/beauty-skin-care/skin-undertones
Terminology: https://www.skincare.com/expert-advice/ask-the-expert/what-is-my-skin-undertone


## Sampling revision
Each original cheek window is split into nine tiles. At least five must survive
the original exposure and L* spread checks and agree with the median tile colour
(L* difference <=12, a*b* distance <=8). Pool those tiles and retain the original
pooled L* spread <=25 and between-cheek difference <=12 checks. This rejects local
outliers without selecting the brightest patch or raising the old cutoff. It is
still geometric sampling, not face landmarks or validated skin segmentation.
The API returns skin_tone_reason and undertone_reason; Laravel persists codes in
analysis_quality JSON and displays a corresponding message. Unknown reasons from
the service are rejected. Historical records without reasons are labelled as such.

See AI_CHECK_2026-09-27.md for measured before/after results and remaining accuracy limits.
