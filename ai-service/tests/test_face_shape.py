import unittest
from unittest.mock import patch
from types import SimpleNamespace
import numpy as np
import face_shape

class ShapeTests(unittest.TestCase):
    def test_prototype_categories(self):
        for label, values in face_shape.PROTOTYPES.items():
            self.assertEqual(face_shape.classify_ratios(*values), label)

    def test_ambiguous_or_outlier_abstains(self):
        self.assertIsNone(face_shape.classify_ratios(1.12,.805,.8775))
        self.assertIsNone(face_shape.classify_ratios(2.5,.4,.5))

    def test_multiple_faces_skip_model(self):
        r=face_shape.estimate_face_shape(np.zeros((200,200,3),dtype=np.uint8),2)
        self.assertEqual(r['face_shape_reason'],'shape_single_face')

    def test_missing_model_does_not_break_upload(self):
        with patch.object(face_shape,'_landmarker',None), patch.object(face_shape,'MODEL') as model:
            model.is_file.return_value=False
            r=face_shape.estimate_face_shape(np.zeros((200,200,3),dtype=np.uint8),1)
        self.assertEqual(r['face_shape_reason'],'shape_service_unavailable')

    def test_cropped_outline_abstains(self):
        points=[SimpleNamespace(x=0,y=0) for _ in range(478)]
        self.assertEqual(face_shape.geometry(points,400,400)['face_shape_reason'],'shape_incomplete')

    def test_realistic_mesh_ratios_do_not_all_collapse_to_round(self):
        self.assertEqual(face_shape.classify_ratios(1.186,.790,.852), 'Oval')
        self.assertEqual(face_shape.classify_ratios(1.166,.787,.819), 'Diamond')
        self.assertNotEqual(face_shape.classify_ratios(1.263,.789,.859), 'Round')

    def test_non_finite_ratios_abstain(self):
        self.assertIsNone(face_shape.classify_ratios(float('nan'),.8,.9))

    def test_geometry_uses_forehead_not_cheek_level_upper_points(self):
        points=[SimpleNamespace(x=.5,y=.5) for _ in range(478)]
        for index,xy in {234:(.25,.45),454:(.75,.45),10:(.5,.2),152:(.5,.79),
            172:(.3025,.65),397:(.6975,.65),54:(.28125,.3),284:(.71875,.3),
            127:(.25,.4),356:(.75,.4),33:(.36,.43),263:(.64,.43),1:(.5,.5)}.items():
            points[index]=SimpleNamespace(x=xy[0],y=xy[1])
        result=face_shape.geometry(points,600,600)
        self.assertEqual(result['face_shape'],'Oval')
        self.assertAlmostEqual(result['face_shape_details']['ratios']['upper'],.875)
