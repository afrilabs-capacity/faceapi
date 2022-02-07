<?php
namespace App\Http\Controllers;

use App\Http\Resources\WebsitesResource;
use App\Models\websites;
use App\Models\WebsiteUsers;
use Ramsey\Uuid\Uuid;
use Illuminate\Http\Request;


class WebsiteController extends \App\Http\Controllers\Controller
{


    public function index(Request $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        if ($request->keyword) {
            $array = explode(" ", $request->keyword);

            $websites = websites::where('unique_id', 'like', '%'.$array[0].'%')
                ->orWhere('name', 'like', '%'.$array[0].'%');
            return WebsitesResource::collection($websites->paginate());

        }
        $websites = websites::paginate();
        return WebsitesResource::collection($websites);
    }

    public function create(Request $request)
    {
        $create = websites::create([
            'name' => $request->name,
            'unique_id' => Uuid::uuid4()->toString()
        ]);

        return response()->json(['success' => true]);
    }


    public function destroy(Request $request)
    {
        $website = websites::where('unique_id', $request->uuid)->first();
        $website->delete();
        return response()->json(['success' => true]);
    }

    public function update(Request $request)
    {
        $website = websites::where('unique_id', $request->unique_id)->first();
        $website->update([
            'name' => $request->name,
        ]);
        return response()->json(['success' => true]);
    }

    public function count(Request $request): \Illuminate\Http\JsonResponse
    {
        $website = websites::all()->count();
        $website_users = WebsiteUsers::all()->count();
        return response()->json([
            'websites' => $website,
            'users' => $website_users
        ]);
    }

}
