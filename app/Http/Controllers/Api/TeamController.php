<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ApiHelper;
use App\Models\TeamUser;
use App\User;
use Exception;
use Illuminate\Support\Facades\DB;

class TeamController extends Controller
{
    public function createTeam(Request $request){

        DB::beginTransaction();

        try{

        $validator = Validator::make($request->all(), [ 
            'team_title' => 'required', 
            'user_id' => 'required', 
            'team_tagline' => 'required',
            'team_description'=>'required',
            'role_title'=>'required',
        ]);

        if ($validator->fails()) { 
            $response=ApiHelper::createAPIResponse(true,-1,$validator->errors(),null);
            return response()->json($response, 200);            
        }

        $teamData=$request->all();
        if ($request->hasFile('team_icon')) {
            $image = $request->file('team_icon');
            $name = time().'.'.$image->getClientOriginalExtension();
            $destinationPath = public_path('/images');
            $image->move($destinationPath, $name);

            $path=url('').'/images/'.$name;
            $teamData['team_icon']=$path;
        }

        $team=Team::create($teamData);

        $teamuser=[];
        $teamuser['user_id']=$request->user_id;
        $teamuser['team_id']=$team->id;
        $teamuser['role_title']=$request->role_title;
        $teamuser['role']='ADMIN';

        TeamUser::create($teamuser);

        if($request->tags){
            $team->tags()->attach($request->tags);
        }

        DB::commit();
        
        $response=ApiHelper::createAPIResponse(false,1,"Team created successfully",null);
        return response()->json($response, 200); 
    }catch(Exception $e){
        DB::rollBack();
        $response=ApiHelper::createAPIResponse(false,-2,"",$e);
        return response()->json($response, 200); 
    }

    }

    public function getMyTeams($userId){
        $teams=DB::table('teams')->leftjoin('team_user','teams.id','=','team_user.team_id')
                        ->select('teams.id','teams.team_title','teams.team_tagline','teams.team_icon','team_user.role','team_user.role_title')
                        ->where('team_user.user_id','=',$userId)
                        ->orderBy('teams.updated_at','DESC')
                        ->get();

        $response=ApiHelper::createAPIResponse(false,1,"Team created successfully",$teams);
        return response()->json($response, 200); 
    }

    public function getMyCreatedTeams($userId){
        $teams=DB::table('teams')
                    ->where('user_id',$userId)
                    ->select('team_title','id','team_icon')
                    ->orderBy('teams.updated_at','DESC')
                    ->get();
    
        $response=ApiHelper::createAPIResponse(false,1,"",$teams);
        return response()->json($response, 200); 
    }

    public function followTeam(Request $request){
        $teamuser=[];
        $teamuser['user_id']=$request->user_id;
        $teamuser['team_id']=$request->team_id;
        $teamuser['role_title']='FOLLOWER';
        $teamuser['role']='FOLLOWER';

        $follower=TeamUser::where('team_id','=',$request->team_id)
                            ->where('user_id','=',$request->user_id)
                            ->first();

        if(!$follower){
            $follower=TeamUser::create($teamuser);
        }

        $response=ApiHelper::createAPIResponse(false,1,"You are following the team",null);
        return response()->json($response, 200); 
    }

    public function setRole(Request $request){
        $teamuser=[];
        $teamuser['user_id']=$request->user_id;
        $teamuser['team_id']=$request->team_id;
        $teamuser['role_title']=$request->role_title;
        $teamuser['role']=$request->role;

        $member=TeamUser::where('team_id','=',$request->team_id)
                        ->where('user_id','=',$request->user_id)
                        ->first();
        if(!$member){
            $member=TeamUser::create($teamuser);
        }

        $response=ApiHelper::createAPIResponse(false,1,"You are now member of the team",null);
        return response()->json($response, 200);
    }

    public function getTeamDetails($teamId,$userId){
        $team=Team::where('teams.id',$teamId)->withCount(['followers'=>function($query){
            $query->where('role','FOLLOWER');
        },'members'=>function($query){
            $query->where('role','MEMBER');
        }])->leftJoin('team_user','team_user.team_id','teams.id')
            ->addSelect(DB::raw("IF(team_user.user_id=".$userId.",team_user.role,'false')as my_team"))
            ->first();

        $response=ApiHelper::createAPIResponse(false,1,"You are now member of the team",$team);
        return response()->json($response, 200);

    }

    public function getTeamAdmins($teamId){
        $admins=TeamUser::leftjoin('users','users.id','=','team_user.user_id')
                            ->leftjoin('colleges','colleges.id','users.college_id')
                            ->where('team_user.team_id','=',$teamId)
                            ->where('team_user.role','=','ADMIN')
                            ->select('users.name','users.user_image','team_user.role','users.id','team_user.role_title','colleges.college_name')
                            ->orderBy('team_user.updated_at','DESC')
                            ->get();

        $response=ApiHelper::createAPIResponse(false,1,"",$admins);
        return response()->json($response, 200);

    }

    public function getTeamMembers($teamId){
        $members=TeamUser::leftjoin('users','users.id','=','team_user.user_id')
                            ->leftjoin('colleges','colleges.id','users.college_id')
                            ->where('team_user.team_id','=',$teamId)
                            ->where('team_user.role','=','MEMBER')
                            ->select('users.name','users.user_image','team_user.role','users.id','team_user.role_title','colleges.college_name')
                            ->orderBy('team_user.updated_at','DESC')
                            ->get();

        $response=ApiHelper::createAPIResponse(false,1,"",$members);
        return response()->json($response, 200);
    }

    public function getTeamFollowers($teamId){
        $followers=TeamUser::leftjoin('users','users.id','=','team_user.user_id')
                            ->leftjoin('colleges','colleges.id','users.college_id')
                            ->where('team_user.team_id','=',$teamId)
                            ->where('team_user.role','=','FOLLOWER')
                            ->select('users.name','users.user_image','team_user.role','users.id','team_user.role_title','colleges.college_name')
                            ->orderBy('team_user.updated_at','DESC')
                            ->get();

        $response=ApiHelper::createAPIResponse(false,1,"",$followers);
        return response()->json($response, 200);
    }
}
