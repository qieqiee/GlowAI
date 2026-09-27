import io
import unittest
from unittest.mock import patch
import numpy as np
from PIL import Image
from skin_tone import estimate_skin_tone
from app import app

class SkinToneTests(unittest.TestCase):
    face = [(0, 0, 200, 200)]

    def image(self, color):
        return np.full((200, 200, 3), color, dtype=np.uint8)

    def test_synthetic_lightness_bands(self):
        for color, expected in [((230, 190, 160), 'Fair'), ((175, 125, 95), 'Medium'), ((95, 60, 40), 'Very Deep')]:
            with self.subTest(color=color):
                self.assertEqual(estimate_skin_tone(self.image(color), self.face), expected)

    def test_no_or_multiple_faces(self):
        for faces in ([], self.face * 2):
            self.assertIsNone(estimate_skin_tone(self.image((175, 125, 95)), faces))

    def test_small_face(self):
        self.assertIsNone(estimate_skin_tone(self.image((175, 125, 95)), [(0, 0, 50, 50)]))

    def test_clipping_and_grayscale(self):
        for color in ((255, 255, 255), (0, 0, 0), (125, 125, 125)):
            self.assertIsNone(estimate_skin_tone(self.image(color), self.face))

    def test_uneven_lighting(self):
        rgb = self.image((230, 190, 160))
        rgb[:, 100:] = (95, 60, 40)
        self.assertIsNone(estimate_skin_tone(rgb, self.face))

    def test_endpoint_includes_estimate(self):
        data = io.BytesIO()
        Image.fromarray(self.image((175, 125, 95))).save(data, format='PNG')
        data.seek(0)
        # Isolate the colour measurement contract from face detection accuracy.
        with patch('app.detector') as detector:
            detector.detectMultiScale.return_value = self.face
            response = app.test_client().post('/detect-face', data={'image': (data, 'test.png')})
        self.assertEqual({k:v for k,v in response.json.items() if not k.startswith('face_shape')}, {'face_detected': True, 'face_count': 1, 'skin_tone': 'Medium', 'undertone': 'Warm', 'skin_tone_reason': None, 'undertone_reason': None})
