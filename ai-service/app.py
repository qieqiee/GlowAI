import io
import warnings
from threading import Lock

import cv2
import numpy as np
from flask import Flask, jsonify, request
from PIL import Image, ImageOps, UnidentifiedImageError
from werkzeug.exceptions import HTTPException
from skin_tone import estimate_skin_attributes
from face_candidates import confirm_multiple
from face_shape import estimate_face_shape, landmark_fallback

app = Flask(__name__)
app.config['MAX_CONTENT_LENGTH'] = 6 * 1024 * 1024
Image.MAX_IMAGE_PIXELS = 20_000_000
detector = cv2.CascadeClassifier(cv2.data.haarcascades + 'haarcascade_frontalface_default.xml')
if detector.empty():
    raise RuntimeError('Face detector could not be loaded.')
detector_lock = Lock()
secondary_detector = cv2.CascadeClassifier(cv2.data.haarcascades + 'haarcascade_frontalface_alt2.xml')
if secondary_detector.empty():
    raise RuntimeError('Secondary face detector could not be loaded.')

@app.errorhandler(HTTPException)
def http_error(error):
    return jsonify(error=error.description), error.code

@app.get('/health')
def health():
    return jsonify(status='ok', service='glowai-face-detection')

@app.post('/detect-face')
def detect_face():
    upload = request.files.get('image')
    if upload is None:
        return jsonify(error='Image field is required.'), 400
    data = upload.read(5 * 1024 * 1024 + 1)
    if len(data) > 5 * 1024 * 1024:
        return jsonify(error='Image must be at most 5 MB.'), 413
    try:
        with warnings.catch_warnings():
            warnings.simplefilter('error', Image.DecompressionBombWarning)
            with Image.open(io.BytesIO(data)) as source:
                if source.format not in ('JPEG', 'PNG'):
                    return jsonify(error='Use a JPEG or PNG image.'), 422
                if source.width * source.height > Image.MAX_IMAGE_PIXELS:
                    return jsonify(error='Image must be at most 20 megapixels.'), 422
                photo = ImageOps.exif_transpose(source).convert('RGB')
                photo.thumbnail((1280, 1280))
                gray = cv2.cvtColor(np.asarray(photo), cv2.COLOR_RGB2GRAY)
    except (UnidentifiedImageError, OSError, ValueError, Image.DecompressionBombWarning, Image.DecompressionBombError):
        return jsonify(error='Invalid or oversized image. Use a JPEG or PNG under 20 megapixels.'), 422
    with detector_lock:
        faces = detector.detectMultiScale(gray, scaleFactor=1.1, minNeighbors=5, minSize=(30, 30))
        if len(faces) > 1:
            confirmations = secondary_detector.detectMultiScale(
                gray, scaleFactor=1.1, minNeighbors=5, minSize=(30, 30))
            faces = confirm_multiple(faces, confirmations)
    landmarks = None
    if len(faces) == 0:
        faces, landmarks = landmark_fallback(np.asarray(photo))
    count = len(faces)
    return jsonify(face_detected=count > 0, face_count=count,
                   **estimate_skin_attributes(np.asarray(photo), faces),
                   **estimate_face_shape(np.asarray(photo), count, landmarks))

if __name__ == '__main__':
    from waitress import serve
    serve(app, host='127.0.0.1', port=5001, max_request_body_size=6 * 1024 * 1024)
