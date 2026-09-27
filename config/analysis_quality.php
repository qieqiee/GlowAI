<?php

return [
    'shape_single_face' => 'Face shape needs exactly one face in the photo.',
    'shape_landmarks_unavailable' => 'A single complete facial outline could not be located. Keep your whole face visible and face the camera.',
    'shape_incomplete' => 'The facial outline reaches the edge of the photo. Include your whole forehead and chin.',
    'shape_pose' => 'Face the camera straight on with your head level for a more reliable shape estimate.',
    'shape_expression' => 'Use a relaxed expression with your mouth closed for face shape analysis.',
    'shape_uncertain' => 'The measured proportions do not clearly match one shape category. No shape has been assigned.',
    'shape_service_unavailable' => 'The local face shape model is unavailable. Other analysis results are still shown.',

    'single_face_required' => 'Colour analysis needs exactly one detected face.',
    'face_too_small' => 'The detected face is too small to sample. Move closer while keeping the whole face visible.',
    'incomplete_face' => 'The detected face extends outside the image. Keep the whole face in the frame.',
    'exposure_clipped' => 'Too much of the sampled cheek area is overexposed or very dark. Try softer, even lighting.',
    'colour_unavailable' => 'The sampled colours are not suitable for this colour estimator. Use an unfiltered colour photo in neutral lighting.',
    'cheek_variation' => 'The sampled cheek area varies too much in brightness or colour. Shadows or covered areas may affect the sample; try even lighting with both cheeks clear.',
    'uneven_lighting' => 'The two sampled cheeks differ too much in brightness. Face the light so both cheeks are evenly lit.',
    'cheek_colour_mismatch' => 'The two cheek samples suggest different colours. An undertone estimate is not reliable for this photo.',
    'cheek_colour_variation' => 'Colour varies too much within the cheek samples to estimate undertone.',
    'undertone_uncertain' => 'The sampled colour is too close to the boundary between undertone categories. No undertone has been assigned.',
];
