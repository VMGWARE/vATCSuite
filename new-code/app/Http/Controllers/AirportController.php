<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use \App\Models\Airport as AirportModel;
use \App\Models\ATISAudioFile;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use \App\Custom\AtisGenerator;
use \App\Custom\Helpers;
use Vyuldashev\LaravelOpenApi\Attributes as OpenApi;
use Illuminate\Support\Facades\Storage;

#[OpenApi\PathItem]
class AirportController extends Controller
{
    /**
     * Get Airport.
     *
     * Gets an airport from the database and returns it in a JSON response.
     *
     * @param string $icao The ICAO code of the airport to get.
     * @param Request $request
     * @return JsonResponse
     */
    #[OpenApi\Operation(tags: ['Airport'])]
    #[OpenApi\Parameters(factory: \App\OpenApi\Parameters\GetAirportParameters::class)]
    public function index(string $icao, Request $request): JsonResponse
    {
        if (!Helpers::validateIcao($icao)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid ICAO code.',
                'code' => 400,
                'data' => null
            ]);
        }

        $airport = AirportModel::where('icao', strtoupper($icao))->first();
        if (!$airport) {
            return response()->json([
                'status' => 'error',
                'message' => 'Could not locate airport with ICAO code ' . strtoupper($icao) . ' in the database.',
                'code' => 404,
                'data' => null
            ]);
        }

        $metar = Helpers::fetch_metar($icao);
        if ($metar == null) {
            $wind = null;
            $runways = null;
        } else {
            $wind = Helpers::get_wind($metar);
            $runways = Helpers::parse_runways($icao, $wind['dir']);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Airport retrieved successfully.',
            'code' => 200,
            'data' => [
                'airport' => $airport,
                'metar' => $metar,
                'wind' => $wind,
                'runways' => $runways,
            ]
        ]);
    }

    /**
     * Get All Airports.
     *
     * Gets all airports in the database and returns them in a JSON response.
     * @param Request $request
     * @return JsonResponse
     */
    #[OpenApi\Operation(tags: ['Airport'])]
    public function all(Request $request): JsonResponse
    {
        $airports = AirportModel::all()->makeHidden(['created_at', 'updated_at']);
        return response()->json([
            'status' => 'success',
            'message' => 'Airports retrieved successfully.',
            'code' => 200,
            'data' => [
                'airports' => $airports,
            ]
        ]);
    }

    /**
     * Get Airport Runways.
     *
     * Gets the runways for an airport and returns them in a JSON response.
     *
     * @param string $icao The ICAO code of the airport to get runways for.
     * @return JsonResponse
     */
    #[OpenApi\Operation(tags: ['Airport'])]
    #[OpenApi\Parameters(factory: \App\OpenApi\Parameters\GetAirportParameters::class)]
    public function runways(string $icao): JsonResponse
    {
        if (!Helpers::validateIcao($icao)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid ICAO code.',
                'code' => 400,
                'data' => null
            ]);
        }

        $airport = AirportModel::where('icao', strtoupper($icao))->first();

        if (!$airport) {
            return response()->json([
                'status' => 'error',
                'message' => 'Could not find airport with ICAO code ' . strtoupper($icao) . '.',
                'code' => 404,
                'data' => null
            ]);
        }

        $metar = Helpers::fetch_metar($icao);
        if ($metar == null) {
            return response()->json([
                'status' => 'error',
                'message' => 'Could not find METAR data for ' . strtoupper($icao) . '.',
                'code' => 404,
                'data' => null
            ]);

        }
        $wind = Helpers::get_wind($metar);
        $runways = Helpers::parse_runways($icao, $wind['dir']);

        return response()->json([
            'status' => 'success',
            'message' => 'Runways retrieved successfully.',
            'code' => 200,
            'data' => [
                'runways' => $runways,
            ]
        ]);
    }

    /**
     * Get Airport ATIS.
     *
     * Gets the ATIS for an airport and returns it in a JSON response.
     *
     * @param string $icao The ICAO code of the airport to get the ATIS for.
     * @param Request $request
     * @return JsonResponse
     */
    #[OpenApi\Operation(tags: ['Airport'])]
    #[OpenApi\Parameters(factory: \App\OpenApi\Parameters\GetAirportParameters::class)]
    public function atis(string $icao, Request $request): JsonResponse
    {
        // Validate ICAO code
        if (!Helpers::validateIcao($icao)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid ICAO code.',
                'code' => 400,
                'data' => null
            ]);
        }

        // Get the airport from the database
        $airport = AirportModel::where('icao', strtoupper($icao))->first();

        // If the airport is not found in the database, return an error
        if (!$airport) {
            return response()->json([
                'status' => 'error',
                'message' => 'Could not find airport with ICAO code ' . strtoupper($icao) . ' in the database.',
                'code' => 404,
                'data' => null
            ]);
        }

        // Fetch the METAR data for the airport
        $metar = Helpers::fetch_metar($icao);

        // Ensure that the METAR data was found
        if ($metar == null) {
            return response()->json([
                'status' => 'error',
                'message' => 'Could not find METAR data for ' . strtoupper($icao) . '.',
                'code' => 404,
                'data' => null
            ]);
        }

        // Get the wind data from the METAR
        if (!isset($request->landing_runways) || !isset($request->departure_runways)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You must select at least one landing and departing runway to generate your ATIS.',
                'code' => 400,
                'data' => null
            ]);
        }

        // Validate ATIS identifier
        if (!isset($request->ident) || !ctype_alpha($request->ident)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You must provide an ATIS identifier.',
                'code' => 400,
                'data' => null
            ]);
        }

        // Define the ATIS generator
        $spoken_atis = new AtisGenerator($icao, $request->ident, $request->landing_runways, $request->departure_runways, $request->remarks_1, $request->remarks_2, $request->override_runway);
        $text_atis = new AtisGenerator($icao, $request->ident, $request->landing_runways, $request->departure_runways, $request->remarks_1, $request->remarks_2, $request->override_runway);

        // Generate the ATIS
        $spoken = $spoken_atis->parse_atis(true);
        $text = $text_atis->parse_atis(false);

        // If the ATIS could not be generated, return an error
        if ($spoken == null || $text == null) {
            return response()->json([
                'status' => 'error',
                'message' => 'Could not generate ATIS.',
                'code' => 500,
                'data' => null
            ]);
        }

        // Return the ATIS
        return response()->json([
            'status' => 'success',
            'message' => 'ATIS generated successfully.',
            'code' => 200,
            'data' => [
                'spoken' => $spoken,
                'text' => $text,
            ]
        ]);
    }

    /**
     * Get Airport METAR.
     *
     * Gets the METAR for an airport and returns it in a JSON response.
     *
     * @param string $icao The ICAO code of the airport to get the METAR for.
     * @return JsonResponse
     */
    #[OpenApi\Operation(tags: ['Airport'])]
    #[OpenApi\Parameters(factory: \App\OpenApi\Parameters\GetAirportParameters::class)]
    public function metar(string $icao): JsonResponse
    {
        if (!Helpers::validateIcao($icao)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid ICAO code.',
                'code' => 400,
                'data' => null
            ]);
        }

        $metar = Helpers::fetch_metar($icao);

        // If the icao is not found in the response, return an error
        if ($metar == null) {
            return response()->json([
                'status' => 'error',
                'message' => 'Could not find METAR data for ' . strtoupper($icao) . '.',
                'code' => 404,
                'data' => null
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'METAR data retrieved successfully.',
            'code' => 200,
            'data' => [
                'metar' => $metar,
            ]
        ]);
    }

    /**
     * Get Airport TTS.
     *
     * Gets a link to a mp3 text-to-speech file for an airport and returns it in a JSON response.
     *
     * @param string $icao The ICAO code of the airport to generate the TTS for.
     * @param Request $request
     * @return void
     */
    #[OpenApi\Operation(tags: ['Text to Speech'])]
    #[OpenApi\Parameters(factory: \App\OpenApi\Parameters\GetAirportParameters::class)]
    public function textToSpeech(string $icao, Request $request): void
    {
        // TODO: Get mp3 atis file and return link and id.
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
    #[OpenApi\Parameters(factory: \App\OpenApi\Parameters\GetAirportParameters::class)]
    public function textToSpeechStore(string $icao, Request $request): JsonResponse
    {
        // Validate the request
        $atis = $request->atis;
        $ident = $request->ident;

        if (!isset($atis) || !isset($ident) || !isset($icao)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You must provide an ATIS, ATIS identifier, and ICAO code.',
                'code' => 400,
                'data' => null
            ]);
        }

        // Create the atis audio file
        $VOICE_RSS_API_KEY = config('app.voice-rss-key');
        $ch = curl_init("http://api.voicerss.org/?key=$VOICE_RSS_API_KEY&hl=en-us&c=MP3&v=John&f=16khz_16bit_stereo&src=" . rawurlencode($atis));
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
                return response()->json([
                    'status' => 'error',
                    'message' => 'Could not generate ATIS.',
                    'code' => 500,
                    'data' => null
                ]);
            }

            // Store the file url in the database, add the url to the response
            $atis_file->url = $file_url;
            $atis_file->update();

            // Return the response
            return response()->json([
                'status' => 'success',
                'message' => 'ATIS generated successfully.',
                'code' => 200,
                'data' => [
                    'file_id' => $file_id,
                    'file_name' => $name,
                    'file_url' => $file_url,
                ]
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Could not generate ATIS.',
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
    #[OpenApi\Parameters(factory: \App\OpenApi\Parameters\GetAirportParameters::class)]
    public function textToSpeechDestroy(string $icao, Request $request): void
    {
        // TODO: Delete mp3 atis file.
    }
}
