<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Vyuldashev\LaravelOpenApi\Attributes as OpenApi;
use App\Custom\Helpers;

#[OpenApi\PathItem]
class PDCController extends Controller
{

    /**
     * Generate a Pre-Departure Clearance (PDC) for a flight.
     * 
     * This endpoint generates a PDC for a flight based on the provided parameters.
     *
     * @param Request $request An instance of the HTTP request which should contain both 'icao' and 'id' parameters.
     * @return JsonResponse A JSON response. It can be:
     *                      1. A success response with the generated PDC.
     *                      2. An error response indicating an error with the request.
     */
    #[OpenApi\Operation(tags: ['Miscellaneous'])]
    #[OpenApi\RequestBody(factory: \App\OpenApi\RequestBodies\PDC\GenerateRequestBody::class)]
    #[OpenApi\Response(factory: \App\OpenApi\Responses\PDC\FailureResponse::class, statusCode: 400)]
    #[OpenApi\Response(factory: \App\OpenApi\Responses\PDC\SuccessResponse::class, statusCode: 200)]
    public function generate(Request $request)
    {
        // Define validation rules for the request data
        $rules = [
            'callsign' => 'required|string',
            'departure' => 'required|string',
            'arrival' => 'required|string',
            'route' => 'required|string',
            'aircraft' => 'required|string',
            'altitude' => 'required|string',
            'squawk' => 'required|string',
            'departure_time' => 'required|date_format:Y-m-d\TH:i:s\Z',
            'remarks' => 'nullable|string',
        ];

        // Validate the request data
        $validator = Validator::make($request->all(), $rules);

        // Check if validation fails
        if ($validator->fails()) {
            return Helpers::response('Validation failed', ['error' => $validator->errors()], 400);
        }

        // Get request data
        $callsign = $request->input('callsign');
        $departure = $request->input('departure');
        $arrival = $request->input('arrival');
        $route = $request->input('route');
        $aircraft = $request->input('aircraft');
        $altitude = $request->input('altitude');
        $squawk = $request->input('squawk');
        $departure_time = $request->input('departure_time');
        $remarks = $request->input('remarks');

        // Build PDC
        $pdc = "PDC FOR $callsign\n";
        $pdc .= "DEPARTING $departure\n";
        $pdc .= "ARRIVING $arrival\n";
        $pdc .= "VIA $route\n";
        $pdc .= "AIRCRAFT $aircraft\n";
        $pdc .= "ALTITUDE $altitude\n";
        $pdc .= "SQUAWK $squawk\n";
        $pdc .= "DEPARTURE TIME $departure_time\n";
        $pdc .= "REMARKS $remarks\n";
        $pdc .= "END PDC";

        return Helpers::response('PDC generated successfully', ['pdc' => $pdc], 200);
    }
}
