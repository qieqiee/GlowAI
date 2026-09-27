import io
import unittest
from PIL import Image
from app import app


class DetectionTests(unittest.TestCase):
    def setUp(self):
        self.client = app.test_client()

    def test_health(self):
        self.assertEqual(self.client.get('/health').json['status'], 'ok')

    def test_missing(self):
        self.assertEqual(self.client.post('/detect-face').status_code, 400)

    def test_invalid(self):
        response = self.client.post('/detect-face', data={'image': (io.BytesIO(b'bad'), 'face.jpg')})
        self.assertEqual(response.status_code, 422)

    def test_no_face(self):
        image = io.BytesIO()
        Image.new('RGB', (300, 300), 'white').save(image, format='PNG')
        image.seek(0)
        response = self.client.post('/detect-face', data={'image': (image, 'blank.png')})
        self.assertEqual({k:v for k,v in response.json.items() if not k.startswith('face_shape')}, {'face_detected': False, 'face_count': 0, 'skin_tone': None, 'undertone': None, 'skin_tone_reason': 'single_face_required', 'undertone_reason': 'single_face_required'})

    def test_too_large(self):
        response = self.client.post('/detect-face', data={'image': (io.BytesIO(b'x' * (5 * 1024 * 1024 + 1)), 'large.jpg')})
        self.assertEqual(response.status_code, 413)

    def test_text_and_noise_are_not_faces(self):
        import numpy as np
        from PIL import ImageDraw
        text=Image.new('RGB',(500,500),'white')
        ImageDraw.Draw(text).text((40,100),'GLOWAI NO FACE 12345',fill='black')
        noise=Image.fromarray(np.random.default_rng(42).integers(0,256,(500,500,3),dtype=np.uint8))
        for image in [text,noise]:
            data=io.BytesIO();image.save(data,format='PNG');data.seek(0)
            response=self.client.post('/detect-face',data={'image':(data,'nonface.png')})
            self.assertEqual(response.status_code,200)
            self.assertFalse(response.json['face_detected'])
            self.assertEqual(response.json['face_count'],0)

    def test_landmark_fallback_used_when_haar_misses(self):
        from unittest.mock import patch
        data=io.BytesIO();Image.new('RGB',(300,300),(120,80,60)).save(data,format='PNG');data.seek(0)
        with patch('app.detector') as detector, patch('app.landmark_fallback',return_value=([(30,30,200,200)],[])) as fallback:
            detector.detectMultiScale.return_value=[]
            response=self.client.post('/detect-face',data={'image':(data,'test.png')})
        self.assertTrue(response.json['face_detected'])
        self.assertEqual(response.json['face_count'],1)
        fallback.assert_called_once()
