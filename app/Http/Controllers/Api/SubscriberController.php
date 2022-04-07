<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\websites;
use App\Models\WebsiteUsers;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Storage;
use Imagick;

define('ImageIDPath', 'storage/id_image/');
define('UserImagePath', 'storage/user_images/');


class SubscriberController extends Controller
{
    public function verify(Request  $request)
    {
        $website = websites::where('unique_id', $request->website_id)->first();
        if (!$website) {
            return response()->json(['success' => false, 'message' => 'invalid website id'], 409);
        }

        if (!is_null($request->pair_activity_with)) {
            return $this->_processDocumentVerification($request->base64_first, $request->base64_second);
        }

        if ($request->activity_type == 'detection') {
            return $this->_processBasicVerification($request->base64, $website);
        }

        if ($request->activity_type == 'database') {
            return $this->_processDatabaseVerification($request->base64, $website);
        }

        if ($request->activity_type == 'database_user') {
            return $this->_processDatabaseVerificationSingleUser($request->base64, $website, $request->remote_user_id);
        }

        if ($request->activity_type == 'compare') {
            return $this->_processDocumentVerification($request->base64_first, $request->base64_second);
        }
    }


    private function _runScript($first, $second)
    {
        $command = "/test_new.py";
        return shell_exec("C:\Users\hp\AppData\Local\Programs\Python\Python36\python ${command} ${first} ${second} 2>&1");

        //  2>&1
    }


    public function _base64ToImage($dataUri, $dir, $path)
    {
        try {
            $dataUri = trim($dataUri);
            $imgstring = request()->pdf ? str_replace('data:application/pdf;base64,', '', $dataUri) : str_replace('data:image/jpeg;base64,', '', $dataUri);
            $imgstring = trim(str_replace('data:image/png;base64,', '', $imgstring));
            $imgstring = str_replace(' ', '+', $imgstring);
            $data = base64_decode($imgstring);
            $filename = '_' . time() . '.png';
            Storage::disk(str_replace('/', '', $dir))->put($filename, $data);
            return $path . $filename;
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'type'=>'image_decode_error', 'message' => 'The provided image has a face in it', 'data' => null], 500);
            //dd($e->getMessage());
        }
    }



    public function _base64ToImagePDF($dataUri, $dir, $path)
    {
        try {
            $dataUri = trim($dataUri);
            $imgstring = request()->pdf ? str_replace('data:application/pdf;base64,', '', $dataUri) : str_replace('data:image/jpeg;base64,', '', $dataUri);
            $imgstring = trim(str_replace('data:image/png;base64,', '', $imgstring));
            $imgstring = str_replace(' ', '+', $imgstring);
            $data = base64_decode($imgstring);
            $imagick = new  Imagick();
            $imagick->readImageBlob($data);
            $imagick->setImageFormat("png");
            $imageBlob = $imagick->getImageBlob();
            $filename = '_' . time() . '.png';
            Storage::disk(str_replace('/', '', $dir))->put($filename, $imageBlob);
            return $path . $filename;
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'type'=>'image_decode_error', 'message' => 'The provided image has a face in it', 'data' => null], 500);
            //dd($e->getMessage());
        }
    }




    private function _deleteImages($image)
    {
        try {
            unlink($image);
        } catch (\Exception $e) {
        }
    }

    

    public function _processBasicVerification($base64, $website)
    {
        $second = $this->_base64ToImage($base64, 'user_images', UserImagePath);
        $check = $this->_runScript($second, $second);
        if ($check) {
            if (explode("\n", $check)[1] == 'True') {
                return response()->json(['success' => true, 'message' => 'The provided image has a face in it', 'data' => null], 200);
            }
        } else {
            return response()->json(['success' => false,'type'=>'no_face', 'message' => 'The provided image has no face in it', 'data' => null], 200);
        }
    }

    public function _processDatabaseVerification($base64, $website, $shouldConvertImage=true, $shouldDetectFace=true)
    {
        if ($shouldConvertImage && $shouldDetectFace) {
            $second = $this->_base64ToImage($base64, 'user_images', UserImagePath);
            $checkValidFace= $this->_runScript($second, $second);
            if (explode("\n", $checkValidFace)[1] !== 'True') {
                $this->_deleteImages($second);
                return response()->json(['success' => false, 'type'=>'no_face', 'message' => 'The provided image has no face in it', 'data' => null], 200);
            }
        } else {
            $second = $base64;
        }
       

        $users = WebsiteUsers::where('websites_id', $website->id)->get();
        $checkMatches = 0;
        $checkedUser = null;

        if (count($users) > 0) {
            foreach ($users as $usr) {
                $check = $this->_runScript($usr->storage, $second);
                $resMatch =  (int) filter_var(explode("\n", $check)[0], FILTER_SANITIZE_NUMBER_INT);
               
                if ((int) substr(ceil($resMatch), 0, 1) < 5) {
                    $checkedUser = $usr;
                    $checkMatches++;
                    //dd($check);
                    if ($shouldConvertImage && $shouldDetectFace) {
                        $this->_deleteImages($second);
                    }
                    break;
                }
            }

            if ($checkMatches > 0) {
                return response()->json(['success' => true, 'message' => 'The provided image exists on your website', 'remote_user_id' => $checkedUser->unique_id, 'data' => null], 200);
            } else {
                $check = WebsiteUsers::create([
                    'unique_id' => Uuid::uuid4()->toString(),
                    'websites_id' => $website->id,
                    'status' => 'verified',
                    'storage' => $second
                ]);

                return response()->json(['success' => true, 'message' => 'The provided image does not exist on your website', 'remote_user_id' => $check->unique_id, 'data' => $base64], 200);
            }
        } else {
            $check = WebsiteUsers::create([
                'unique_id' => Uuid::uuid4()->toString(),
                'websites_id' => $website->id,
                'status' => 'verified',
                'storage' => $second
            ]);
            return response()->json(['success' => true, 'message' => 'The user does not exist on your website', 'remote_user_id' => $check->unique_id, 'data' => $base64], 200);
        }
    }


    public function _processDatabaseVerificationSingleUser($base64, $website, $userId, $shouldConvertImage=true, $shouldDetectFace=true)
    {
        if (!is_null($userId)) {
            $userExists = WebsiteUsers::where('websites_id', $website->id)->where('unique_id', $userId)->first();
            if (!$userExists) {
                return response()->json(['success' => false , 'message' => 'invalid user id','type'=>'unsuccessful'], 409);
            }
        }
        
     
        
        if ($shouldConvertImage && $shouldDetectFace) {
            $second = $this->_base64ToImage($base64, 'user_images', UserImagePath);
            $check = $this->_runScript($second, $second);

            if ($check) {
                if (explode("\n", $check)[1] !== 'True') {
                    return response()->json(['success' => false, 'message' => 'The provided image has no face in it', 'data' => null], 200);
                }
            }
        } else {
            $second=$base64;
        }


        $user = WebsiteUsers::where('websites_id', $website->id)->where('unique_id', $userId)->first();
        $check = $this->_runScript($user->storage, $second);
        $resMatch =  (int) filter_var(explode("\n", $check)[0], FILTER_SANITIZE_NUMBER_INT);
        if ((int) substr(ceil($resMatch), 0, 1) < 5) {
            return response()->json(['success' => true, 'message' => 'The provided image exists on your website for user: ' . $userId, 'data' => null], 200);
        } else {
            return response()->json(['success' => false, 'message' => 'The provided image does not exist on your website for user: ' . $userId,'data' => null], 200);
        }
    }


    public function _processDocumentVerification($base64_first, $base64_second)
    {
        $first = $this->_base64ToImage($base64_first, 'user_images', UserImagePath);

        $checkValidFirst= $this->_runScript($first, $first);

        if (explode("\n", $checkValidFirst)[1] !== 'True') {
            $this->_deleteImages($first);
            return response()->json(['success' => false, 'message' => 'base64_first has no face in it', 'data' => null], 200);
        }

        $second = $this->_base64ToImage($base64_second, 'user_images', UserImagePath);
        $checkValidSecond= $this->_runScript($second, $second);

        if (explode("\n", $checkValidSecond)[1] !== 'True') {
            $this->_deleteImages($second);
            return response()->json(['success' => false, 'message' => 'base64_second has no face in it', 'data' => null], 200);
        }


        $checkCompare= $this->_runScript($first, $second);
        $resMatch =  (int) filter_var(explode("\n", $checkCompare)[0], FILTER_SANITIZE_NUMBER_INT);
    

       
        if ($resMatch < 53296129) {
            if (request()->activity_type=='database') {
                $website = websites::where('unique_id', request()->website_id)->first();
                $databaseCheck = $this->_processDatabaseVerification($second, $website, false, false);
                $databaseCheckDecode =  json_decode($databaseCheck->getContent());
                $Imagetype = pathinfo($first, PATHINFO_EXTENSION);
                return response()->json(['success' => true, 'message' => 'The provided images are a match | '.$databaseCheckDecode->message, 'remote_user_id'=>$databaseCheckDecode->remote_user_id, 'data'=>null], 200);
            } elseif (request()->activity_type=='database_user') {
                $website = websites::where('unique_id', request()->website_id)->first();
                $databaseCheck = $this->_processDatabaseVerificationSingleUser($second, $website, request()->remote_user_id, false, false);
                $databaseCheckDecode =  json_decode($databaseCheck->getContent());
                $Imagetype = pathinfo($first, PATHINFO_EXTENSION);
                return response()->json(['success' => true, 'message' => 'The provided images are a match | '.$databaseCheckDecode->message,
                    'data' =>null], 200);
            }

            return response()->json(['success' => true, 'message' => 'The provided images are a match', 'data' => null], 200);
        }

        return response()->json(['success' => false,  'message' => 'The provided images do not match', 'data' => null], 200);
    }
}