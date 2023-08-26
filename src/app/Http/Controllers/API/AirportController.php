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
     * Get Airport Information.
     *
     * Retrieves detailed information about an airport using its ICAO code and returns it in a JSON response.
     *
     * @param string $icao The ICAO code of the airport to fetch information for.
     * @return JsonResponse Returns a JSON response containing the airport information.
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
     * Retrieves a list of all airports from the database and returns them in a JSON response.
     *
     * @return JsonResponse Returns a JSON response containing the list of airports.
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
     * Retrieves the runways information for an airport using its ICAO code and returns them in a JSON response.
     *
     * @param string $icao The ICAO code of the airport to fetch runways information for.
     * @return JsonResponse Returns a JSON response containing the runways information.
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
     * Returns ATIS for the specified airport in spoken and text format
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
        if ($request['output-type'] == 'atis') {
            if (!isset($request->landing_runways) || !isset($request->departing_runways)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You must select at least one landing and departing runway to generate your ATIS.',
                    'code' => 400,
                    'data' => null
                ]);
            }
        }

        // Override the runway if requested
        if ($request['output-type'] == 'awos') {
            $request['override_runways'] = true;
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

        // Define the ATIS generator for spoken and text ATIS
        $spoken_atis = new AtisGenerator(
            $icao,
            ident: $request->ident,
            landing_runways: $request->landing_runways,
            departing_runways: $request->departing_runways,
            remarks1: $request->remarks1,
            remarks2: $request->remarks2,
            override_runways: $request->override_runways,
            output_type: $request['output-type'],
            approaches: $request->approaches,
        );
        $text_atis = new AtisGenerator(
            $icao,
            ident: $request->ident,
            landing_runways: $request->landing_runways,
            departing_runways: $request->departing_runways,
            remarks1: $request->remarks1,
            remarks2: $request->remarks2,
            override_runways: $request->override_runways,
            output_type: $request['output-type'],
            approaches: $request->approaches,
        );

        // Generate the ATIS
        $spoken = $spoken_atis->parse_atis(true);
        $text = $text_atis->parse_atis(false);

        // If the ATIS could not be generated, return an error
        if ($spoken == null || $text == null) {
            return response()->json([
                'status' => 'error',
                'message' => 'Could not generate ATIS/AWOS.',
                'code' => 500,
                'data' => null
            ]);
        }

        // Return the ATIS
        return response()->json([
            'status' => 'success',
            'message' => 'ATIS/AWOS generated successfully.',
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
     * Retrieves the METAR (Meteorological Aerodrome Report) for an airport using its ICAO code and returns it in a JSON response.
     *
     * @param string $icao The ICAO code of the airport to fetch METAR for.
     * @return JsonResponse Returns a JSON response containing the METAR data.
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
