# Local face-shape estimator v2

MediaPipe FaceLandmarker extracts local landmarks from face_landmarker.task. The custom geometric classifier is an unvalidated engineering heuristic, not a Google face-shape model.

Features: top10-to-chin152 / cheek234-to454, jaw172-to397 / cheek width, forehead54-to284 / cheek width. Point10 is not a measured hairline. Earlier code used upper127-to356, which is nearly cheek width and cannot distinguish upper-face narrowing well. Ratios and method mesh-ratios-v2 are retained with the outline for inspection; there is no makeup overlay.

Eight prototypes, normalization scales, outlier cutoff and ambiguity margin are explicit in face_shape.py. Categories: Oval, Round, Square, Heart, Oblong, Diamond, Triangle, Rectangle. Heart includes inverted-triangle-like shapes; widow peaks cannot be determined from this mesh. Unknown is null, never a default shape.

Cropped outlines, small faces, asymmetric/tilted poses, open mouth and unavailable models produce reason codes. Hidden contours and pitch are not fully detectable. Prototypes must be calibrated on independent labelled photos before claiming accuracy. See AI_CHECK_2026-09-27.md: the reference sheet still has multiple label disagreements after this fix.

When Haar returns zero faces, the same local landmark model supplies a fallback face box. This improves recall on the tested dark reference crops; it is not a fairness validation. The model result is reused for shape to avoid duplicate inference. Face colour remains independent and can succeed while shape abstains.

Run .venv/Scripts/python.exe -m unittest discover -s tests from ai-service. Restart app.py after changes. Upload again for new analysis; old database estimates are not silently changed.

Official model documentation: https://ai.google.dev/edge/mediapipe/solutions/vision/face_landmarker/python
