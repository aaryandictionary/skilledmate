<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ApiHelper;
use App\Models\Course;
use App\Models\MySkill;
use App\Models\Post;
use App\Models\PostComment;
use App\Models\PostLike;
use App\Models\Tag;
use App\Models\Team;
use App\User;
use DB;

class PostController extends Controller
{
    public function createPost(Request $request){
        $rules =[
            'user_id'=>'required',
            'post_type'=>'required',
            'event_id'=>'required',
        ];

        $validator=Validator::make($request->all(),$rules);
        if($validator->fails()){
            $response=ApiHelper::createAPIResponse(true,400,$validator->errors(),null);
            return response()->json($response,400);
        }
        $tags=$request->tags;

        if($request->post_image){
            $image = $request->file('post_image');
            $name = time().'.'.$image->getClientOriginalExtension();
            $destinationPath = public_path('/PostImages');
            $image->move($destinationPath, $name);

            $path=url('').'/PostImages/'.$name;
            $postData['user_id']=$request['user_id'];
            // $postData['post_image']=$path;
            $postData['post_content']=$request['post_content'];
            // $postData['post_category']=$request['post_category'];
            $postData['post_image']=$path;
            $postData['event_id']=-1;
            $postData['team_id']=$request['team_id'];
            $postData['post_type']=$request->post_type;
            
            $post=Post::create($postData);

        }else{
            $post=Post::create($request->all());
        }


        if($tags){
            $post->tags()->attach($tags);
        }

        $response=ApiHelper::createAPIResponse(false,200,"Post created successfully",$post);
        return response()->json($response, 200);
    }

    public function getNontags($type,$id){

        if($type=="POST"){
            $post=Post::find($id);
            $postTags=$post->tags()->pluck('id');    
        }else if($type=="TEAM"){
            $team=Team::find($id);
            $postTags=$team->tags()->pluck('id');
        }else if($type=="COURSE"){
            $course=Course::find($id);
            $postTags=$course->tags()->pluck('id');
        }
        
        $tags=Tag::where('is_skill',1)
                ->whereIn('id',$postTags)
                ->select('id','name')
                ->addSelect(DB::raw("'true' as my_tag"));
        $finalTags=Tag::where('is_skill',1)
                    ->whereNotIn('id',$postTags)
                    ->select('id','name')
                    ->addSelect(DB::raw("'false' as my_tag"))
                    ->union($tags)
                    ->get();
        $response=ApiHelper::createAPIResponse(false,200,"",$finalTags);
        return response()->json($response, 200);
    }


    public function updatePost(Request $request){
        $rules =[
            'id'=>'required',
            'user_id'=>'required',
            'post_type'=>'required',
            'event_id'=>'required',
        ];

        $validator=Validator::make($request->all(),$rules);
        if($validator->fails()){
            $response=ApiHelper::createAPIResponse(true,400,$validator->errors(),null);
            return response()->json($response,400);
        }

        $post=Post::find($request->id);
        $post->post_content=$request->post_content;

        if($request->hasFile('post_image')){
            $image = $request->file('post_image');
            $name = time().'.'.$image->getClientOriginalExtension();
            $destinationPath = public_path('/PostImages');
            $image->move($destinationPath, $name);

            $path=url('').'/PostImages/'.$name;
            $post->post_image=$path;
        }else{
                if($request->post_image==null){
                    $post->post_image=null;
                }
        }

        $post->save();

        if($request->tags){
            $post->tags()->sync($request->tags);
        }

        $response=ApiHelper::createAPIResponse(false,1,"",null);
        return response()->json($response, 200);

    }


    public function getUserPosts($userId){
        $user=User::find($userId);

        $posts=$user->posts()->get();

        $response=ApiHelper::createAPIResponse(false,200,"",$posts);
        return response()->json($response, 200);
    }

    public function getAllPosts($user_id){
        $user=User::find($user_id);

        if($user){


        $usertags=$user->tags()->pluck('id');

        $teamevents=$user->teams()
                            ->leftjoin('events','events.team_id','teams.id')
                            ->whereNotNull('events.id')
                            ->pluck('events.id');
                            
        $userTeams=$user->teams()->pluck('teams.id');

        $posts=Post::whereHas('tags',function($query) use ($usertags){
                            $query->whereIn('tags.id',$usertags);
                    })->with(['team','user'=>function($query){$query->join('colleges','colleges.id','college_id')->select('name','user_image','college_name','users.id');}])
                    ->withCount(['likes','comments'])
                    ->leftjoin('post_likes',function($joins) use($user_id){
                        $joins->on('post_likes.post_id','=','id')
                                ->where('post_likes.liker_id',$user_id);
                    })
                    // ->leftjoin('post_comments','post_comments.post_id','posts.id')
                    // ->where('post_likes.liker_id',$user_id)
                    ->addSelect(DB::raw("IF(post_likes.liker_id=".$user_id.",'true','false')as is_liked"))
                    // ->addSelect(DB::raw("IF(post_comments.commenter_id=".$user_id.",post_comments.comment,'')as is_comment"))
                    // ->addSelect('post_likes.liker_id')
                    ->where('post_type','USERPOST')
                    ->orWhereIn('team_id',$userTeams)
                    ->orWhereIn('event_id',$teamevents)
                    ->orderBy('posts.updated_at','DESC')
                    ->get();
        }

        // $completePost=$posts->user()->get();

        $response=ApiHelper::createAPIResponse(false,200,"",$posts);
        return response()->json($response, 200);
    }

    public function getPostDetails($postId){
        $post=Post::find($postId)->with(['team','user'=>function($query){$query->join('colleges','colleges.id','college_id')->select('name','user_image','college_name','users.id');}])
                    ->withCount(['likes','comments'])
                    ->leftjoin('post_likes',function($joins){
                        $joins->on('post_likes.post_id','=','id')
                                ->where('post_likes.liker_id','=','posts.user_id');
                    })
                    // ->leftjoin('post_comments','post_comments.post_id','posts.id')
                    // ->where('post_likes.liker_id',$user_id)
                    ->addSelect(DB::raw("IF(post_likes.liker_id=posts.user_id,'true','false')as is_liked"))
                    // ->addSelect(DB::raw("IF(post_comments.commenter_id=".$user_id.",post_comments.comment,'')as is_comment"))
                    // ->addSelect('post_likes.liker_id')
                    ->first();
        $response=ApiHelper::createAPIResponse(false,200,"",$post);
        return response()->json($response, 200);
    }

    public function createLike(Request $request){
        // $post=Post::find($request->post_id);

        $like=PostLike::where('post_id','=',$request->post_id)
                        ->where('liker_id','=',$request->liker_id)
                        ->first();

        if($like){
            $like->delete();
            $response=ApiHelper::createAPIResponse(false,-1,"",null);
            return response()->json($response, 200);
        }else{
            $like=[];
            $like['liker_id']=$request['liker_id'];
            $like['post_id']=$request['post_id'];
            $like=PostLike::create($like);
    
            $response=ApiHelper::createAPIResponse(false,1,"",$like);
            return response()->json($response, 200);
        }
        
    }

    public function getPostLikes($postId){
        $users=PostLike::join('users','users.id','liker_id')
                            ->join('colleges','colleges.id','users.college_id')
                            ->where('post_id',$postId)
                            ->select('users.name','users.id','users.user_image','colleges.college_name')
                            ->orderBy('post_likes.updated_at','DESC')
                            ->get();
        $response=ApiHelper::createAPIResponse(false,1,"",$users);
        return response()->json($response, 200);
    }

    public function getPostComments($postId){
        $users=PostComment::join('users','users.id','commenter_id')
                            ->join('colleges','colleges.id','users.college_id')
                            ->where('post_id',$postId)
                            ->select('users.name','users.id','users.user_image','colleges.college_name','comment')
                            ->orderBy('post_comments.updated_at','DESC')
                            ->get();
        $response=ApiHelper::createAPIResponse(false,1,"",$users);
        return response()->json($response, 200);
    }

    public function createComment(Request $request){
        $comment=PostComment::where('post_id','=',$request->post_id)
                            ->where('commenter_id','=',$request->commenter_id)
                            ->where('comment','=',$request->comment)
                            ->first();
        if(!$comment){
            $comment=[];
            $comment['post_id']=$request->post_id;
            $comment['commenter_id']=$request->commenter_id;
            $comment['comment']=$request->comment;

            $comment=PostComment::create($comment);
        }

        $user=User::find($request->commenter_id)
                    ->join('colleges','colleges.id','users.college_id')
                    ->select('users.name','users.id','users.user_image','colleges.college_name')
                    ->first();

        $user['comment']=$request->comment;


        $response=ApiHelper::createAPIResponse(false,1,"",$user);
        return response()->json($response, 200);
    }

    public function getTeamPosts($teamId,$userId){
        // $posts=Post::with(['user:id,name,college_id,user_image','images','user.college:id,college_name','likes'=>function($query) use ($teamId){
        //     $query->select('post_id','liker_id')->where('liker_id','=',$teamId)->first();
        // }])->withCount(['likes','comments'])->where('post_type','TEAMPOST')->orWhere('post_type','TEAMEVENT')->get();

        // $completePost=$posts->user()->get();

        $posts=Post::with(['user'=>function($query){$query->join('colleges','colleges.id','college_id')->select('name','user_image','college_name','users.id');}])
    ->withCount(['likes','comments'])
    ->leftjoin('post_likes',function($joins) use($userId){
        $joins->on('post_likes.post_id','=','id')
                ->where('post_likes.liker_id',$userId);
    })
    ->leftJoin('team_user',function($joins)use ($userId){
        $joins->on('team_user.team_id','=','posts.team_id')
                ->where('team_user.user_id',$userId);
    })
    ->addSelect(DB::raw("IF(team_user.user_id=".$userId.",team_user.role_title,'false')as role_title"))
    // ->leftjoin('post_comments','post_comments.post_id','posts.id')
    // ->where('post_likes.liker_id',$user_id)
    ->addSelect(DB::raw("IF(post_likes.liker_id=".$userId.",'true','false')as is_liked"))

    // ->addSelect(DB::raw("IF(post_comments.commenter_id=".$user_id.",post_comments.comment,'')as is_comment"))
    // ->addSelect('post_likes.liker_id')
    ->where('post_type','!=','USERPOST')
    ->where('posts.team_id',$teamId)
    ->orderBy('posts.updated_at','DESC')
    ->get();

        $response=ApiHelper::createAPIResponse(false,200,"",$posts);
        return response()->json($response, 200);
    }
}
