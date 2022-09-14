<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{
    public function createNewMessage(Request $request) {

        try {

            $userId = auth()->user()->id;

            Log::info('User id '.$userId. ' is creating a message..');

            $validator = Validator::make($request->all(), [
                'channel_id' => ['required','integer'],
                'message' => ['required','string','min:2','max:100']
            ]);
            Log::info('User id '.$userId.' passed validator correctly.');

            if ($validator->fails()) {
                
                Log::info('Validator failed. '.$validator->errors());

                return response()->json(
                    [
                        "success" => false,
                        "message" => 'Error creating new message TWO '.$validator->errors()
                    ],
                    400
                );
            };

            $channel_id = $request->input('channel_id');

            Log::info('Verifying if channel exists...');
            $channel = Channel::find($channel_id);

            if(!$channel){

                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Channel does not exist.'
                    ],
                    404
                );
            }
            Log::info('Channel '.$channel_id.' does exist.');

            Log::info("Finding out if user has joined the channel...");
            $joinedUser = DB::table('channel_user')
            ->where('channel_id','=',$channel_id)
            ->where('user_id', '=', $userId)
            ->first();

            if(!$joinedUser){
                Log::info('User id '.$userId.' has not joined the channel yet.');

                return response()->json(
                    [
                        'success' => false,
                        'message' => 'User id '.$userId.' must join channel first.'
                    ],
                    401
                );
            }

            $userMessage = $request->input('message');

            Log::info('User id '.$userId,' has previously joined channel '.$channel_id.' and has created a new message.');
            $message = new Message();
            $message->channel_id = $channel_id;
            $message->message = $userMessage;
            $message->user_id = $userId;
            $message->save();
            
            Log::info('User id '.$userId.' created message correctly.');

            return response()->json(
                [
                    'success' => true,
                    'message' => 'User id '.$userId.' created message correctly.'
                ],
                200
            );

        } catch (\Exception $exception) {

            Log::info('Error creating new message '. $exception->getMessage());
            
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Error creating message.',
                ],
                400
            );
        }
    }

    public function getOwnMessages(){

        try {
            $userId = auth()->user()->id;

            Log::info('User id '.$userId.' getting own messages.');

            $messages = Message::query()
            -> where('user_id','=', $userId)
            -> get()
            -> toArray();

            Log::info('User id '.$userId.' got own messages without error.');

            return response()->json(
                [
                    'success' => true,
                    'message' => 'Messages retrieved successfully.',
                    'data' => $messages
                ],
                200
            );

        } catch (\Exception $exception) {
            Log::error('Error getting messages. '. $exception->getMessage());

            return response()->json(
                [
                'success'=> false,
                'message' => 'Messages could not be retrieved.'
                ],
                400
            );
        }
    }
    
    public function getMessageByMsgId($id) {

        try {
            $userId = auth()->user()->id;

            Log::info('User id '.$userId.' getting message by message id...');

            $message = Message::query()
            
            ->where('id','=', $id)
            ->where('user_id','=', $userId)
            ->get()->toArray();

            if(!$message) {

                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Message does not exist.'
                    ],
                    404
                );
            }
            
            Log::info('User id '.$userId.' retrieved message by id without error.');

            return response()->json(
                [
                'success'=> true,
                'message' => 'Message retrieved successfully.',
                'data'=> $message
                ],
                200
            );
    
        } catch (\Exception $exception) {
            Log::info('Error getting message by Id '.$exception->getMessage());

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Error getting message by Id.'
                ],
                400
            );
        }
    }

    public function updateMessageByMsgId(Request $request, $id){
        
        try {
            $userId = auth()->user()->id;

            Log::info('User id '.$userId.' updating message...');

            $validator = Validator::make($request->all(), [
                'channel_id' => ['required','integer'],
                'message' => ['required','string','min:2','max:100']
            ]);

            if($validator->fails()) {

                Log::info('User id '.$userId.' validation error updating message.'.$validator->errors());

                return response()->json(
                    [
                        'success' => false,
                        'message' => 'User id '.$userId.' validation error updating message. '.$validator->errors()
                    ],
                    400
                );
            }            
            Log::info('User id '.$userId.' passed validator correctly.');

            $message = Message::query()
            -> where('user_id', '=' , $userId)
            -> find($id);            

            if (!$message){
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'This message does not exist.',
                    ],
                    404
                );
            }

            $channelId = $request->input('channel_id');
            $userMessage = $request->input('message');

            if(isset($channel_id)){
                $message->channel_id = $channelId;
            }

            if(isset($message)){
                $message->message = $userMessage;
            }

            $message->save();

            Log::info('User id '.$userId.' updated message correctly.');

            return response()->json(

                [
                    'success' => true,
                    'message' => 'Message ' . $id . ' updated correctly.'

                ],
                200
                );

        } catch (\Exception $exception) {
            
            Log::info('Error updating message. ' . $exception->getMessage());
            return response()->json(
                [
                    'success'=> false,
                    'message' => 'Error updating message.'
                ],
                400
            );
        }
    }

    public function delMessageById($id){
        
        try {
            $userId = auth()->user()->id;

            Log::info('User id '.$userId.' deleting message...');

            $message = Message::query()-> find($id);            

            if (!$message){
                Log::info('Message does not exist.');

                return response()->json(
                    [
                        'success' => false,
                        'message' => 'This message does not exist.',
                    ],
                    404
                );
            }

            $message->delete();

            Log::info('User id '.$userId.' deleted message correctly.');

            return response()->json(
                [
                    'success' => true,
                    'message' => 'Message '.$id.' deleted correctly.',

                ],
                200
                );

        } catch (\Exception $exception) {
            
            Log::info('Error deleting message. '.$exception->getMessage());

            return response()->json(
                [
                    'success'=> false,
                    'message' => 'Error deleting message.'
                ],
                400
            );
        }
    }

    public function getMessagesByChannel($channelId){

        try {
            Log::info('Getting messages by channel id...');

            $messages = Message::query()
            ->where('channel_id', '=', $channelId)
            ->get()
            ->toArray();

            Log::info('Got messages by channel id correctly.');
            
            return response()->json(
                [
                    'success' => true,
                    'message'=> 'Got messages by channel id correctly.',
                    'data' => $messages
                ],
                200
            );
            
        } catch (\Exception $exception) {
            
            Log::info('Error getting messages by channel id. '.$exception->getMessage());

            return response()->json(
                [
                    'success'=> false,
                    'message' => 'Error getting messages by channel id.'
                ],
                400
            );
        }
    }


}
