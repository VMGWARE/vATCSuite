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
     * Get a link to an Airport TTS (Text-to-Speech) file.
     *
     * Retrieves a link to an MP3 text-to-speech file for an airport using its ICAO code and ID, returning it in a JSON response.
     * If you are looking to generate a new ATIS audio file, go down to the 'Generate an Airport TTS (Text-to-Speech) file' section.
     *
     * @param Request $request The HTTP request containing the 'icao' and 'id' parameters.
     * @return JsonResponse Returns a JSON response containing the link to the TTS audio file.
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
     * Generate the Airport TTS (Text-to-Speech) file.
     *
     * Generates the MP3 text-to-speech file for an airport using its ICAO code, ATIS, and ATIS identifier, returning it in a JSON response.
     *
     * @param Request $request
     * @return JsonResponse
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
            $atis_file->url = url($file_url);

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
                    'url' => url($file_url),
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
     * Delete the Airport TTS (Text-to-Speech) file.
     *
     * Deletes an MP3 text-to-speech file for an airport using its ID and password (if required).
     *
     * @param Request $request The HTTP request containing the 'id' and 'password' (optional) parameters.
     * @return JsonResponse Returns a JSON response indicating the success or failure of the delete operation.
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
        Storage::delete('public/atis/' . $id . '/' . $atis_file->file_name);

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
