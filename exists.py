import face_recognition
import sys
import json
import os
import subprocess
import numpy as np
import codecs


# This program is suppose to be called
# like following
# python3 index.py known_image unknown_image requestOrigin label


program_name    = sys.argv[0]
args            = sys.argv[1:]
given_pic       = args[0]
identify_pic    = args[1]
origin          = args[2]
label           = args[3]



known_image     = face_recognition.load_image_file(given_pic)

unknown_image   = face_recognition.load_image_file(identify_pic)

user_image_face_location = face_recognition.face_locations(known_image)
id_image_face_location = face_recognition.face_locations(unknown_image)


biden_encoding   = face_recognition.face_encodings(known_image, known_face_locations=user_image_face_location)
unknown_encoding = face_recognition.face_encodings(unknown_image,  known_face_locations=id_image_face_location)


encodingFile     = os.path.abspath('/var/www/facerecognition/storage/encodings/'+origin+'.json')
output = {}
output['exists'] = False

if len(biden_encoding) > 0:
    biden_encoding   = biden_encoding[0]
else:
    print(json.dumps({'success' : False, 'message' : 'There was no face in given user image.'}))
    quit()


if len(unknown_encoding) > 0:
    unknown_encoding = unknown_encoding[0]
else:
   print(json.dumps({'success' : False, 'message' : 'There was no face in given user image ID.'}))
   quit()

foundEncoding = ''
def isEncodingAlreadyPresent(encoding, file):

    global encodingFile
    global foundEncoding
    if os.path.isfile(encodingFile) == False:
        return False
    obj_text = codecs.open(encodingFile, 'r', encoding='utf-8').read()

    encodings = json.loads(obj_text)


    for e in encodings:
        response = face_recognition.compare_faces([e['encoding']], encoding)
        if (response[0] == True):
            label = e['label'].split('_')
            output['success'] = False
            output['exists'] = True
            output['user_id'] = label[len(label)-1]
            foundEncoding = e
            return True


isEncodingAlreadyPresent(encoding=biden_encoding, file=encodingFile)


results = face_recognition.compare_faces([biden_encoding], unknown_encoding)

if results[0] == False:
    output['match'] = False;
    print(json.dumps(output))
    quit()

def storeEncodings(encoding, label, origin):
    global encodingFile
    data = {
        'label': label,
        'encoding': encoding.tolist()
    }

    if os.path.isfile(encodingFile) == True:
        append_to_json(data, encodingFile)
    else:
        # Create file
        with open(encodingFile, 'w') as outfile:
            array = []
            array.append(data)
            json.dump(array, outfile)

def append_to_json(_dict,path):
    with open(path, 'ab+') as f:
        f.seek(0,2)                                #Go to the end of file
        if f.tell() == 0 :                         #Check if file is empty
            f.write(json.dumps([_dict]).encode())  #If empty, write an array
        else :
            f.seek(-1,2)
            f.truncate()                           #Remove the last character, open the array
            f.write(' , '.encode())                #Write the separator
            f.write(json.dumps(_dict).encode())    #Dump the dictionary
            f.write(']'.encode())                  #Close the array


if results[0] == True:
    output['success'] = True
    output['match'] = True
    print(json.dumps(output))


if output['exists']:
    pass
else:
    storeEncodings(biden_encoding, label, origin)
