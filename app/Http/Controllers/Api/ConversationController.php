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

    public function getMyConversations($userId){
        $user=User::find($userId);

        $singleConv=ConversationUser::where('user_id',$user->id)
                                ->join('conversations','conversations.id','conversation_user.conversation_id')
                                ->where('conv_type','MONO')
                                ->pluck('conversation_id');

        $single=Conversation::whereIn('conversations.id',$singleConv)
                                ->leftJoin('conversation_user','conversation_user.conversation_id','conversations.id')
                                ->leftJoin('messages',function($joins){
                                    $joins->on('messages.conversation_id','=','conversations.id')
                                            ->select('messages.text_msg','messages.content','messages.content_type','messages.created_at')
                                            ->orderBy('messages.id','DESC')
                                            ->limit(1);
                                })
                                ->addSelect(DB::raw("IF(messages.content_type='TEXT',messages.text_msg,messages.content_type)as last_message,messages.created_at"))
                                ->where('conversation_user.user_id','!=',$userId)
                                ->join('users','users.id','conversation_user.user_id')
                                ->select(DB::raw("users.name as conv_title, users.user_image as conv_icon,conversation_user.user_id as user_id,last_msg,conv_type,conversation_user.conversation_id,conversation_user.last_active"));

        $groupConv=ConversationUser::where('user_id',$user->id)
                                ->join('conversations','conversations.id','conversation_user.conversation_id')
                                ->where('conv_type','GROUP')
                                ->pluck('conversation_id');
        $groups=Conversation::whereIn('conversations.id',$groupConv)
                                ->leftJoin('conversation_user','conversation_user.conversation_id','conversations.id')
                                ->leftJoin('messages',function($joins){
                                    $joins->on('messages.conversation_id','=','conversations.id')
                                            ->select('messages.text_msg','messages.content','messages.content_type','messages.created_at')
                                            ->orderBy('messages.id','DESC')
                                            ->limit(1);
                                })
                                ->addSelect(DB::raw("IF(messages.content_type='TEXT',messages.text_msg,messages.content_type)as last_message,messages.created_at"))
                                ->where('conversation_user.user_id','=',$userId)
                                ->select(DB::raw("conv_title,conv_icon,-1 as user_id,last_msg,conv_type,id as conversation_id,conversation_user.last_active"))
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
            'content_type'=>'required',
        ];

        $validator=Validator::make($request->all(),$rules);
        if($validator->fails()){
            $response=ApiHelper::createAPIResponse(true,400,$validator->errors(),null);
            return response()->json($response,400);
        }

        if($request->content){
            $image = $request->file('content');
            $name = time().'.'.$image->getClientOriginalExtension();
            $destinationPath = public_path('/MessageContent');
            $image->move($destinationPath, $name);

            $path=url('').'/MessageContent/'.$name;
      
            $request['content']=$path;
            
            $message=Message::create($request->all());
        }else{
            $message=Message::create($request->all());
        }

        $response=ApiHelper::createAPIResponse(false,200,"",$message);
        return response()->json($response, 200);
    }

    public function getChatMessages($convId,$userId){
        $messages=ConversationUser::where('conversation_user.conversation_id',$convId)
                                    ->where('conversation_user.user_id',$userId)
                                    ->join('messages','messages.conversation_id','conversation_user.conversation_id')
                                    ->join('users','users.id','messages.sender_id')
                                    ->where('messages.id','>=','conversation_user.start_at')
                                    ->select(DB::raw("messages.id,messages.sender_id,messages.content_type,messages.content,messages.text_msg,messages.created_at,users.name as sender_name"))
                                    ->get();
        $response=ApiHelper::createAPIResponse(false,200,"",$messages);
        return response()->json($response, 200);
    }
}
