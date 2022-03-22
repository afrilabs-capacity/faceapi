<?php

namespace App\Http\Controllers;

use App\Http\Resources\WebsitesResource;
use App\Models\websites;
use App\Models\WebsiteUsers;
use Ramsey\Uuid\Uuid;
use Illuminate\Http\Request;
use App\Http\Resources\WebsitesUsersResource;


class WebsiteUserController extends \App\Http\Controllers\Controller
{


    public function index(Request $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        if ($request->keyword) {
            $array = explode(" ", $request->keyword);

            $websites = WebsiteUsers::with('website')
                ->where('unique_id', 'like', '%' . $array[0] . '%')
                ->orWhere('status', 'like', '%' . $array[0] . '%')
                ->orWhereHas('website', function ($q) use ($array) {
                    $q->where('name', 'like', '%' . $array[0] . '%');
                })->paginate();
            return WebsitesUsersResource::collection($websites);
        }
        $websites = WebsiteUsers::paginate();
        return WebsitesUsersResource::collection($websites);
    }

    public function getWebsiteUsersWebsiteId(Request $request)
    {


        $website = websites::where('unique_id', $request->website_id)->first();
        if (!$website) {
            return response()->json(['error' => true, 'message' => 'invalid website id'], 409);
        }

        $websiteUsers = WebsiteUsers::where('websites_id', $website->id)->get();

        return response()->json(['success' => true, 'data' => $websiteUsers]);
    }

    public function destroyWebsiteUserWebsiteIdUserId(Request $request)
    {

        $website = websites::where('unique_id', $request->website_id)->first();
        if (!$website) {
            return response()->json(['error' => true, 'message' => 'invalid website id'], 409);
        }

        $userExists = WebsiteUsers::where('websites_id', $website->id)->where('unique_id', $request->user_id)->first();
        if (!$userExists) {
            return response()->json(['error' => true, 'message' => 'invalid user id'], 409);
        }

        WebsiteUsers::where('websites_id', $website->id)->where('unique_id', $request->user_id)->delete();


        return response()->json(['success' => true, 'data' => null]);
    }


    public function destroy(Request $request)
    {
        $website_user = WebsiteUsers::where('unique_id', $request->uuid)->first();
        $this->_deleteImages($website_user->storage);
        $website_user->delete();
        return response()->json(['success' => true]);
    }

    private function _deleteImages($image)
    {
        try {
            unlink($image);
        } catch (\Exception $e) {
        }
    }
}
