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

            $websites = websites::where('unique_id', 'like', '%' . $array[0] . '%')
                ->orWhere('name', 'like', '%' . $array[0] . '%');
            return WebsitesResource::collection($websites->latest()->paginate());
        }
        $websites = websites::latest()->paginate();
        return WebsitesResource::collection($websites);
    }

    public function create(Request $request)
    {


        $validated = $request->validate([
            'name' => 'required|string|unique:websites'
        ]);

        $create = websites::create([
            'name' => $validated['name'],
            'unique_id' => Uuid::uuid4()->toString()
        ]);

        return response()->json(['success' => true, 'data' => $create]);
    }


    public function destroy(Request $request)
    {
        $website = websites::where('unique_id', $request->uuid)->first();
        $users = $website->websiteUsers;
        if (count($users) > 0) {
            foreach ($users as $user) {
                $this->_deleteImages($user->storage);
                $user->delete();
            }
        }
        $website->delete();
        return response()->json(['success' => true]);
    }

    private function _deleteImages($image)
    {
        try {
            unlink($image);
        } catch (\Exception $e) {
        }
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

    public function validateSiteKey($id): \Illuminate\Http\JsonResponse
    {
        $key = websites::where('unique_id', $id)->first();

        if ($key->exists()) {
            return response()->json(['success' => true, 'data' => $key]);
        }

        return response()->json(['success' => false, 'data' => null]);
    }
}
