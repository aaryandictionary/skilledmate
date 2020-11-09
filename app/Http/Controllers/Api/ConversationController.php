<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ApiHelper;
use App\Models\Conversation;
use App\Models\ConversationUser;
use App\Models\Message;
use App\User;
use Illuminate\Support\Facades\DB;

class ConversationController extends Controller
{
    public function createConversation(Request $request){
        $rules =[
            'conv_type'=>'required',
            'user_id'=>'required',
            'opponents'=>'required',
        ];

        $validator=Validator::make($request->all(),$rules);
        if($validator->fails()){
            $response=ApiHelper::createAPIResponse(true,400,$validator->errors(),null);
            return response()->json($response,400);
        }

        if($request->conv_icon){
            $image = $request->file('conv_icon');
            $name = time().'.'.$image->getClientOriginalExtension();
            $destinationPath = public_path('/ConImages');
            $image->move($destinationPath, $name);

            $path=url('').'/ConImages/'.$name;
      
            $request['conv_icon']=$path;
            
            $conv=Conversation::create($request->all());
        }else{
            $conv=Conversation::create($request->all());
        }

        $conv->users()->attach($request->opponents);
        $conv->users()->attach($request->user_id,['role'=>'ADMIN']);

        $response=ApiHelper::createAPIResponse(false,200,"",$conv);
        return response()->json($response, 200);
    }

    // public function getmyconv($userId){
    //     $user=User::find($userId);

    //     $singleConv=ConversationUser::where('user_id',$user->id)
    //                             ->join('conversations','conversations.id','conversation_user.conversation_id')
    //                             ->where('conv_type','MONO')
    //                             ->pluck('conversation_id');

    //     $single=Conversation::with(['lastmessage'=>function($query){
    //         $query->select('text_msg','content_type','created_at','conversation_id');
    //     }])
    //     ->whereIn('conversations.id',$singleConv)
    //     ->leftjoin('conversation_user',function($joins)use($userId){
    //         $joins->on('conversation_user.conversation_id','=','conversations.id')
    //             ->where('conversation_user.user_id','!=',$userId);
    //     })
    //     // ->where('conversation_user.user_id','!=',$userId)
    //     ->leftJoin('users','users.id','user_id')
    //     // ->select('conversations.*')
    //     ->select(DB::raw("users.name as conv_title,users.user_image as conv_icon,conversation_user.user_id as user_id,conv_type,conversation_user.conversation_id,conversations.id"))
    //     ->get();

    //     $response=ApiHelper::createAPIResponse(false,200,"",$single);
    //     return response()->json($response, 200);
    // }

    public function getMyConversations($userId){
        $user=User::find($userId);

        $singleConv=ConversationUser::where('user_id',$user->id)
                                ->join('conversations','conversations.id','conversation_user.conversation_id')
                                ->where('conv_type','MONO')
                                ->pluck('conversation_id');

        $single=Conversation::whereIn('conversations.id',$singleConv)
                                ->leftJoin('conversation_user','conversation_user.conversation_id','conversations.id')
                                ->where('conversation_user.user_id','!=',$userId)
                                ->join('users','users.id','conversation_user.user_id')
                                ->with(['lastmessage'=>function($query){
                                    $query->select('text_msg','content_type','created_at','conversation_id');
                                }])
                                ->select(DB::raw("users.name as conv_title, users.user_image as conv_icon,conversation_user.user_id as user_id,conv_type,conversations.id"));
                                // ->addSelect(DB::raw("IF(messages.content_type='TEXT',messages.text_msg,messages.content_type)as last_message,messages.created_at"));

        $groupConv=ConversationUser::where('user_id',$user->id)
                                ->join('conversations','conversations.id','conversation_user.conversation_id')
                                ->where('conv_type','GROUP')
                                ->pluck('conversation_id');
        $groups=Conversation::whereIn('conversations.id',$groupConv)
                                ->leftJoin('conversation_user','conversation_user.conversation_id','conversations.id')
                                ->where('conversation_user.user_id','=',$userId)
                                ->with(['lastmessage'=>function($query){
                                            $query->select('text_msg','content_type','created_at','conversation_id');
                                        }])
                                ->select(DB::raw("conv_title,conv_icon,-1 as user_id,conv_type,conversations.id"))
                                // ->addSelect(DB::raw("IF(messages.content_type='TEXT',messages.text_msg,messages.content_type)as last_message,messages.created_at"))
                                ->union($single)
                                ->get();                                


        $response=ApiHelper::createAPIResponse(false,200,"",$groups);
        return response()->json($response, 200);
    }

    public function getConversationUsers($convId){
        $conversation=Conversation::find($convId);

        if($conversation){
            $users=$conversation->users()->get();
        }

        $response=ApiHelper::createAPIResponse(false,200,"",$users);
        return response()->json($response, 200);
    }


    public function sendMessage(Request $request){
        $rules =[
            'conversation_id'=>'required',
            'sender_id'=>'required',
            'receiver_id'=>'required',
            'content_type'=>'required',
        ];

        $validator=Validator::make($request->all(),$rules);
        if($validator->fails()){
            $response=ApiHelper::createAPIResponse(true,400,$validator->errors(),null);
            return response()->json($response,400);
        }

        $uids=[];
        $uids[0]=$request->sender_id;
        $uids[1]=$request->receiver_id;

        $msgs=$request->all();

        if($request->conversation_id=="-1"){
            $conversation=ConversationUser::whereIn('conversation_user.user_id',$uids)
                                        ->leftJoin('conversations','conversations.id','conversation_user.conversation_id')
                                        ->where('conversations.conv_type','MONO')
                                        ->pluck('conversations.id');
            if(!$conversation){
                $convDetails=[];
                $convDetails['conv_type']='MONO';

                $conversation=Conversation::create($convDetails)->pluck('id');

                $user1=[];
                $user1['user_id']=$request->sender_id;
                $user1['conversation_id']=$conversation;
                $user1['start_at']=0;
                $user1['role']="USER";

                ConversationUser::create($user1);

                $user2=[];
                $user2['user_id']=$request->receiver_id;
                $user2['conversation_id']=$conversation;
                $user2['start_at']=0;
                $user2['role']="USER";
                ConversationUser::create($user2);
                
                $msgs['conversation_id']=$conversation;
            }else{
                $msgs['conversation_id']=$conversation;
            }
        }

        if($request->hasFile('content')){
            $image = $request->file('content');
            $name = time().'.'.$image->getClientOriginalExtension();
            $destinationPath = public_path('/MessageContent');
            $image->move($destinationPath, $name);

            $path=url('').'/MessageContent/'.$name;
      
            $msgs['content']=$path;

        }
        $message=Message::create($msgs);


        $response=ApiHelper::createAPIResponse(false,200,"",$message);
        return response()->json($response, 200);
    }

    public function getChatMessages($convId,$userId,$from,$paginate){
        if($from=="-1"){
        $messages=ConversationUser::where('conversation_user.conversation_id',$convId)
            ->where('conversation_user.user_id',$userId)
            ->join('messages','messages.conversation_id','conversation_user.conversation_id')
            ->join('users','users.id','messages.sender_id')
            ->where('messages.id','>=','conversation_user.start_at')
            ->select(DB::raw("messages.id,messages.sender_id,messages.content_type,messages.content,messages.text_msg,messages.created_at,users.name as sender_name"))
            ->orderBy('messages.id','DESC')
            ->limit($paginate)
            // ->simplePaginate($paginate);
            ->get();
        }else{
        $messages=ConversationUser::where('conversation_user.conversation_id',$convId)
            ->where('conversation_user.user_id',$userId)
            ->join('messages','messages.conversation_id','conversation_user.conversation_id')
            ->join('users','users.id','messages.sender_id')
            ->where('messages.id','>=','conversation_user.start_at')
            ->where('messages.id','<',$from)
            ->select(DB::raw("messages.id,messages.sender_id,messages.content_type,messages.content,messages.text_msg,messages.created_at,users.name as sender_name"))
            ->orderBy('messages.id','DESC')
            ->limit($paginate)
            // ->simplePaginate($paginate);
            ->get();
        }
       
        $response=ApiHelper::createAPIResponse(false,200,"",$messages);
        return response()->json($response, 200);
    }
}
