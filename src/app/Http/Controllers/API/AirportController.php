<?php

namespace App\Http\Controllers\API;

use App\Custom\AtisGenerator;
use App\Custom\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Airport as AirportModel;
use App\OpenApi\Parameters\GetAirportParameters;
use App\OpenApi\Responses\Airport\GetAirportResponse;
use App\OpenApi\Responses\Airport\GetAllAirportsResponse;
use App\OpenApi\Responses\Airport\RunwayResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Vyuldashev\LaravelOpenApi\Attributes as OpenApi;
use App\OpenApi\RequestBodies\Airport\GenerateAtisRequestBody;
use App\OpenApi\Responses\Airport\AtisResponse;
use App\OpenApi\Responses\TTS\ErrorValidatingIcaoResponse;

#[OpenApi\PathItem]
class AirportController extends Controller
{
    /**
     * Get Airport.
     *
     * Gets an airport from the database and returns it in a JSON response.
     *
     * @param string $icao The ICAO code of the airport to get.
     * @return JsonResponse
     */
    #[OpenApi\Operation(tags: ['Airport'])]
    #[OpenApi\Parameters(factory: GetAirportParameters::class)]
    #[OpenApi\Response(factory: ErrorValidatingIcaoResponse::class, statusCode: 400)]
    #[OpenApi\Response(factory: GetAirportResponse::class, statusCode: 200)]
    public function index(string $icao): JsonResponse
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
     * @return JsonResponse
     */
    #[OpenApi\Operation(tags: ['Airport'])]
    #[OpenApi\Response(factory: GetAllAirportsResponse::class, statusCode: 200)]
    public function all(): JsonResponse
    {
        $airports = AirportModel::all()->makeHidden(['created_at', 'updated_at']);
        return response()->json([
            'status' => 'success',
            'message' => 'Airports retrieved successfully.',
            'code' => 200,
            'data' => $airports,
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
    #[OpenApi\Parameters(factory: GetAirportParameters::class)]
    #[OpenApi\Response(factory: ErrorValidatingIcaoResponse::class, statusCode: 400)]
    #[OpenApi\Response(factory: RunwayResponse::class, statusCode: 200)]
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
            'data' => $runways,
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
    #[OpenApi\Parameters(factory: GetAirportParameters::class)]
    #[OpenApi\RequestBody(factory: GenerateAtisRequestBody::class)]
    #[OpenApi\Response(factory: ErrorValidatingIcaoResponse::class, statusCode: 400)]
    #[OpenApi\Response(factory: AtisResponse::class, statusCode: 200)]
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
                'code' => 4041,
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
                'code' => 4042,
                'data' => null
            ]);
        }

        // Get the wind data from the METAR
        if($request->output-type == "atis"){
            if (!isset($request->landing_runways) || !isset($request->departing_runways)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You must select at least one landing and departing runway to generate your ATIS.',
                    'code' => 400,
                    'data' => null
                ]);
            }
        }

        // If the runways are not an array, return an error
        if (!is_array($request->landing_runways) || !is_array($request->departing_runways)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Runways must be an array.',
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
        $spoken_atis = new AtisGenerator(
            $icao,
            ident: $request->ident,
            landing_runways: $request->landing_runways,
            departing_runways: $request->departing_runways,
            remarks1: $request->remarks1,
            remarks2: $request->remarks2,
            override_runways: $request->override_runway
        );
        $text_atis = new AtisGenerator(
            $icao,
            ident: $request->ident,
            landing_runways: $request->landing_runways,
            departing_runways: $request->departing_runways,
            remarks1: $request->remarks1,
            remarks2: $request->remarks2,
            override_runways: $request->override_runway
        );

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
    #[OpenApi\Parameters(factory: GetAirportParameters::class)]
    #[OpenApi\Response(factory: ErrorValidatingIcaoResponse::class, statusCode: 400)]
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
}
