#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Spyder Editor
This is a temporary script file.
"""

from PIL import Image
import sys
import face_recognition

hash0 = face_recognition.load_image_file(sys.argv[1])
unknown_picture = face_recognition.load_image_file(sys.argv[2])

my_face_encoding = face_recognition.face_encodings(hash0)[0]

# my_face_encoding now contains a universal 'encoding' of my facial features that can be compared to any other picture of a face!

unknown_face_encoding = face_recognition.face_encodings(unknown_picture)[0]

# Now we can see the two face encodings are of the same person with `compare_faces`!

results = face_recognition.compare_faces([my_face_encoding], unknown_face_encoding,tolerance=0.50)
print(results)

if results[0]==True :
    print(True)
else:
    print(False)
