<?php
namespace App\Http\Controllers;

use App\Models\websites;
use App\Models\WebsiteUsers;
use Ramsey\Uuid\Uuid;
use Illuminate\Http\Request;


class WebsiteUserController extends \App\Http\Controllers\Controller
{





    public function destroy(Request $request)
    {
        $website = WebsiteUsers::where('unique_id', $request->unique_id)->first();
        $website->delete();
        return response()->json(['success' => true]);
    }


}
