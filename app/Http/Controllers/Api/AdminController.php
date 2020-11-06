<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiHelper;
use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function addTag(Request $request){
        $tag=Tag::create($request->all());

        $response=ApiHelper::createAPIResponse(false,1,"",$tag);
        return response()->json($response, 200);
    }
}
