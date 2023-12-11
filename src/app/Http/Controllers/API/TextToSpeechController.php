<?php

namespace App\Http\Controllers\API;

use App\Custom\Helpers;
use App\Custom\TextToSpeech;
use App\Http\Controllers\Controller;
use App\Models\ATISAudioFile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Vyuldashev\LaravelOpenApi\Attributes as OpenApi;
use Illuminate\Support\Facades\Log;

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
            return Helpers::response('Invalid ICAO code.', null, 400, 'error');
        }

        // Get the ATIS audio file
        $atis_file = ATISAudioFile::where('icao', $icao)->where('id', $id)->first();

        // Check if the ATIS audio file exists
        if ($atis_file == null || !$atis_file->exists()) {
            return Helpers::response('ATIS audio file not found.', null, 404, 'error');
        }

        // Return the response
        return Helpers::response('ATIS audio file found.',  [
            'id' => $atis_file->id,
            'name' => $atis_file->file_name,
            'url' => $atis_file->url,
            'expires_at' => $atis_file->expires_at,
        ], 200);
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
     * @throws \Exception
     */
    #[OpenApi\Operation(tags: ['Text to Speech'])]
    #[OpenApi\RequestBody(factory: \App\OpenApi\RequestBodies\TTS\GenerateRequestBody::class)]
    #[OpenApi\Response(factory: \App\OpenApi\Responses\TTS\ErrorRequestConflictResponse::class, statusCode: 409)]
    #[OpenApi\Response(factory: \App\OpenApi\Responses\TTS\ErrorValidatingIcaoResponse::class, statusCode: 400)]
    #[OpenApi\Response(factory: \App\OpenApi\Responses\TTS\ErrorWithVoiceAPIResponse::class, statusCode: 500)]
    #[OpenApi\Response(factory: \App\OpenApi\Responses\TTS\ErrorGeneratingResponse::class, statusCode: 422)]
    #[OpenApi\Response(factory: \App\OpenApi\Responses\TTS\SuccessResponse::class, statusCode: 200)]
    #[OpenApi\Response(factory: \App\OpenApi\Responses\TTS\VoiceAPIConfigurationDependencyFailedResponse::class, statusCode: 424)]
    public function generate(Request $request): JsonResponse
    {
        // Variables
        $engine = config('app.voice-engine');
        $API_KEYS = [];

        // Get the request parameters
        $icao = $request->icao;
        $atis = $request->atis;
        $ident = $request->ident;
        $options = $request->options ?? null;

        // Validate the request
        if (!Helpers::validateIcao($icao)) {
            return Helpers::response('Invalid ICAO code.', null, 400, 'error');
        }

        // Check if the request has the required parameters, not using request()->validate() for now.
        if (!isset($atis) || !isset($ident) || !isset($icao)) {
            return Helpers::response('You must provide an ATIS, ATIS identifier, and ICAO code.', null, 400, 'error');
        }

        // Check if custom tts engine info was provided
        if (isset($options)) {
            // Check if the custom config is valid
            if (!TextToSpeech::validateCustomConfig($options)) {
                return Helpers::response('Invalid custom config.', null, 400, 'error');
            }

            // Set the API key
            $API_KEYS[$options['engine']] = $options['api_key'];

            // Set the engine
            $engine = $options['engine'];
        }

        // Check if the ATIS already exists in the database
        $atis_file = ATISAudioFile::where('icao', $icao)->where('ident', $ident)->where('atis', $atis)->where('custom_atis', false)->first();
        // Don't through if options are set, as we will always generate a new file if options are set
        if ($atis_file != null && $atis_file->exists() && !isset($options)) {
            return Helpers::response('This ATIS audio file already exists.', [
                'id' => $atis_file->id,
                'name' => $atis_file->file_name,
                'url' => $atis_file->url,
                'expires_at' => $atis_file->expires_at,
            ], 409, 'error');
        }

        // Make sure at least one API key is set
        if (!TextToSpeech::hasApiKey() && !$options && $engine != 'Larynx') {
            Log::error('Your server voice API configuration is incorrect. Please check your .env file.');

            // Return the response
            return Helpers::response('Server voice API configuration error.', null, 424, 'error');
        }

        $tts = null;
        // Check if custom tts engine info was provided
        if (isset($options)) {
            // Initialize the TextToSpeech class
            $tts = new TextToSpeech($atis, 'en-us', $engine, $options, $API_KEYS);
        } else {
            // Initialize the TextToSpeech class
            $tts = new TextToSpeech($atis, 'en-us', $engine);
        }

        try {
            $output = $tts->generateAudio();

            // Check if the output is empty
            if (empty($output) || $output == "ERROR: The API key is not available!") {
                Log::error('There was an error generating the ATIS audio file.');

                // Return the response
                return Helpers::response('There was an error generating the ATIS audio file.', null, 500, 'error');
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            // Return the response
            return Helpers::response('There was an error generating the ATIS audio file.', null, 500, 'error');
        }

        // Define some variables
        $zulu = gmdate("dHi");
        $icao = strtoupper($icao);
        $ident = strtoupper($ident);
        // TODO: Add logic to make sure the file extension is correct
        $name = $icao . "_ATIS_" . $ident . "_" . $zulu . "Z.mp3";

        // Create the database entry
        $atis_file = new ATISAudioFile;
        $atis_file->icao = $icao;
        $atis_file->ident = $ident;
        $atis_file->atis = $atis;
        $atis_file->zulu = $zulu;
        $atis_file->file_name = $name;
        $atis_file->storage_location = config('filesystems.default');
        if (strpos($atis, 'AUTOMATED WEATHER OBSERVATION') !== false) {
            $atis_file->output_type = 'AWOS';
        } else {
            $atis_file->output_type = 'ATIS';
        }
        $atis_file->custom_atis = isset($options) ? true : false;
        $atis_file->save();

        $file_id = $atis_file->id;

        // Write the file to the server storage
        Storage::disk()->put("atis/$file_id/$name", $output);
        $file_url = Storage::url("atis/$file_id/$name");
        if (!$file_url) {
            // Delete the database entry
            $atis_file->delete();

            return Helpers::response('Could not generate ATIS audio file.', null, 422, 'error');
        }

        // Validate that the file exists
        if (!Storage::disk()->exists("atis/$file_id/$name")) {
            // Delete the database entry
            $atis_file->delete();

            return Helpers::response('Could not generate ATIS audio file.', null, 422, 'error');
        }

        // Store the file url in the database, add the url to the response
        $atis_file->url = Storage::url("atis/$file_id/$name");

        // Set the expiration date to 2 hours from now
        $atis_file->expires_at = now()->addHours(2);
        $atis_file->update();

        // Return the response
        return Helpers::response('ATIS generated successfully.', [
            'id' => $file_id,
            'name' => $name,
            'url' => Storage::url("atis/$file_id/$name"),
            'expires_at' => $atis_file->expires_at,
        ], 200);
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
            return Helpers::response('You must provide an ATIS ID.', null, 400, 'error');
        }

        // Check if the ATIS audio file exists
        $atis_file = ATISAudioFile::where('id', $id)->first();

        // Check if the ATIS audio file exists
        if ($atis_file == null || !$atis_file->exists()) {
            return Helpers::response('ATIS audio file not found.', null, 404, 'error');
        }

        // Check if the file requires a password to delete
        if ($atis_file->password != null) {
            // Check if the password is correct
            if (!isset($password) || $password != $atis_file->password) {
                return Helpers::response('Incorrect password.', null, 401, 'error');
            }
        }

        // Delete the file from the server
        Storage::delete('atis/' . $id . '/' . $atis_file->file_name);

        // Delete the database entry
        $atis_file->delete();

        // Return the response
        return Helpers::response('ATIS audio file deleted successfully.', null, 200);
    }
}
