<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Twilio\Jwt\AccessToken;
use Twilio\Jwt\Grants\ChatGrant;

class ConversationController extends Controller
{
    public function generateToken(Request $request)
    {
        // Manually validate using Validator facade to ensure user_id is provided
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 401,
                'message' => $validator->errors()->first(),
            ], 401);
        }

        $token = $this->createToken($request->user_id);

        return response()->json([
            'status' => 200,
            'message' => 'Token generated',
            'token' => $token,
        ], 200);
    }

    private function createToken($userId)
    {

        $twilioAccountSid = env('TWILIO_ACCOUNT_SID');
        $twilioApiKey = env('TWILIO_API_KEY');
        $twilioApiSecret = env('TWILIO_API_SECRET');

        // Required for Chat grant
        $serviceSid = env('TWILIO_CHAT_SERVICE_SID');

        $identity = "user_{$userId}"; // or any unique identifier

        $token = new AccessToken(
            $twilioAccountSid,
            $twilioApiKey,
            $twilioApiSecret,
            3600, // Token expiry in seconds
            $identity
        );
        // Create Chat grant
        $chatGrant = new ChatGrant;
        $chatGrant->setServiceSid($serviceSid);

        $token->addGrant($chatGrant);

        // Return the JWT token
        return $token->toJWT();
    }
}
