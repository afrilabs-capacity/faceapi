#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Spyder Editor

This is a temporary script file.
"""

from PIL import Image
import imagehash
import cv2
import numpy as np
import sys

hash0 = cv2.imread(sys.argv[1])
hash1 = cv2.imread(sys.argv[2])

sift = cv2.xfeatures2d.SIFT_create()
kp_1, desc_1 = sift.detectAndCompute(hash0, None)
kp_2, desc_2 = sift.detectAndCompute(hash1, None)

index_params = dict(algorithm=0, trees=5)
search_params = dict()
flann = cv2.FlannBasedMatcher(index_params, search_params)
matches = flann.knnMatch(desc_1, desc_2, k=2)

good_points = []
ratio = 0.6
for m, n in matches:
    if m.distance < ratio*n.distance:
        good_points.append(m)
        print(len(good_points))
result = cv2.drawMatches(hash0, kp_1, hash1, kp_2, good_points, None)

number_keypoints = 0
if len(kp_1) <= len(kp_2):
    number_keypoints = len(kp_1)
else:
    number_keypoints = len(kp_2)
    
percentage = len(good_points) / number_keypoints * 100
print("GOOD Matches:", len(good_points))
print("How good it's the match: ", percentage, "%")
print(percentage)







