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
     * Airport Information Retrieval Endpoint.
     *
     * This method provides an endpoint to fetch detailed information about an airport based on its ICAO code. In addition 
     * to basic airport details, the method attempts to fetch and provide the METAR data for the given airport, which includes 
     * current weather conditions. Additionally, based on the METAR wind data, it provides information on the relevant runways 
     * at the airport.
     *
     * @param string $icao The ICAO code representing the airport. This should be a valid four-character ICAO identifier.
     *
     * @return JsonResponse A structured JSON response containing:
     *                      - 'status': Indicates the result of the request, either 'success' or 'error'.
     *                      - 'message': A brief description about the outcome of the request.
     *                      - 'code': HTTP status code reflecting the result.
     *                      - 'data': An array of information about the airport including:
     *                                 * 'airport': Detailed information about the airport fetched from the database.
     *                                 * 'metar': The METAR data for the given airport, containing weather conditions.
     *                                 * 'wind': An array containing wind speed and direction.
     *                                 * 'runways': A list of relevant runways based on wind direction.
     */
    #[OpenApi\Operation(tags: ['Airport'])]
    #[OpenApi\Parameters(factory: GetAirportParameters::class)]
    #[OpenApi\Response(factory: ErrorValidatingIcaoResponse::class, statusCode: 400)]
    #[OpenApi\Response(factory: GetAirportResponse::class, statusCode: 200)]
    public function index(string $icao): JsonResponse
    {
        if (!Helpers::validateIcao($icao)) {
            return Helpers::response('Invalid ICAO code.', null, 400, 'error');
        }

        $airport = AirportModel::where('icao', strtoupper($icao))->first();
        if (!$airport) {
            return Helpers::response('Could not locate airport with ICAO code ' . strtoupper($icao) . ' in the database.', null, 404, 'error');
        }

        $metar = Helpers::fetch_metar($icao);
        if ($metar == null) {
            $wind = null;
            $runways = null;
        } else {
            $wind = Helpers::get_wind($metar);
            $runways = Helpers::parse_runways($icao, $wind['dir']);
        }

        return Helpers::response('Airport retrieved successfully.', [
            'airport' => $airport,
            'metar' => $metar,
            'wind' => $wind,
            'runways' => $runways,
        ]);
    }

    /**
     * Fetch All Airports Endpoint.
     *
     * This endpoint retrieves a comprehensive list of all airports stored in the database. For improved readability 
     * and compactness, the timestamps (i.e., 'created_at' and 'updated_at') associated with each airport record are 
     * excluded from the response.
     *
     * @return JsonResponse A structured JSON response containing:
     *                      - 'status': Always 'success' for this endpoint given its nature.
     *                      - 'message': A brief description of the outcome, indicating successful retrieval.
     *                      - 'code': HTTP status code, 200 indicating success.
     *                      - 'data': An array of airports, each with its attributes minus the timestamps.
     */
    #[OpenApi\Operation(tags: ['Airport'])]
    #[OpenApi\Response(factory: GetAllAirportsResponse::class, statusCode: 200)]
    public function all(): JsonResponse
    {
        $airports = AirportModel::all()->makeHidden(['created_at', 'updated_at']);
        return Helpers::response('Airports retrieved successfully.', $airports);
    }

    /**
     * Fetch Airport Runways Endpoint.
     *
     * This endpoint fetches runway information for a specified airport using its ICAO code. It makes use of METAR 
     * data to assist in providing detailed runway info.
     * 
     * Note: The METAR data is essential for fetching detailed runway information. If METAR data isn't available, 
     * the response will reflect that.
     *
     * @param string $icao The ICAO code of the desired airport.
     * @return JsonResponse A structured JSON response containing:
     *                      - 'status': Can be 'success' if retrieval was successful or 'error' otherwise.
     *                      - 'message': A brief description of the outcome.
     *                      - 'code': HTTP status code, which varies based on the outcome.
     *                      - 'data': The runway information or null if an error occurred.
     */
    #[OpenApi\Operation(tags: ['Airport'])]
    #[OpenApi\Parameters(factory: GetAirportParameters::class)]
    #[OpenApi\Response(factory: ErrorValidatingIcaoResponse::class, statusCode: 400)]
    #[OpenApi\Response(factory: RunwayResponse::class, statusCode: 200)]
    public function runways(string $icao): JsonResponse
    {
        if (!Helpers::validateIcao($icao)) {
            return Helpers::response('Invalid ICAO code.', null, 400, 'error');
        }

        $airport = AirportModel::where('icao', strtoupper($icao))->first();

        if (!$airport) {
            return Helpers::response('Could not find airport with ICAO code ' . strtoupper($icao) . '.', null, 404, 'error');
        }

        $metar = Helpers::fetch_metar($icao);
        if ($metar == null) {
            return Helpers::response('Could not find METAR data for ' . strtoupper($icao) . '.', null, 404, 'error');
        }
        $wind = Helpers::get_wind($metar);
        $runways = Helpers::parse_runways($icao, $wind['dir']);

        return Helpers::response('Runways retrieved successfully.', $runways);
    }

    /**
     * Retrieve ATIS Information for the Specified Airport.
     * 
     * This method generates and returns the ATIS (Automatic Terminal Information Service) 
     * information for an airport identified by its ICAO code. The ATIS data is provided 
     * in two formats: spoken and text. Input parameters, such as the airport's ICAO code 
     * and related request details (e.g., landing and departing runways, remarks), drive 
     * the generation of the ATIS.
     * 
     * @param string $icao     The ICAO code for which to generate the ATIS.
     * @param Request $request Contains various parameters necessary for ATIS generation 
     *                         such as selected runways, remarks, and more.
     * 
     * @return JsonResponse    A JSON formatted response which contains the generated ATIS 
     *                         data in both spoken and text formats.
     * 
     * @throws ValidationException If the provided ICAO code or any other required parameter is invalid.
     * 
     * @see AtisGenerator For details on how ATIS is generated.
     * 
     * @apiNote Ensure that the ICAO code and other request details are valid before making 
     *          the request. In the event of any error, appropriate status and error messages 
     *          will be returned.
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
            return Helpers::response('Invalid ICAO code.', null, 400, 'error');
        }

        // Get the airport from the database
        $airport = AirportModel::where('icao', strtoupper($icao))->first();

        // If the airport is not found in the database, return an error
        if (!$airport) {
            return Helpers::response('Could not find airport with ICAO code ' . strtoupper($icao) . ' in the database.', null, 404, 'error');
        }

        // Fetch the METAR data for the airport
        $metar = Helpers::fetch_metar($icao);

        // Ensure that the METAR data was found
        if ($metar == null && $request->metar == null) {
            return Helpers::response('Could not find METAR data for ' . strtoupper($icao) . '.', null, 404, 'error');
        }

        // Get the wind data from the METAR
        if ($request['output-type'] == 'atis') {
            if (!isset($request->landing_runways) || !isset($request->departing_runways)) {
                return Helpers::response('You must select at least one landing and departing runway to generate your ATIS.', null, 400, 'error');
            }
        }

        // Override the runway if requested
        if ($request['output-type'] == 'awos') {
            $request['override_runways'] = true;
        }

        // Validate ATIS identifier
        if (!isset($request->ident) || !ctype_alpha($request->ident)) {
            return Helpers::response('You must provide an ATIS identifier.', null, 400, 'error');
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
            metar: $request->metar,
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
            metar: $request->metar,
        );

        // Generate the ATIS
        $spoken = $spoken_atis->parse_atis(true);
        $text = $text_atis->parse_atis(false);

        // If the ATIS could not be generated, return an error
        if ($spoken == null || $text == null) {
            return Helpers::response('Could not generate ATIS/AWOS.', null, 500, 'error');
        }

        // Return the ATIS
        return Helpers::response('ATIS/AWOS generated successfully.', [
            'spoken' => $spoken,
            'text' => $text,
        ]);
    }

    /**
     * Retrieve METAR Data for the Specified Airport.
     * 
     * This method fetches the METAR (Meteorological Aerodrome Report) for an airport, which provides 
     * current weather conditions at that airport. The report is identified using the airport's ICAO code.
     * 
     * @param string $icao     The ICAO code representing the airport for which the METAR data is desired.
     * 
     * @return JsonResponse    A JSON formatted response containing either the METAR data or an appropriate error message.
     * 
     * @throws ValidationException If the provided ICAO code is invalid.
     * 
     * @see Helpers::fetch_metar For details on how METAR data is fetched.
     * 
     * @apiNote Ensure the ICAO code is valid and represents a recognized airport before making 
     *          the request. If METAR data is not available for the specified airport, an error will be returned.
     */
    #[OpenApi\Operation(tags: ['Airport'])]
    #[OpenApi\Parameters(factory: GetAirportParameters::class)]
    #[OpenApi\Response(factory: ErrorValidatingIcaoResponse::class, statusCode: 400)]
    #[OpenApi\Response(factory: \App\OpenApi\Responses\Airport\MetarResponse::class, statusCode: 200)]
    #[OpenApi\Response(factory: \App\OpenApi\Responses\Airport\MetarNotFoundResponse::class, statusCode: 404)]
    public function metar(string $icao): JsonResponse
    {
        if (!Helpers::validateIcao($icao)) {
            return Helpers::response('Invalid ICAO code.', null, 400, 'error');
        }

        $metar = Helpers::fetch_metar($icao);

        // If the icao is not found in the response, return an error
        if ($metar == null) {
            return Helpers::response('Could not find METAR data for the requested airport.', null, 404, 'error');
        }

        return Helpers::response('METAR data retrieved successfully.', [
            'metar' => $metar,
        ]);
    }
}
