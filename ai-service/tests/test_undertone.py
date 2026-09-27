import io
import unittest
from unittest.mock import patch
import cv2
import numpy as np
from PIL import Image
from app import app
from skin_tone import estimate_skin_attributes


class UndertoneTests(unittest.TestCase):
    face = [(0, 0, 200, 200)]

    def image(self, lightness=60, a=14, b=22):
        lab = np.full((200, 200, 3), (lightness, a, b), dtype=np.float32)
        return np.rint(cv2.cvtColor(lab, cv2.COLOR_LAB2RGB) * 255).astype(np.uint8)

    def test_seven_depths(self):
        for lightness, depth in ((86, 'Very Fair'), (78, 'Fair'), (70, 'Light'), (60, 'Medium'), (50, 'Tan'), (40, 'Deep'), (25, 'Very Deep')):
            with self.subTest(depth=depth):
                self.assertEqual(estimate_skin_attributes(self.image(lightness, a=8, b=12), self.face)['skin_tone'], depth)

    def test_undertone_independent_of_depth(self):
        for lightness in (35, 60, 78):
            for a, b, expected in ((14, 22, 'Warm'), (22, 14, 'Cool'), (16, 16, 'Neutral')):
                with self.subTest(lightness=lightness, expected=expected):
                    self.assertEqual(estimate_skin_attributes(self.image(lightness, a, b), self.face)['undertone'], expected)

    def test_borderline_is_not_neutral(self):
        result = estimate_skin_attributes(self.image(a=16, b=19.5), self.face)
        self.assertEqual(result['skin_tone'], 'Medium')
        self.assertIsNone(result['undertone'])

    def test_different_cheek_colours_abstain(self):
        rgb = self.image(a=14, b=22)
        rgb[:, 100:] = self.image(a=22, b=14)[:, 100:]
        self.assertIsNone(estimate_skin_attributes(rgb, self.face)['undertone'])

    def test_invalid_colour_and_multiple_faces(self):
        for rgb, faces in ((self.image(), self.face * 2), (self.image(a=0, b=0), self.face), (self.image(a=-10, b=20), self.face)):
            self.assertIsNone(estimate_skin_attributes(rgb, faces)['undertone'])

    def test_endpoint_returns_all_undertones(self):
        for a, b, expected in ((14, 22, 'Warm'), (22, 14, 'Cool'), (16, 16, 'Neutral')):
            data = io.BytesIO()
            Image.fromarray(self.image(a=a, b=b)).save(data, format='PNG')
            data.seek(0)
            with patch('app.detector') as detector:
                detector.detectMultiScale.return_value = self.face
                with app.test_client() as client:
                    response = client.post('/detect-face', data={'image': (data, 'fixture.png')})
                    self.assertEqual(response.status_code, 200)
                    self.assertEqual(response.json['undertone'], expected)

    def test_depth_order_and_boundaries(self):
        from skin_tone import classify_depth
        for threshold, below, above in [(32,'Very Deep','Deep'),(46,'Deep','Tan'),(55,'Tan','Medium'),(66,'Medium','Light'),(75,'Light','Fair'),(82,'Fair','Very Fair')]:
            self.assertEqual(classify_depth(threshold-.01),below)
            self.assertEqual(classify_depth(threshold),above)

    def test_dark_samples_do_not_collapse_to_tan(self):
        for lightness,expected in [(25,'Very Deep'),(38,'Deep'),(50,'Tan')]:
            for a,b in [(8,12),(12,8),(9,9)]:
                self.assertEqual(estimate_skin_attributes(self.image(lightness,a,b),self.face)['skin_tone'],expected)
