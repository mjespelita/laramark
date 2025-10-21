<?php

namespace App\Http\Controllers;

use App\Models\FacebookMessengerWebhook;
use Illuminate\Http\Request;

class FacebookMessengerWebhookController extends Controller
{
    private $VERIFY_TOKEN = 'Testing123456789!'; // must match your webhook verify token

    public function init()
    {
        return view('messenger-init');
    }

    /**
     * ✅ 1️⃣ Verify webhook (GET)
     */
    public function verify(Request $request)
    {
        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        if ($mode === 'subscribe' && $token === $this->VERIFY_TOKEN) {
            return response($challenge, 200)->header('Content-Type', 'text/plain');
        }

        return response('Verification token mismatch', 403);
    }

    /**
     * ✅ 2️⃣ Handle webhook POST events
     */
    public function handle(Request $request)
    {
        $payload = $request->all();

        $entry = $payload['entry'][0]['messaging'][0] ?? null;

        if ($entry) {
            $psid = $entry['sender']['id'] ?? null; // Facebook user PSID
            $page_id = $entry['recipient']['id'] ?? null; // Page ID
            $messageText = $entry['message']['text'] ?? null; // Text user typed

            if ($psid && $page_id && $messageText) {
                $user_app_id = trim($messageText); // Treat message text as user_app_id

                // 🔹 Ensure no duplicate user_app_id exists
                FacebookMessengerWebhook::where('user_app_id', $user_app_id)->delete();

                // 🔹 Optionally also ensure no duplicate PSID exists (for safety)
                FacebookMessengerWebhook::where('psid', $psid)->delete();

                // 🔹 Insert new record
                FacebookMessengerWebhook::create([
                    'user_app_id' => $user_app_id,
                    'user_id' => $page_id,
                    'psid' => $psid,
                ]);
            }
        }

        return response()->json(['status' => 'ok']);
    }
}
