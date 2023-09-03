<?php

namespace App\Http\Controllers\API;

use App\Custom\Helpers;
use App\Http\Controllers\Controller;
use App\Models\ATISAudioFile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Vyuldashev\LaravelOpenApi\Attributes as OpenApi;

#[OpenApi\PathItem]
class TextToSpeechController extends Controller
{
    /**
     * Retrieve the URL for an Airport TTS (Text-to-Speech) audio file.
     *
     * This method is responsible for fetching the URL of a TTS audio file associated with an airport. 
     * To identify the correct file, it uses the provided ICAO code and ID from the HTTP request.
     * In the event the desired TTS audio file doesn't exist, an error response is returned.
     * If you need to create a new ATIS audio file, please refer to the documentation under the 'Generate an Airport TTS (Text-to-Speech) file' section.
     *
     * @param Request $request An instance of the HTTP request which should contain both 'icao' and 'id' parameters.
     * @return JsonResponse A JSON response. It can be:
     *                      1. A success response with the audio file details.
     *                      2. An error response indicating an invalid ICAO code.
     *                      3. An error response indicating the ATIS audio file was not found.
     */
    #[OpenApi\Operation(tags: ['Text to Speech'])]
    #[OpenApi\Parameters(factory: \App\OpenApi\Parameters\GetTextToSpeechParameters::class)]
    #[OpenApi\Response(factory: \App\OpenApi\Responses\TTS\ErrorValidatingIcaoResponse::class, statusCode: 400)]
    #[OpenApi\Response(factory: \App\OpenApi\Responses\TTS\GetTextToSpeech\ErrorGetTextToSpeechResponse::class, statusCode: 404)]
    #[OpenApi\Response(factory: \App\OpenApi\Responses\TTS\GetTextToSpeech\SuccessResponse::class, statusCode: 200)]
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
     * Generate the Airport TTS (Text-to-Speech) audio file.
     *
     * This method handles the creation of a TTS audio file for an airport. It requires the ICAO code, ATIS, 
     * and the ATIS identifier to generate the MP3 audio file. Once generated, the audio file's details are returned in a JSON response.
     *
     * @param Request $request An instance of the HTTP request which should contain:
     *                          - 'icao': The International Civil Aviation Organization (ICAO) code for the airport.
     *                          - 'atis': The Automated Terminal Information Service (ATIS) for the airport.
     *                          - 'ident': The identifier for the ATIS.
     * @return JsonResponse A JSON response that can indicate:
     *                      1. A success response after successfully generating the audio file.
     *                      2. An error response if the provided ICAO code is invalid.
     *                      3. An error response if required parameters are missing.
     *                      4. An error response if the ATIS audio file already exists.
     *                      5. An error response for issues with the VoiceRSS API.
     *                      6. An error response if the generated audio file cannot be saved.
     */
    #[OpenApi\Operation(tags: ['Text to Speech'])]
    #[OpenApi\Parameters(factory: \App\OpenApi\Parameters\GetAirportParameters::class)]
    #[OpenApi\RequestBody(factory: \App\OpenApi\RequestBodies\TTS\GenerateRequestBody::class)]
    #[OpenApi\Response(factory: \App\OpenApi\Responses\TTS\ErrorRequestConflictResponse::class, statusCode: 409)]
    #[OpenApi\Response(factory: \App\OpenApi\Responses\TTS\ErrorValidatingIcaoResponse::class, statusCode: 400)]
    #[OpenApi\Response(factory: \App\OpenApi\Responses\TTS\ErrorWithVoiceAPIResponse::class, statusCode: 500)]
    #[OpenApi\Response(factory: \App\OpenApi\Responses\TTS\ErrorGeneratingResponse::class, statusCode: 422)]
    #[OpenApi\Response(factory: \App\OpenApi\Responses\TTS\SuccessResponse::class, statusCode: 200)]
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
            Storage::disk()->put("atis/$file_id/$name", $output);
            $file_url = Storage::url("atis/$file_id/$name");
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
            if (!Storage::disk()->exists("atis/$file_id/$name")) {
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
            $atis_file->url = Storage::url("atis/$file_id/$name");

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
                    'url' => Storage::url("atis/$file_id/$name"),
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
     * Delete a specific Airport TTS (Text-to-Speech) audio file.
     *
     * This method facilitates the removal of a TTS audio file linked to an airport. The file to be deleted 
     * is identified using its unique ID, and if the file is password-protected, the correct password must also be provided.
     *
     * @param Request $request An instance of the HTTP request which should contain:
     *                          - 'id': The unique identifier for the ATIS audio file.
     *                          - 'password': (optional) The password for the file if it's password-protected.
     * @return JsonResponse A JSON response that can indicate:
     *                      1. A success response after successfully deleting the audio file.
     *                      2. An error response if the ID isn't provided.
     *                      3. An error response if the ATIS audio file isn't found.
     *                      4. An error response if the provided password is incorrect.
     */
    #[OpenApi\Operation(tags: ['Text to Speech'])]
    #[OpenApi\Parameters(factory: \App\OpenApi\Parameters\TTS\DeleteParameters::class)]
    #[OpenApi\Response(factory: \App\OpenApi\Responses\TTS\Delete\ErrorMissingIdResponse::class, statusCode: 400)]
    #[OpenApi\Response(factory: \App\OpenApi\Responses\TTS\Delete\ErrorNotFoundResponse::class, statusCode: 404)]
    #[OpenApi\Response(factory: \App\OpenApi\Responses\TTS\Delete\ErrorPasswordProtectedResponse::class, statusCode: 401)]
    #[OpenApi\Response(factory: \App\OpenApi\Responses\TTS\Delete\SuccessResponse::class, statusCode: 200)]
    public function delete(Request $request): JsonResponse
    {
        // Get the request parameters
        $id = isset($request->id) ? $request->id : null;
        $password = isset($request->password) ? $request->password : null;

        // Validate the request
        if (!isset($id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You must provide an ATIS ID.',
                'code' => 400,
                'data' => null
            ]);
        }

        // Check if the ATIS audio file exists
        $atis_file = ATISAudioFile::where('id', $id)->first();

        // Check if the ATIS audio file exists
        if ($atis_file == null || !$atis_file->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'ATIS audio file not found.',
                'code' => 404,
                'data' => null
            ]);
        }

        // Check if the file requires a password to delete
        if ($atis_file->password != null) {
            // Check if the password is correct
            if (!isset($password) || $password != $atis_file->password) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Incorrect password.',
                    'code' => 401,
                    'data' => null
                ]);
            }
        }

        // Delete the file from the server
        Storage::delete('atis/' . $id . '/' . $atis_file->file_name);

        // Delete the database entry
        $atis_file->delete();

        // Return the response
        return response()->json([
            'status' => 'success',
            'message' => 'ATIS audio file deleted successfully.',
            'code' => 200,
            'data' => null
        ]);
    }
}
