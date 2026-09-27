# Makeup recommendation milestone

Upload and AI analysis remain unchanged. On the result page select occasion and
style, then POST /customer/ai-makeup/{analysis}/recommend. Authentication, record
ownership, CSRF and allow-list validation protect updates. Existing uploads work
without re-uploading or another Python call. Choices are required, never inferred.

MakeupRecommender is a deterministic Laravel rules service, not a trained recommender
or a generative AI API. It supplies base, eyes, blush, lips, placement and occasion
tips plus a readable explanation. Style always controls intensity, including a
natural wedding look. Undertone controls optional palette, depth the intensity tip,
shape optional blush placement, occasion the event tip. No foundation shade ID,
skin type, allergy, ethnicity or other unavailable attribute is inferred.

If an estimate is missing, unsupported, or face count is not exactly one, that
attribute is not used and is listed as unavailable. Preferences still work.
Results are suggestions, not prescriptions or proven suitability. Upstream photo
estimates and the editorial rules need expert/user evaluation for FYP claims.

New nullable fields: occasion, preferred_style, recommendation_details JSON.
Existing recommended_makeup_look and recommendation_reason store the saved output.
Changing preferences overwrites the recommendation on the same analysis record.
The JSON includes rules-v1 for traceability. If AI attributes are manually changed
later, regenerate the recommendation rather than treating the stored result as live.

Tests: php artisan test --compact tests/FaceDetectionTest.php
Validation covers save/display, preference updates, missing data, multi-face inputs,
palette changes, invalid input, ownership and authentication. Python is not involved.

Beauty references informing the optional starting ideas (not validation of the rules):
https://www.maybelline.com/makeup-tips/face/how-to-choose-blush-color
https://www.maybelline.co.in/makeup-tips/face-makeup/how-to-apply-blush
https://www.healthline.com/health/beauty-skin-care/skin-undertones
