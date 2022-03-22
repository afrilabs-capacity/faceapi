#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Spyder Editor

This is a temporary script file.
"""

from PIL import Image
import sys
import face_recognition
import json


hash0 = face_recognition.load_image_file(sys.argv[1])
unknown_picture = face_recognition.load_image_file(sys.argv[2])



my_face_encoding = face_recognition.face_encodings(hash0)[0]

# my_face_encoding now contains a universal 'encoding' of my facial features that can be compared to any other picture of a face!

unknown_face_encoding = face_recognition.face_encodings(unknown_picture)[0]

# Now we can see the two face encodings are of the same person with `compare_faces`!

results = face_recognition.face_distance([my_face_encoding], unknown_face_encoding)


print(unknown_face_encoding);

if results[0] < 0.2:
    print(True)
else:
    print(False)
