<?php

namespace App\Http\Controllers;

use App\Models\websites;
use App\Models\WebsiteUsers;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Ramsey\Uuid\Uuid;
use Illuminate\Http\Request;

define('ImageIDPath', env('STORAGE_PATH') . '/app/id_image/');
define('UserImagePath', env('STORAGE_PATH') . '/app/user_images/');

class VerificationController extends \App\Http\Controllers\Controller
{
    public function verify(Request  $request): \Illuminate\Http\JsonResponse
    {
        $validator = $this->_validate($request);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->getMessageBag()
            ], 422);
        }

        $website = $this->_checkIfUserExistsAndVerified($request->user_id, $request->website_id);
        $res = $this->checkIfUserImageAlreadyExists($request->user_image, $website);
        if ($res[0] === true) {
            return response()->json(['success' => true, 'message' => 'user already exists', 'unique_id' => $res['1']->unique_id]);
        }
        $first = $this->_base64ToImage($request->photo_image, ImageIDPath . '_' . time() . '.png');
        $second = $this->_base64ToImage($request->user_image, UserImagePath . '_' . time() . '.png');
        $check = $this->_runScript($first, $second);
        if ($check == true) {
            $check = WebsiteUsers::where('unique_id', $request->user_id)->where('websites_id', $website->id)->first();
            if ($check) {
                $check->update([
                    'status' => 'verified',
                    'storage' => $second
                ]);
            } else {
                $check = WebsiteUsers::create([
                    'unique_id' => Uuid::uuid4()->toString(),
                    'websites_id' => $website->id,
                    'status' => 'verified',
                    'storage' => $second
                ]);
            }

            return response()->json(['success' => true, 'message' => 'user verified', 'user' => [
                'unique_id' => $check->unique_id,
                'status'   => $check->status
            ]]);
        }
        return response()->json(['error' => true, 'message' => 'image does not match'], 422);
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
        $command = "/var/www/face/test.py";
        Log::info(exec("python3 ${command} ${first} ${second}"));
        return exec("python3 ${command} ${first} ${second}");
    }


    public function _base64ToImage($dataUri, $filename)
    {
        $dataUri = trim($dataUri);
        $imgstring = str_replace('data:image/jpeg;base64,', '', $dataUri);
        $imgstring = trim(str_replace('data:image/png;base64,', '', $imgstring));
        $imgstring = str_replace(' ', '+', $imgstring);
        $data = base64_decode($imgstring);

        Log::info($filename);

        file_put_contents($filename, $data);
        return $filename;
    }

    private function _validate($request)
    {
        return Validator::make($request->all(), [
            'user_image'    => 'required',
            'photo_image'      => 'required',
            'website_id'      => 'required'
        ]);
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
        $second = $this->_base64ToImage($image, UserImagePath . '_' . time() . '.png');
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
}
