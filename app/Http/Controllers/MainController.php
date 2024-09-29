<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Twilio\Exceptions\ConfigurationException;
use Twilio\Rest\Client;
use Twilio\TwiML\VoiceResponse;

class MainController extends Controller
{
    /**
     * TwiML for call
     * @return $this
     */
    public function init(){
        $response = new VoiceResponse();
        $response->say('Please leave a message for Natasha and Liam at the beep, you have 60 seconds. Press the star key when finished.');
        $response->record(['maxLength' => 60, 'finishOnKey' => '*','action'=> getenv('BASE_URL').'/save-voicemail', 'method' => 'GET']);
        $response->say('I did not receive a recording');

        echo $response;
    }

    /**
     * make the call
     * @throws ConfigurationException|\Twilio\Exceptions\TwilioException
     */
    public function makeCall() {

        $sid = getenv('ACCOUNT_SID');
        $token = getenv('TWILIO_TOKEN');
        $twilio_number = getenv('TWILIO_NUMBER');
        $receiver_number = getenv('RECEIVER_NUMBER');
        $twilio = new Client( $sid, $token );
        $twilio->calls
            ->create( $receiver_number, // the receiver
                $twilio_number, // your voice enabled number from the console
                [
                    'url' => getenv('BASE_URL').'/response'
                ]
            );
    }

    /**
     * save the Twilio recording
     * @throws Exception
     */
    public function saveVoiceMail(Request $request){

        $response = new VoiceResponse();

        $response->say('Message received, thank you for calling! Love Natasha and Liam');

        echo $response;

        $response->stop();


        Log::info($request->all());
        try {
            $name = Carbon::now() . '_' . $request->Caller. '_' . $request->CallSid . '_' . $request->RecordingSid . '.wav';
            $response = Http::get($request->RecordingUrl);
            if ($response->successful()) {
                file_put_contents('voicemails/'.$name, $response->body());
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
            throw new Exception($e->getMessage());
        }
    }
}
