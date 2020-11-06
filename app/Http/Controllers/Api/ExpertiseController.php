<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expertise;
use Illuminate\Http\Request;
use App\Helpers\ApiHelper;


class ExpertiseController extends Controller
{
    public function createExpertise(Request $request){
        $expertise=Expertise::create($request->all());

        $response=ApiHelper::createAPIResponse(false,200,"",$expertise);
        return response()->json($response, 200);
    }

    public function getExpertiseDetails($expertise_id){
        $expertise=Expertise::where('id',$expertise_id)->first();

        $response=ApiHelper::createAPIResponse(false,200,"",$expertise);
        return response()->json($response, 200);
    }

    public function updateExpertise(Request $request){
        $expertise=Expertise::where('id',$request->expertise_id)->first();

        $expertise->update($request->all());

        $response=ApiHelper::createAPIResponse(false,200,"",$expertise);
        return response()->json($response, 200);
    }

    public function deleteExpertise($expertise_id){
        $expertise=Expertise::where('id',$expertise_id)->first();

        $expertise->delete();

        $response=ApiHelper::createAPIResponse(false,200,"",null);
        return response()->json($response, 200);
    }
}
