<?php
namespace App\Http\Controllers;

use App\Models\websites;
use App\Models\WebsiteUsers;
use Ramsey\Uuid\Uuid;
use Illuminate\Http\Request;


class UserController extends \App\Http\Controllers\Controller
{




    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        return response()->json(['success' => true, 'user' => auth()->user()]);
    }


}
