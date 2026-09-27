import unittest
import numpy as np
from skin_tone import estimate_skin_attributes

class SamplingTests(unittest.TestCase):
    faces = [(0, 0, 300, 300)]

    def photo(self):
        return np.full((300, 300, 3), (175, 125, 95), dtype=np.uint8)

    def test_local_shadow_outlier_does_not_destroy_whole_cheek(self):
        rgb = self.photo()
        # One of nine cheek tiles is shadowed; eight usable tiles remain.
        rgb[156:172, 60:77] = (60, 40, 25)
        result = estimate_skin_attributes(rgb, self.faces)
        self.assertEqual(result['skin_tone'], 'Medium')
        self.assertIsNone(result['skin_tone_reason'])

    def test_majority_required(self):
        rgb = self.photo()
        # Almost all of one cheek is occluded; do not use a tiny surviving area.
        rgb[156:204, 60:98] = (0, 0, 0)
        result = estimate_skin_attributes(rgb, self.faces)
        self.assertIsNone(result['skin_tone'])
        self.assertEqual(result['skin_tone_reason'], 'exposure_clipped')

    def test_two_cheek_shadow_still_rejected(self):
        rgb = self.photo()
        rgb[:, 150:] = (70, 45, 30)
        self.assertEqual(estimate_skin_attributes(rgb, self.faces)['skin_tone_reason'], 'uneven_lighting')

    def test_small_face_reason(self):
        result = estimate_skin_attributes(self.photo(), [(0, 0, 50, 50)])
        self.assertEqual(result['skin_tone_reason'], 'face_too_small')
