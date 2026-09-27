"""Local landmarks plus unvalidated, explainable geometric shape heuristics."""
from pathlib import Path
from threading import Lock
import numpy as np

MODEL = Path(__file__).parent / 'models' / 'face_landmarker.task'
OUTLINE = [10,338,297,332,284,251,389,356,454,323,361,288,397,365,379,378,400,377,152,148,176,149,150,136,172,58,132,93,234,127,162,21,54,103,67,109]
_landmarker = None
_lock = Lock()

# Ratios refer specifically to MediaPipe's visible face mesh, not hairline/head
# measurements. The upper width is 54--284, not the cheek-level 127--356.
# These prototypes are engineering heuristics, not a trained shape classifier.
PROTOTYPES = {
    'Oval': (1.18, .79, .875), 'Round': (1.06, .82, .88),
    'Square': (1.10, .88, .90), 'Heart': (1.14, .72, .90),
    'Oblong': (1.32, .79, .88), 'Diamond': (1.18, .77, .80),
    'Triangle': (1.15, .88, .79), 'Rectangle': (1.30, .88, .90),
}

def shape_scores(length, jaw, upper):
    features = np.array([length, jaw, upper], dtype=float)
    if not np.isfinite(features).all():
        return []
    return sorted((float(np.linalg.norm((features-values)/[.07,.05,.045])), label)
                  for label, values in PROTOTYPES.items())


def shape_candidates(length, jaw, upper):
    scores = shape_scores(length, jaw, upper)
    if len(scores) >= 2 and scores[1][0] <= 2.0 and scores[1][0]-scores[0][0] < .25:
        return [scores[0][1], scores[1][1]]
    return []


def classify_ratios(length, jaw, upper):
    features = np.array([length, jaw, upper], dtype=float)
    if not np.isfinite(features).all():
        return None
    scores = sorted((float(np.linalg.norm((features-values)/[.07,.05,.045])), label)
                    for label, values in PROTOTYPES.items())
    if scores[0][0] > 2.0 or scores[1][0]-scores[0][0] < .25:
        return None
    return scores[0][1]


def geometry(points, width, height):
    result = {'face_shape': None, 'face_shape_reason': None, 'face_shape_details': None}
    xy = np.array([[p.x*width,p.y*height] for p in points], dtype=float)
    if len(xy) < 455 or not np.isfinite(xy).all():
        return dict(result, face_shape_reason='shape_landmarks_unavailable')
    contour = xy[OUTLINE]
    if ((contour[:,0] <= 1) | (contour[:,0] >= width-1) | (contour[:,1] <= 1) | (contour[:,1] >= height-1)).any():
        return dict(result, face_shape_reason='shape_incomplete')
    dist = lambda a,b: float(np.linalg.norm(xy[a]-xy[b]))
    cheek = dist(234,454)
    if cheek < 100:
        return dict(result, face_shape_reason='face_too_small')
    result['face_shape_details'] = {'outline': [[round(float(x/width),5),round(float(y/height),5)] for x,y in contour]}
    # Large roll, asymmetric projection or open mouth makes ratios unreliable.
    roll = abs(float(np.degrees(np.arctan2(xy[263,1]-xy[33,1], xy[263,0]-xy[33,0]))))
    symmetry = abs(dist(1,234)-dist(1,454))/cheek
    if min(roll, abs(180-roll)) > 15 or symmetry > .18:
        return dict(result, face_shape_reason='shape_pose')
    if dist(13,14)/cheek > .08:
        return dict(result, face_shape_reason='shape_expression')
    length, jaw, upper = dist(10,152)/cheek, dist(172,397)/cheek, dist(54,284)/cheek
    if not (.9 <= length <= 2.1 and .45 <= jaw <= 1.1 and .65 <= upper <= 1.2):
        return dict(result, face_shape_reason='shape_uncertain')
    result['face_shape_details']['method'] = 'mesh-ratios-v2'
    result['face_shape_details']['ratios'] = dict(length=round(length,5), jaw=round(jaw,5), upper=round(upper,5))
    result['face_shape'] = classify_ratios(length,jaw,upper)
    if result['face_shape'] is None:
        result['face_shape_reason'] = 'shape_uncertain'
        candidates = shape_candidates(length, jaw, upper)
        if candidates:
            result['face_shape_details']['candidates'] = candidates
    return result

def detect_landmarks(rgb):
    """Shared local model, also a fallback when Haar finds no face."""
    global _landmarker
    import mediapipe as mp
    with _lock:
        if _landmarker is None:
            if not MODEL.is_file():
                raise OSError('Local landmark model is missing')
            options = mp.tasks.vision.FaceLandmarkerOptions(
                base_options=mp.tasks.BaseOptions(model_asset_path=str(MODEL)),
                running_mode=mp.tasks.vision.RunningMode.IMAGE,
                num_faces=3, min_face_detection_confidence=.6,
                min_face_presence_confidence=.6)
            _landmarker = mp.tasks.vision.FaceLandmarker.create_from_options(options)
        return _landmarker.detect(mp.Image(image_format=mp.ImageFormat.SRGB,
            data=np.ascontiguousarray(rgb))).face_landmarks


def landmark_fallback(rgb):
    """No network; return boxes plus landmarks without rerunning inference."""
    try:
        detected = detect_landmarks(rgb)
    except (ImportError, RuntimeError, ValueError, OSError):
        return [], []
    height, width = rgb.shape[:2]
    faces, usable = [], []
    for points in detected:
        xy = np.array([[p.x*width, p.y*height] for p in points])[OUTLINE]
        if not np.isfinite(xy).all():
            continue
        x0,y0 = np.maximum(np.floor(xy.min(axis=0)),0).astype(int)
        x1,y1 = np.minimum(np.ceil(xy.max(axis=0)),[width,height]).astype(int)
        if min(x1-x0,y1-y0) >= 30:
            faces.append((x0,y0,x1-x0,y1-y0)); usable.append(points)
    return faces, usable


def estimate_face_shape(rgb, face_count, landmarks=None):
    empty = {'face_shape': None, 'face_shape_reason': None, 'face_shape_details': None}
    if face_count != 1:
        return dict(empty, face_shape_reason='shape_single_face')
    try:
        detected = detect_landmarks(rgb) if landmarks is None else landmarks
        if len(detected) != 1:
            return dict(empty, face_shape_reason='shape_landmarks_unavailable')
        return geometry(detected[0], rgb.shape[1], rgb.shape[0])
    except (ImportError, RuntimeError, ValueError, OSError):
        return dict(empty, face_shape_reason='shape_service_unavailable')
