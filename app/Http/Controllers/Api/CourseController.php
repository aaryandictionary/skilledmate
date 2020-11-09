<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiHelper;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseContent;
use App\Models\Tag;
use App\User;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CourseController extends Controller
{
    public function createCourse(Request $request){

        DB::beginTransaction();

        try{

        $validator = Validator::make($request->all(), [ 
            'user_id' => 'required', 
            'course_title' => 'required', 
            'course_details' => 'required',
            'course_duration'=>'required',
            'course_fee'=>'required',
        ]);

        if ($validator->fails()) { 
            $response=ApiHelper::createAPIResponse(true,-1,$validator->errors(),null);
            return response()->json($response, 200);            
        }

        $courseData=$request->all();
        if($request->course_image){
            $image = $request->file('course_image');
            $name = time().'.'.$image->getClientOriginalExtension();
            $destinationPath = public_path('/CourseImages');
            $image->move($destinationPath, $name);

            $path=url('').'/CourseImages/'.$name;
            $courseData['course_image']=$path;
            
            $courses=Course::create($courseData);
        }else{
            $courses=Course::create($courseData);
        }
        $contents=$request->content;

        $tags=$request->tags;

        if($tags){
            $courses->tags()->attach($tags);
        }


        if($contents){
            foreach($contents as $content){
                $content['content_id']=$courses->id;
                $courseContent=CourseContent::create($content);
            }
        }

        DB::commit();
        $response=ApiHelper::createAPIResponse(false,1,"Course created successfully",null);
        return response()->json($response, 200);
    }catch(Exception $e){
        DB::rollBack();
        $response=ApiHelper::createAPIResponse(false,-1,"",null);
        return response()->json($response, 200);
    }

    }

    public function updateCourse(Request $request){
        $validator = Validator::make($request->all(), [ 
            'id'=>'required',
            'user_id' => 'required', 
            'course_title' => 'required', 
            'course_details' => 'required',
            'course_duration'=>'required',
            'course_fee'=>'required',
        ]);

        if ($validator->fails()) { 
            $response=ApiHelper::createAPIResponse(true,-1,$validator->errors(),null);
            return response()->json($response, 200);            
        }

        $course=Course::find($request->id);
        $course->course_title=$request->course_title;
        $course->course_details=$request->course_details;
        $course->course_duration=$request->course_duration;
        $course->course_fee=$request->course_fee;

        if($request->hasFile('course_image')){
            $image = $request->file('course_image');
            $name = time().'.'.$image->getClientOriginalExtension();
            $destinationPath = public_path('/CourseImages');
            $image->move($destinationPath, $name);

            $path=url('').'/CourseImages/'.$name;
            $course->course_image=$path;
        }else{
            if($request->course_image==null){
                $course->course_image=null;
            }
        }

        $course->save();

        if($request->tags){
            $course->tags()->sync($request->tags);
        }

        $response=ApiHelper::createAPIResponse(false,1,"",$course);
        return response()->json($response, 200);

    }

    public function updateCourseContent(Request $request){
        $validator = Validator::make($request->all(), [ 
            'id'=>'required',
            'course_id' => 'required', 
            'content_title' => 'required', 
            'content_details' => 'required',
            'content_time'=>'required',
        ]);

        if ($validator->fails()) { 
            $response=ApiHelper::createAPIResponse(true,-1,$validator->errors(),null);
            return response()->json($response, 200);            
        }

        $courseContent=CourseContent::find($request->id);

        $courseContent->content_title=$request->content_title;
        $courseContent->content_details=$request->content_details;
        $courseContent->content_time=$request->content_time;

        $response=ApiHelper::createAPIResponse(false,1,"",$courseContent);
        return response()->json($response, 200);

    }

    public function createCourseContent(Request $request){
        $validator = Validator::make($request->all(), [ 
            'course_id' => 'required', 
            'content_title' => 'required', 
            'content_details' => 'required',
            'content_time'=>'required',
        ]);

        if ($validator->fails()) { 
            $response=ApiHelper::createAPIResponse(true,-1,$validator->errors(),null);
            return response()->json($response, 200);            
        }

        $content=CourseContent::create($request->all());

        $response=ApiHelper::createAPIResponse(false,1,"Content created successfully",$content);
        return response()->json($response, 200);

    }

    public function getCourseDetails($courseId){
        $courseDetails=Course::join('users','users.id','courses.user_id')
                                ->join('colleges','colleges.id','users.college_id')
                                ->select('users.name','users.user_image','colleges.college_name','courses.id','courses.user_id','courses.course_title','courses.course_details','courses.course_duration','courses.course_fee','courses.course_image','courses.created_at')
                                ->where('courses.id',$courseId)
                                ->first();

        $response=ApiHelper::createAPIResponse(false,1,"",$courseDetails);
        return response()->json($response, 200);
    }

    public function getCourseContent($courseId){
        $courseContent=CourseContent::where('course_id',$courseId)
                                        ->orderBy('course_content.updated_at','DESC')
                                        ->get();

        $response=ApiHelper::createAPIResponse(false,1,"",$courseContent);
        return response()->json($response, 200);
    }

    public function getMyCourses($userId){
        $courses=Course::where('user_id',$userId)
                        ->select('id','course_title','course_details','course_image','course_fee')
                        ->orderBy('courses.updated_at','DESC')
                        ->get();

        $response=ApiHelper::createAPIResponse(false,1,"",$courses);
        return response()->json($response, 200);
    }

    public function getCourseSuggestion($userId){
        $user=User::find($userId);

        if($user){
            $usertags=$user->tags()->pluck('id');

            $courses=Course::whereHas('tags',function($query) use ($usertags){
                $query->whereIn('tags.id',$usertags);
            })->where('courses.user_id','!=',$userId)
                ->select('id','course_title','course_details','course_image','course_fee')
                ->inRandomOrder()
                ->get();
    
            $response=ApiHelper::createAPIResponse(false,1,"",$courses);
            return response()->json($response, 200);
        }
        
    }
}
