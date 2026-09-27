import unittest
from face_candidates import confirm_multiple

class CandidateTests(unittest.TestCase):
    def test_window_false_positive(self):
        boxes = [(543, 138, 93, 93), (80, 351, 446, 446)]
        self.assertEqual(confirm_multiple(boxes, [(89, 354, 440, 440)]), [boxes[1]])

    def test_two_supported_faces_remain(self):
        boxes = [(0, 0, 200, 200), (300, 0, 80, 80)]
        self.assertEqual(confirm_multiple(boxes, [(2, 2, 195, 195), (301, 1, 78, 78)]), boxes)

    def test_duplicates_match_only_once(self):
        self.assertEqual(len(confirm_multiple([(0, 0, 100, 100), (1, 1, 100, 100)], [(0, 0, 100, 100)])), 1)

    def test_no_confirmation_does_not_invent_face(self):
        self.assertEqual(confirm_multiple([(0, 0, 100, 100)], []), [])
