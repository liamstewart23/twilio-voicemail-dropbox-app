<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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
        $response->say('Please leave a message for Natasha and Liam at the beep. Press the star key when finished.');
        $response->record(['maxLength' => 60, 'finishOnKey' => '*','action'=> getenv('BASE_URL').'/save-voicemail', 'method' => 'GET']);
//        $response->say('I did not receive a recording');

        echo $response;

        $response->stop();
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

        $name = Carbon::now() . '_' . $request->CallSid . '_' . $request->RecordingSid . '.wav';

        // create directory
        if (!file_exists('voicemails/'.$request->Caller)) {
            mkdir('voicemails/'.$request->Caller, 0777, true);
        }

        file_put_contents( 'voicemails/'.$request->Caller.'/'.$name, $request->RecordingUrl );

    }

}
