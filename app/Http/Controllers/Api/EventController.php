<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiHelper;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\ConversationUser;
use App\Models\Event;
use App\Models\EventParticipant;
use App\Models\Post;
use App\Models\TeamUser;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class EventController extends Controller
{
    public function createEvent(Request $request){

        DB::beginTransaction();

        try{

        $validator = Validator::make($request->all(), [ 
            'user_id' => 'required', 
            'team_id'=>'required',
            'event_title' => 'required', 
            'event_details' => 'required',
            'event_time'=>'required',
            'event_privacy'=>'required',
            'event_deadline'=>'required',
        ]);

        if ($validator->fails()) { 
            $response=ApiHelper::createAPIResponse(true,-1,$validator->errors(),null);
            return response()->json($response, 200);            
        }

        $conversations=[];
        $event=[];
        $event['event_details']=$request->event_details;
        $event['user_id']=$request->user_id;
        $event['team_id']=$request->team_id;
        $event['event_organiser']=$request->event_organiser;
        $event['event_time']=$request->event_time;
        $event['event_title']=$request->event_title;
        // $event['event_image']="";
        $event['event_privacy']=$request->event_privacy;
        $event['active']=$request->active;
        $event['event_deadline']=$request->event_deadline;

        $conversation['conv_title']=$event['event_title'];
        $conversation['conv_desc']=$event['event_organiser'];
        $conversation['conv_type']="GROUP";

        if($request->event_image){
            $image = $request->file('event_image');
            $name = time().'.'.$image->getClientOriginalExtension();
            $destinationPath = public_path('/EventsImages');
            $image->move($destinationPath, $name);

            $path=url('').'/EventsImages/'.$name;
            $event['event_image']=$path;
            $conversation['conv_icon']=$path;

            $createConv=Conversation::create($conversation);
            $event['conversation_id']=$createConv['id'];

            $eventCreated=Event::create($event);
        }else{
            $createConv=Conversation::create($conversation);
            $event['conversation_id']=$createConv['id'];

            $eventCreated=Event::create($event);
        }

        $convUser=[];
        $convUser['user_id']=$request->user_id;
        $convUser['conversation_id']=$createConv['id'];
        $convUser['role']="ADMIN";

        ConversationUser::create($convUser);

        // $eventpart=[];
        // $eventpart['user_id']=$request->user_id;
        // $eventpart['event_id']=$eventCreated->id;

        // $eventParticipantCreated=EventParticipant::create($eventpart);

        $post=[];
        $post['user_id']=$request->user_id;
        $post['post_type']='TEAMEVENT';
        $post['event_id']=$eventCreated->id;
        $post['team_id']=$request->team_id;
        $post['post_image']=$eventCreated->event_image;
        $post['post_content']=$request->event_details;
        // $post['post_category']=$request->post_category;

        $postCreated=Post::create($post);

        DB::commit();

        $response=ApiHelper::createAPIResponse(false,1,"Event created successfully",null);
        return response()->json($response, 200);
    }catch(Exception $e){
        DB::rollBack();

        $response=ApiHelper::createAPIResponse(false,-2,"",null);
        return response()->json($response, 200);
    }
        
    }

    public function joinEvent(Request $request){

        $eventpart=[];
        $eventpart['user_id']=$request->user_id;
        $eventpart['event_id']=$request->event_id;

        $conversationId=Event::find($request->event_id)->pluck('conversation_id');

        $eventParticipantCreated=EventParticipant::where('event_id',$request->event_id)
                                            ->where('user_id',$request->user_id)
                                            ->first();

        if(!$eventParticipantCreated){
            $eventParticipantCreated=EventParticipant::create($eventpart);
            $convUser=[];
            $convUser['user_id']=$request->user_id;
            $convUser['conversation_id']=$conversationId;
            $convUser['role']="USER";
    
            ConversationUser::create($convUser);
        }

        $response=ApiHelper::createAPIResponse(false,1,"Event created successfully",null);
        return response()->json($response, 200);
    }

    public function getEventsForMe($userId){
        $now=new Carbon();

        $myTeams=TeamUser::where('user_id',$userId)->pluck('team_id');

        $events=Event::whereIn('team_id',$myTeams)
                        ->join('teams','teams.id','events.team_id')
                        ->orWhere('event_privacy','PUBLIC')
                        ->where('event_deadline','<',$now)
                        ->select('teams.team_title','events.event_title','events.event_image','events.event_time','events.id','events.team_id')
                        ->orderBy('events.event_deadline','ASC')
                        ->get();

        $response=ApiHelper::createAPIResponse(false,1,"",$events);
        return response()->json($response, 200);
    }

    public function checkParticipantsList($eventId,$teamId){
        $participants=EventParticipant::leftjoin('users','users.id','event_participants.user_id')
                                        ->leftjoin('colleges','colleges.id','users.college_id')
                                        ->where('event_id',$eventId)
                                        ->leftjoin('team_user',function($joins)use ($teamId){
                                            $joins->on('team_user.user_id','=','event_participants.user_id')
                                                    ->where('team_user.team_id',$teamId);
                                        })
                                        ->select('users.name','users.id','users.user_image','colleges.college_name')
                                        ->orderBy('event_participants.updated_at','DESC')
                                        ->addSelect(DB::raw("IF(team_user.team_id=".$teamId.",team_user.role,'false')as role"))
                                        ->addSelect(DB::raw("IF(team_user.team_id=".$teamId.",team_user.role_title,'false')as role_title"))
                                        ->get();
        
        $response=ApiHelper::createAPIResponse(false,1,"",$participants);
        return response()->json($response, 200);
    }

    public function getEventDetails($eventId,$userId){
        $event=Event::where('events.id',$eventId)
                        ->select('events.id','event_details','events.team_id','events.user_id','event_organiser','event_time','event_title','event_image','event_deadline','events.updated_at')
                        ->withCount('participants')
                        ->leftJoin('team_user',function($joins)use ($userId){
                            $joins->on('team_user.team_id','=','events.team_id')
                                    ->where('team_user.user_id',$userId);
                        })
                        ->addSelect(DB::raw("IF(team_user.user_id=".$userId.",team_user.role,'false')as role"))
                        ->leftjoin('event_participants',function($joins) use($userId){
                            $joins->on('event_participants.event_id','=','events.id')
                                    ->where('event_participants.user_id',$userId);
                        })
                        ->addSelect(DB::raw("IF(event_participants.user_id=".$userId.",'true','false')as my_event"))
                        ->with('team:id,team_title,team_icon,team_tagline')->first();

        $response=ApiHelper::createAPIResponse(false,1,"",$event);
        return response()->json($response, 200);
    }

}
