<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\websites;
use App\Models\WebsiteUsers;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Storage;

define('ImageIDPath', 'storage/id_image/');
define('UserImagePath', 'storage/user_images/');

class SubscriberController extends Controller
{
    //

    public function verify(Request  $request)
    {

        $website = websites::where('unique_id', $request->website_id)->first();
        if (!$website) {
            return response()->json(['error' => true, 'message' => 'invalid website id'], 409);
        }

        if ($request->verification_type == 'basic') {
            return $this->_processBasicVerification($request->base64, $website);
        }

        if ($request->verification_type == 'database') {
            return $this->_processDatabaseVerification($request->base64, $website);
        }

        if ($request->verification_type == 'database_user') {
            return $this->_processDatabaseVerificationSingleUser($request->base64, $website, $request->user_id);
        }

        if ($request->verification_type == 'compare') {
            return $this->_processDocumentVerification($request->base64_first, $request->base64_second);
        }
    }


    private function _checkIfUserExistsAndVerified(string|null $user_id, string $website_id): \Illuminate\Http\JsonResponse| websites
    {
        $website = websites::where('unique_id', $website_id)->first();
        if (!$website) {
            return response()->json(['error' => true, 'message' => 'invalid website id'], 409);
        }
        if ($user_id) {
            $check = WebsiteUsers::where('unique_id', $user_id)->where('websites_id', $website->id)->first();
            if (!$check) {
                return response()->json(['error' => true, 'message' => 'invalid user id'], 409);
            }
            if ($check->status === 'verified') {
                return response()->json(['success' => true, 'message' => 'user already verified', 'user' => [
                    'unique_id' => $check->id,
                    'status'   => $check->status
                ]]);
            }
        }

        return $website;
    }


    private function _runScript($first, $second)
    {
        $command = "/test.py";
        Log::info(shell_exec("C:\Users\hp\AppData\Local\Programs\Python\Python36\python ${command} ${first} ${second}"));
        return shell_exec("C:\Users\hp\AppData\Local\Programs\Python\Python36\python ${command} ${first} ${second}  ");

        //  2>&1 
    }

    // private function _runScriptCompare($first, $second)
    // {
    //     $command = "/compare.py";
    //     // Log::info(shell_exec("C:\Users\hp\AppData\Local\Programs\Python\Python36\python ${command} ${first} ${second}"));
    //     return shell_exec("C:\Users\hp\AppData\Local\Programs\Python\Python36\python ${command} ${first} ${second}  2>&1");

    //     //  2>&1 
    // }


    public function _base64ToImage($dataUri, $dir, $path)
    {
        $dataUri = trim($dataUri);
        $imgstring = str_replace('data:image/jpeg;base64,', '', $dataUri);
        $imgstring = trim(str_replace('data:image/png;base64,', '', $imgstring));
        $imgstring = str_replace(' ', '+', $imgstring);
        $data = base64_decode($imgstring);
        $filename = '_' . time() . '.png';

        //Log::info($filename);
        // . '_' . time() . '.png'
        // file_put_contents($filename, $data);
        //dd(str_replace('/', '', $filename));
        Storage::disk(str_replace('/', '', $dir))->put($filename, $data);


        return $path . $filename;
    }



    private function _validate($request, $type)
    {
        if ($type == 'basic') {
            return Validator::make($request->all(), [
                'base64'    => 'required',
                'website_id'      => 'required'
            ]);
        }

        if ($type == 'database') {
            return Validator::make($request->all(), [
                'base64'    => 'required',
                'website_id'      => 'required'
            ]);
        }

        if ($type == 'database_user') {
            return Validator::make($request->all(), [
                'base64'    => 'required',
                'website_id'      => 'required',
                'user_id'      => 'required'
            ]);
        }
    }

    private function _deleteImages($image)
    {
        try {
            unlink($image);
        } catch (\Exception $e) {
        }
    }

    public function checkIfUserImageAlreadyExists($image, $website): array
    {
        $second = $this->_base64ToImage($image, 'user_images', UserImagePath);
        $users = WebsiteUsers::where('websites_id', $website->id)->get();
        $check = 0;
        $checkedUser = null;
        if (count($users) > 0) {
            foreach ($users as $usr) {
                $check = $this->_runScript($usr->storage, $second);
                if ($check == "True") {
                    $checkedUser = $usr;
                    $this->_deleteImages($second);
                    break;
                }
            }
        }
        Log::info("whats checked" . $check);
        if ($check == "True") {
            Log::info("check " . $check);
            return [
                true,
                $checkedUser
            ];
        }
        return [
            false
        ];
    }


    public function _processBasicVerification($base64, $website)
    {

        $second = $this->_base64ToImage($base64, 'user_images', UserImagePath);
        $check = $this->_runScript($second, $second);
        if ($check) {
            if (explode("\n", $check)[1] == 'True') {
                $check = WebsiteUsers::create([
                    'unique_id' => Uuid::uuid4()->toString(),
                    'websites_id' => $website->id,
                    'status' => 'verified',
                    'storage' => $second
                ]);
                return response()->json(['success' => true, 'message' => 'The provided image has a face in it', 'data' => $base64], 200);
            }
        } else {
            return response()->json(['success' => false, 'message' => 'The provided image has no face in it', 'data' => null], 200);
        }
    }

    public function _processDatabaseVerification($base64, $website)
    {

        $second = $this->_base64ToImage($base64, 'user_images', UserImagePath);
        if (!$this->_runScript($second, $second)) {
            return response()->json(['success' => false, 'message' => 'The provided image has no face in it', 'data' => null], 200);
        }

        $users = WebsiteUsers::where('websites_id', $website->id)->get();
        $checkMatches = 0;
        $checkedUser = null;

        if (count($users) > 0) {
            foreach ($users as $usr) {
                $check = $this->_runScript($usr->storage, $second);
                if (explode("\n", $check)[1] == 'True') {
                    $checkedUser = $usr;
                    $checkMatches++;
                    //dd($check);
                    //$this->_deleteImages($second);
                    break;
                }
            }

            if ($checkMatches > 0) {
                return response()->json(['success' => true, 'message' => 'The provided image exists on your website', 'data' => null], 200);
            } else {


                $check = WebsiteUsers::create([
                    'unique_id' => Uuid::uuid4()->toString(),
                    'websites_id' => $website->id,
                    'status' => 'verified',
                    'storage' => $second
                ]);

                return response()->json(['success' => true, 'message' => 'The provided image does not exist on your website', 'user_unique_id' => $check->unique_id, 'data' => $base64], 200);
            }
        } else {
            $check = WebsiteUsers::create([
                'unique_id' => Uuid::uuid4()->toString(),
                'websites_id' => $website->id,
                'status' => 'verified',
                'storage' => $second
            ]);
            return response()->json(['success' => false, 'message' => 'The user does not exist on your website', 'user_unique_id' => $check->unique_id, 'data' => $base64], 200);
        }
    }


    public function _processDatabaseVerificationSingleUser($base64, $website, $userId)
    {

        if (!is_null($userId)) {
            $userExists = WebsiteUsers::where('websites_id', $website->id)->where('unique_id', $userId)->first();
            if (!$userExists) {
                return response()->json(['error' => true, 'message' => 'invalid user id'], 409);
            }
        }

        $second = $this->_base64ToImage($base64, 'user_images', UserImagePath);
        if (!$this->_runScript($second, $second)) {
            return response()->json(['success' => false, 'message' => 'The provided image has no face in it', 'data' => null], 200);
        }

        $user = WebsiteUsers::where('websites_id', $website->id)->where('unique_id', $userId)->first();
        $check = $this->_runScript($user->storage, $second);
        if (explode("\n", $check)[1] == 'True') {
            return response()->json(['success' => true, 'message' => 'The provided image exists on your website for user: ' . $userId, 'data' => null], 200);
        } else {
            return response()->json(['success' => false, 'message' => 'The provided image does not exist on your website for user: ' . $userId, 'data' => null], 200);
        }
    }


    public function _processDocumentVerification($base64_first, $base64_second)
    {

        $first = $this->_base64ToImage($base64_first, 'user_images', UserImagePath);
        $second = $this->_base64ToImage($base64_second, 'user_images_compare', UserImagePath);
        $check = $this->_runScript($first, $second);

        if ($check) {
            if (explode("\n", $check)[1] == 'True') {
                return response()->json(['success' => true, 'message' => 'The provided images are a match', 'data' => null], 200);
                // $this->_deleteImages($first);
                // $this->_deleteImages($second);
            }

            return response()->json(['success' => false, 'message' => 'The provided images do not match', 'data' => null], 200);
        } else {
            // $this->_deleteImages($first);
            // $this->_deleteImages($second);
            return response()->json(['success' => false, 'message' => 'The provided images do not match', 'data' => null], 200);
        }
    }
}
