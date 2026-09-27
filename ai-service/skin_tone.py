"""Prototype apparent skin lightness, not calibrated skin colour or phototype.

Fixed cheek windows assume a frontal face. They are not skin segmentation.
L* cutoffs are project heuristics, not a validated dermatological scale.
"""
import cv2
import numpy as np


class SampleRejected(ValueError):
    """A stable reason code, suitable for localised UI messages."""


def sample_cheeks(rgb, faces):
    if len(faces) != 1:
        raise SampleRejected('single_face_required')
    x, y, w, h = map(int, faces[0])
    if min(w, h) < 100:
        raise SampleRejected('face_too_small')
    if x < 0 or y < 0 or x+w > rgb.shape[1] or y+h > rgb.shape[0]:
        raise SampleRejected('incomplete_face')
    samples = []
    # Same cheek windows, split into 3x3 tiles. Require a majority consensus;
    # never choose only the brightest tile or relax the original spread limit.
    for left, right in ((.20, .37), (.63, .80)):
        patch = rgb[y+int(h*.52):y+int(h*.68), x+int(w*left):x+int(w*right)]
        pixels = patch.reshape(-1, 3).astype(np.float32) / 255
        usable = (pixels.max(axis=1) < .98) & (pixels.min(axis=1) > .02)
        if usable.mean() < .70:
            raise SampleRejected('exposure_clipped')
        if np.median(np.ptp(pixels[usable], axis=1)) < .025:
            raise SampleRejected('colour_unavailable')
        tiles = []
        for row in np.array_split(patch, 3, axis=0):
            for tile in np.array_split(row, 3, axis=1):
                values = tile.reshape(-1, 3).astype(np.float32) / 255
                valid = (values.max(axis=1) < .98) & (values.min(axis=1) > .02)
                if valid.mean() < .70:
                    continue
                lab = cv2.cvtColor(values[valid].reshape(-1, 1, 3), cv2.COLOR_RGB2LAB).reshape(-1, 3)
                if np.percentile(lab[:, 0], 90) - np.percentile(lab[:, 0], 10) <= 25:
                    tiles.append(lab)
        if len(tiles) < 5:
            raise SampleRejected('cheek_variation')
        centers = np.array([np.median(tile, axis=0) for tile in tiles])
        center = np.median(centers, axis=0)
        keep = (np.abs(centers[:, 0]-center[0]) <= 12) & (np.linalg.norm(centers[:, 1:]-center[1:], axis=1) <= 8)
        selected = [tile for tile, accepted in zip(tiles, keep) if accepted]
        if len(selected) < 5:
            raise SampleRejected('cheek_variation')
        combined = np.concatenate(selected)
        if np.percentile(combined[:, 0], 90)-np.percentile(combined[:, 0], 10) > 25:
            raise SampleRejected('cheek_variation')
        samples.append(combined)
    if abs(float(np.median(samples[0][:, 0]))-float(np.median(samples[1][:, 0]))) > 12:
        raise SampleRejected('uneven_lighting')
    return samples


def classify_depth(lightness):
    # Prototype L* bands, not melanin measurements or sun-response categories.
    for cutoff, label in ((82, 'Very Fair'), (75, 'Fair'), (66, 'Light'), (55, 'Medium'), (46, 'Tan'), (32, 'Deep')):
        if lightness >= cutoff:
            return label
    return 'Very Deep'


def classify_undertone(samples):
    # Red/yellow balance is only an apparent photo-colour cue. It cannot
    # separate actual undertone from camera white balance, redness or makeup.
    cheek_colors = [np.median(sample[:, 1:], axis=0) for sample in samples]
    if np.linalg.norm(cheek_colors[0] - cheek_colors[1]) > 8:
        raise SampleRejected('cheek_colour_mismatch')
    votes = []
    for sample, color in zip(samples, cheek_colors):
        a, b = map(float, color)
        chroma = float(np.hypot(a, b))
        if not (6 <= chroma <= 45) or a <= 0 or b <= 0:
            raise SampleRejected('colour_unavailable')
        if np.percentile(np.linalg.norm(sample[:, 1:] - color, axis=1), 90) > 12:
            raise SampleRejected('cheek_colour_variation')
        balance = b - a
        if balance >= 5:
            votes.append('Warm')
        elif balance <= -5:
            votes.append('Cool')
        elif abs(balance) <= 2:
            votes.append('Neutral')
        else:
            # Borderline colour is unknown, not automatically Neutral.
            raise SampleRejected('undertone_uncertain')
    if votes[0] != votes[1]:
        raise SampleRejected('cheek_colour_mismatch')
    return votes[0]


def estimate_skin_attributes(rgb, faces):
    result = {'skin_tone': None, 'undertone': None,
              'skin_tone_reason': None, 'undertone_reason': None}
    try:
        samples = sample_cheeks(rgb, faces)
    except SampleRejected as error:
        result.update(skin_tone_reason=str(error), undertone_reason=str(error))
        return result
    lightness = float(np.median(np.concatenate(samples)[:, 0]))
    result['skin_tone'] = classify_depth(lightness)
    try:
        result['undertone'] = classify_undertone(samples)
    except SampleRejected as error:
        result['undertone_reason'] = str(error)
    return result


def estimate_skin_tone(rgb, faces):
    """Compatibility wrapper for callers requesting depth only."""
    return estimate_skin_attributes(rgb, faces)['skin_tone']
