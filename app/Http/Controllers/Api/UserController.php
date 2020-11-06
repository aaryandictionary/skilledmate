<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\ApiHelper;
use App\Models\College;
use App\Models\Tag;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Validator;
use App\User;

class UserController extends Controller
{
    //Register New User
    public function register(Request $request) { 
        $validator = Validator::make($request->all(), [ 
            'name' => 'required', 
            'phone' => 'required|unique:users', 
            'password' => 'required',
            'email'=>'required',
            'college_id'=>'required',
            'tags'=>'required',
            // 'skills'=>'required',
        ]);

        $userCheck=User::where('phone','=',$request->phone)->first();

        if($userCheck){
            // $userCheck['token']=$userCheck->createToken('MyApp')-> accessToken; 
            $response=ApiHelper::createAPIResponse(true,2,"User already exist",null);
            return response()->json($response, 200);  
        }

        if ($validator->fails()) { 
            $response=ApiHelper::createAPIResponse(true,-1,$validator->errors(),null);
            return response()->json($response, 200);            
        }

        $tags=$request->tags;

        

        $input = $request->all(); 
        // $input['skills']=null;
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input); 
        $success=$user;


        if($tags){
            $user->tags()->attach($tags);
        }

        
        $response=ApiHelper::createAPIResponse(false,1,"User created successfully",$success);
        return response()->json($response, 200); 
    }

    //Check User Existance and Login
    public function login(){ 
        if(Auth::attempt(['phone' => request('phone'), 'password' => request('password')])){ 
            $user = Auth::user(); 

            $success=$user;
            // $success['token'] =  $user->createToken('MyApp')-> accessToken; 
            $response=ApiHelper::createAPIResponse(false,1,"Login successful",$success);
            return response()->json($response, 200); 
        } 
        else{ 
            $response=ApiHelper::createAPIResponse(true,-1,"Unauthorised",null);
            return response()->json($response, 200); 
        } 
    }

    //Get User's Detail with UID
    public function getUserDetails($userId){
        $user=User:: where('id','=',$userId)
                    ->select('id','name','email','user_image','college_id')
                    ->with(['college:id,college_name','tags:id,name'])
                    ->first();

        if($user){
            $response=ApiHelper::createAPIResponse(false,200,"",$user);
            return response()->json($response, 200);
        }else{
            $response=ApiHelper::createAPIResponse(false,200,"User doesn't exist",null);
            return response()->json($response, 200);
        }
        
    }

    //Get College List
    public function getCollegeList(){
        $colleges=DB::table('colleges')
                        ->select('id','college_name')
                        ->orderBy('college_name')
                        ->get();

        $response=ApiHelper::createAPIResponse(false,200,"",$colleges);
        return response()->json($response, 200);
    }

    //Get Skills List
    public function getSkillsList(){
        $skills=Tag::where('is_skill',1)
                        ->select('id','name')
                        ->get();

        $response=ApiHelper::createAPIResponse(false,200,"",$skills);
        return response()->json($response, 200);
    }


    public function updateUser(Request $request){
        $validator = Validator::make($request->all(), [ 
            'id'=>'required',
            'name' => 'required', 
            'email'=>'required',
        ]);

        if ($validator->fails()) { 
            $response=ApiHelper::createAPIResponse(true,401,$validator->errors(),null);
            return response()->json($response, 401);            
        }

        $user=User::find($request->id);

        $user->name=$request->name;
        $user->email=$request->email;

        if($request->user_image){
            $image = $request->file('user_image');
            $name = time().'.'.$image->getClientOriginalExtension();
            $destinationPath = public_path('/UsersImages');
            $image->move($destinationPath, $name);

            $path=url('').'/UsersImages/'.$name;
            $user->user_image=$path;
        }

        $user->save();

        $user=User:: where('id','=',$request->id)
                    ->select('id','name','email','user_image','college_id')
                    ->with(['college:id,college_name','skills:skill_id,skill_name,skill_category'])
                    ->first();
        $response=ApiHelper::createAPIResponse(false,200,"User updated successfully",$user);
        return response()->json($response, 200); 
    }
    

    public function getUsersByCollege($collegeId){
        $college=College::find($collegeId);

        // $users=$college->users()->select('name')->get();
        $users=$college->users()->get();

        $response=ApiHelper::createAPIResponse(false,200,"",$users);
        return response()->json($response, 200); 
    }

    public function addSkill(Request $request){
        $user=User::find($request->user_id);

        $tag=$user->tags()->where('id',$request->tag_id)->first();

        if(!$tag){
            $tag=$user->tags()->attach($request->tag_id);
        }

        $response=ApiHelper::createAPIResponse(false,200,"Tag added successfully",null);
        return response()->json($response, 200); 
    }

    public function getUsersSuggestions($userId){
        $usertags=User::find($userId)->tags()->pluck('id');

        $users=User::whereHas('tags',function($query) use ($usertags){
            $query->whereIn('tags.id',$usertags);
        })->inRandomOrder()->get();

        $response=ApiHelper::createAPIResponse(false,1,"",$users);
        return response()->json($response, 200);
    }

}
