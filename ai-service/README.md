# GlowAI face detection — milestone 1

## Start
Open PowerShell in C:\laragon\www\glowai:

```powershell
cd ai-service
.\.venv\Scripts\python.exe app.py
```

Keep that terminal running. Start Laravel in another terminal with `php artisan serve`.
Open http://127.0.0.1:8000/customer/ai-makeup and log in.

Health: GET http://127.0.0.1:5001/health
Detection: POST http://127.0.0.1:5001/detect-face, multipart field `image`.
Response: `{"face_detected": true, "face_count": 1}` (false / 0 for no face).
Missing image: 400; unsupported/corrupt/oversized dimensions: 422; over 5 MB: 413.

## Reinstall dependencies
```powershell
C:\laragon\bin\python\python-3.13\python.exe -m venv .venv
.\.venv\Scripts\python.exe -m pip install -r requirements.txt
```

Laravel defaults to http://127.0.0.1:5001; optional FACE_DETECTION_URL must also be a numeric loopback HTTP URL with a port. Redirects and proxies are disabled.
Python decodes images in memory and does not save or send them anywhere. Accepted images use the existing Laravel local public upload disk; rejected images create no analysis record. Existing uploads remain unchanged. Do not expose this development app publicly: the existing photo previews use public storage.

OpenCV's bundled Haar cascade needs no model download at runtime. This is basic frontal face detection, not identity recognition or a guarantee that a face exists. Profiles, poor light or occlusion may fail; patterns may cause false positives. Multiple faces are counted and accepted. Photo-based skin tone and undertone estimates are now available; see SKIN_TONE.md for the method and limitations. Makeup recommendations are not generated.

Tests: `.\.venv\Scripts\python.exe -m unittest discover -s tests`; from Laravel root: `php artisan test --compact tests/FaceDetectionTest.php`.
References: https://flask.palletsprojects.com/en/stable/patterns/fileuploads/ and https://docs.opencv.org/4.x/d1/de5/classcv_1_1CascadeClassifier.html

The milestone tests use an isolated in-memory SQLite database and only the users/face-analysis migrations. The full historical migration chain has a pre-existing duplicate users.role column and is not repaired by this milestone.

Multiple face candidates are now checked with the bundled frontalface_alt2 cascade using one-to-one box matching (IoU >= 0.4). Single detections keep their existing behavior. This reduces the observed car-window false positive but may miss real faces not confirmed by the second cascade. Test group photos before treating multi-face counting as validated.
