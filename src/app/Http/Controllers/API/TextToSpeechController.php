<?php

namespace App\Http\Controllers\API;

use App\Custom\Helpers;
use App\Http\Controllers\Controller;
use App\Models\ATISAudioFile;
use App\OpenApi\Parameters\GetAirportParameters;
use App\OpenApi\Parameters\GetTextToSpeechParameters;
use App\OpenApi\RequestBodies\TTS\GenerateRequestBody;
use App\OpenApi\RequestBodies\TTS\GetTextToSpeechRequestBody;
use App\OpenApi\Responses\TTS\ErrorGeneratingResponse;
use App\OpenApi\Responses\TTS\ErrorRequestConflictResponse;
use App\OpenApi\Responses\TTS\ErrorValidatingIcaoResponse;
use App\OpenApi\Responses\TTS\ErrorWithVoiceAPIResponse;
use App\OpenApi\Responses\TTS\SuccessResponse;
use App\OpenApi\Responses\TTS\GetTextToSpeech\ErrorGetTextToSpeechResponse;
use App\OpenApi\Responses\TTS\GetTextToSpeech\SuccessResponse as GetTextToSpeechSuccessResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Vyuldashev\LaravelOpenApi\Attributes as OpenApi;

#[OpenApi\PathItem]
class TextToSpeechController extends Controller
{
    /**
     * Get Airport TTS.
     *
     * Gets a link to a mp3 text-to-speech file for an airport and returns it in a JSON response.
     *
     * @param Request $request
     * @return JsonResponse
     */
    #[OpenApi\Operation(tags: ['Text to Speech'])]
    #[OpenApi\Parameters(factory: GetTextToSpeechParameters::class)]
    #[OpenApi\Response(factory: ErrorValidatingIcaoResponse::class, statusCode: 400)]
    #[OpenApi\Response(factory: ErrorGetTextToSpeechResponse::class, statusCode: 404)]
    #[OpenApi\Response(factory: GetTextToSpeechSuccessResponse::class, statusCode: 200)]
    public function index(Request $request): JsonResponse
    {
        // Get the request parameters
        $id = $request->id;
        $icao = $request->icao;

        // Validate the request
        if (
            !isset($icao) ||
            !Helpers::validateIcao($icao)
        ) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid ICAO code.',
                'code' => 400,
                'data' => null
            ]);
        }

        // Get the ATIS audio file
        $atis_file = ATISAudioFile::where('icao', $icao)->where('id', $id)->first();

        // Check if the ATIS audio file exists
        if ($atis_file == null || !$atis_file->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'ATIS audio file not found.',
                'code' => 404,
                'data' => null
            ]);
        }

        // Return the response
        return response()->json([
            'status' => 'success',
            'message' => 'ATIS audio file found.',
            'code' => 200,
            'data' => [
                'id' => $atis_file->id,
                'name' => $atis_file->file_name,
                'url' => $atis_file->url,
                'expires_at' => $atis_file->expires_at,
            ]
        ]);
    }

    /**
     * Generate Airport TTS.
     *
     * Generates a mp3 text-to-speech file for an airport and returns a link to it.
     *
     * @param string $icao The ICAO code of the airport to generate the TTS for.
     * @param Request $request
     * @return JsonResponse
     */
    #[OpenApi\Operation(tags: ['Text to Speech'])]
    #[OpenApi\Parameters(factory: GetAirportParameters::class)]
    #[OpenApi\RequestBody(factory: GenerateRequestBody::class)]
    #[OpenApi\Response(factory: ErrorRequestConflictResponse::class, statusCode: 409)]
    #[OpenApi\Response(factory: ErrorValidatingIcaoResponse::class, statusCode: 400)]
    #[OpenApi\Response(factory: ErrorWithVoiceAPIResponse::class, statusCode: 500)]
    #[OpenApi\Response(factory: ErrorGeneratingResponse::class, statusCode: 422)]
    #[OpenApi\Response(factory: SuccessResponse::class, statusCode: 200)]
    public function generate(Request $request): JsonResponse
    {
        // Get the request parameters
        $icao = $request->icao;
        $atis = $request->atis;
        $ident = $request->ident;

        // Validate the request
        if (!Helpers::validateIcao($icao)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid ICAO code.',
                'code' => 400,
                'data' => null
            ]);
        }

        // Check if the request has the required parameters, not using request()->validate() for now.
        if (!isset($atis) || !isset($ident) || !isset($icao)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You must provide an ATIS, ATIS identifier, and ICAO code.',
                'code' => 400,
                'data' => null
            ]);
        }

        // Check if the ATIS already exists in the database
        $atis_file = ATISAudioFile::where('icao', $icao)->where('ident', $ident)->where('atis', $atis)->first();
        if ($atis_file != null && $atis_file->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'This ATIS audio file already exists.',
                'code' => 409, // Conflict error code
                'data' => [
                    'id' => $atis_file->id,
                    'name' => $atis_file->file_name,
                    'url' => $atis_file->url,
                    'expires_at' => $atis_file->expires_at,
                ]
            ]);
        }

        // Create the atis audio file
        $VOICE_RSS_API_KEY = config('app.voice-rss-key');

        // Validate the API key
        if (!isset($VOICE_RSS_API_KEY) || empty($VOICE_RSS_API_KEY)) {
            return response()->json([
                'status' => 'error',
                'message' => 'The VoiceRSS API key is not set.',
                'code' => 500,
                'data' => null
            ]);
        }
        $ch = curl_init("https://api.voicerss.org/?key=$VOICE_RSS_API_KEY&hl=en-us&c=MP3&v=John&f=16khz_16bit_stereo&src=" . rawurlencode($atis));
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_NOBODY, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        $output = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($status == 200) {
            // Define some variables
            $zulu = gmdate("dHi");
            $icao = strtoupper($icao);
            $ident = strtoupper($ident);
            $name = $icao . "_ATIS_" . $ident . "_" . $zulu . "Z.mp3";

            // Create the database entry
            $atis_file = new ATISAudioFile;
            $atis_file->icao = $icao;
            $atis_file->ident = $ident;
            $atis_file->atis = $atis;
            $atis_file->zulu = $zulu;
            $atis_file->file_name = $name;
            $atis_file->save();

            $file_id = $atis_file->id;

            // Write the file to the server storage
            Storage::disk('local')->put("public/atis/$file_id/$name", $output);
            $file_url = Storage::url("public/atis/$file_id/$name");
            if (!$file_url) {
                // Delete the database entry
                $atis_file->delete();

                return response()->json([
                    'status' => 'error',
                    'message' => 'Could not generate ATIS audio file.',
                    'code' => 422,
                    'data' => null
                ]);
            }

            // Validate that the file exists
            if (!Storage::disk('local')->exists("public/atis/$file_id/$name")) {
                // Delete the database entry
                $atis_file->delete();

                return response()->json([
                    'status' => 'error',
                    'message' => 'Could not generate ATIS audio file.',
                    'code' => 422,
                    'data' => null
                ]);
            }

            // Store the file url in the database, add the url to the response
            $atis_file->url = $file_url;
            // Set the expiration date to 2 hours from now
            $atis_file->expires_at = now()->addHours(2);
            $atis_file->update();

            // Return the response
            return response()->json([
                'status' => 'success',
                'message' => 'ATIS generated successfully.',
                'code' => 200,
                'data' => [
                    'id' => $file_id,
                    'name' => $name,
                    'url' => $file_url,
                    'expires_at' => $atis_file->expires_at,
                ]
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Could not generate ATIS using the VoiceRSS API.',
                'code' => 500,
                'data' => null
            ]);
        }
    }

    /**
     * Delete Airport TTS.
     *
     * Deletes a mp3 text-to-speech file for an airport.
     *
     * @param string $icao The ICAO code of the airport to generate the TTS for.
     * @param Request $request
     * @return void
     */
    #[OpenApi\Operation(tags: ['Text to Speech'])]
    #[OpenApi\Parameters(factory: GetAirportParameters::class)]
    public function delete(Request $request): void
    {
        // TODO: Delete mp3 atis file.
    }
}
