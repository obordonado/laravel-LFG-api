<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{
    public function createNewMessage(Request $request) {

        try {

            $user_Id = auth()->user()->id;

            Log::info('User id '.$user_Id. ' is creating a message..');

            $validator = Validator::make($request->all(), [
                'channel_id' => ['required','integer'],
                'message' => ['required','string','min:2','max:100']
            ]);
            Log::info('User id '.$user_Id.' passed validator correctly.');

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
            $userMessage = $request->input('message');

            $message = new Message();
            $message->channel_id = $channel_id;
            $message->message = $userMessage;
            $message->user_id = $user_Id;
            $message->save();
            
            Log::info('User id '.$user_Id.' created message correctly.');

            return response()->json(
                [
                    'success' => true,
                    'message' => 'User id '.$user_Id.' created message correctly.'
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
            $user_Id = auth()->user()->id;

            Log::info('User id '.$user_Id.' getting own messages.');

            $messages = Message::query()
            -> where('user_id','=', $user_Id)
            -> get()
            -> toArray();

            Log::info('User id '.$user_Id.' got own messages without error.');

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
            $user_Id = auth()->user()->id;

            Log::info('User id '.$user_Id.' getting message by message id...');

            $message = Message::query()
            
            ->where('id','=', $id)
            ->where('user_id','=', $user_Id)
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
            
            Log::info('User id '.$user_Id.' retrieved message by id without error.');

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
            $user_Id = auth()->user()->id;

            Log::info('User id '.$user_Id.' updating message...');

            $validator = Validator::make($request->all(), [
                'channel_id' => ['required','integer'],
                'message' => ['required','string','min:2','max:100']
            ]);

            if($validator->fails()) {

                Log::info('User id '.$user_Id.' validation error updating message.'.$validator->errors());

                return response()->json(
                    [
                        'success' => false,
                        'message' => 'User id '.$user_Id.' validation error updating message. '.$validator->errors()
                    ],
                    400
                );
            }            
            Log::info('User id '.$user_Id.' passed validator correctly.');

            $message = Message::query()
            -> where('user_id', '=' , $user_Id)
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

            Log::info('User id '.$user_Id.' updated message correctly.');

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
            $user_Id = auth()->user()->id;

            Log::info('User id '.$user_Id.' deleting message...');

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

            Log::info('User id '.$user_Id.' deleted message correctly.');

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
