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
                            ->where('unique_id', 'like', '%'.$array[0].'%')
                            ->orWhere('status', 'like', '%'.$array[0].'%')
                            ->orWhereHas('website', function ($q) use ($array) {
                                    $q->where('name', 'like', '%'.$array[0].'%');
                            })->paginate();
            return WebsitesUsersResource::collection($websites);

        }
        $websites = WebsiteUsers::paginate();
        return WebsitesUsersResource::collection($websites);
    }


    public function destroy(Request $request)
    {
        $website_user = WebsiteUsers::where('unique_id', $request->uuid)->first();
        $website_user->delete();
        return response()->json(['success' => true]);
    }


}
