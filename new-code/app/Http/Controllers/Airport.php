<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Models\Airport as AirportModel;
use Illuminate\Support\Facades\Cache;
use \App\Custom\AtisGenerator;
use \App\Custom\Helpers;

class Airport extends Controller
{
    public function index($icao, Request $request)
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

    public function all(Request $request)
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

    public function runways($icao)
    {
        if (!Helpers::validateIcao($icao)) {
            if (request()->query('res') == 'html') {
                return view('partials.failed', [
                    'message' => 'Invalid ICAO code.',
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid ICAO code.',
                    'code' => 400,
                    'data' => null
                ]);
            }
        }

        $airport = AirportModel::where('icao', strtoupper($icao))->first();

        if (!$airport) {
            if (request()->query('res') == 'html') {
                return view('partials.failed', [
                    'message' => 'Could not find airport with ICAO code <strong>' . strtoupper($icao) . '</strong>.',
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Could not find airport with ICAO code ' . strtoupper($icao) . '.',
                    'code' => 404,
                    'data' => null
                ]);
            }
        }

        $metar = Helpers::fetch_metar($icao);
        if ($metar == null) {
            if (request()->query('res') == 'html') {
                return view('partials.failed', [
                    'message' => 'AviationWeather.gov does not have any weather information available for <strong>' . strtoupper($icao) . '</strong>. Please try again with a different airport.',
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Could not find METAR data for ' . strtoupper($icao) . '.',
                    'code' => 404,
                    'data' => null
                ]);
            }
        }
        $wind = Helpers::get_wind($metar);
        $runways = Helpers::parse_runways($icao, $wind['dir']);

        if (request()->query('res') == 'html') {
            return view('partials.runways', [
                'airport' => $airport,
                'metar' => $metar,
                'wind' => $wind,
                'runways' => $runways,
            ]);
        } else {
            return response()->json([
                'status' => 'success',
                'message' => 'Runways retrieved successfully.',
                'code' => 200,
                'data' => [
                    'runways' => $runways,
                ]
            ]);
        }
    }

    public function atis($icao, Request $request)
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

        // If the icao is not found in the response, return an error
        if ($metar == null) {
            return response()->json([
                'status' => 'error',
                'message' => 'Could not find METAR data for ' . strtoupper($icao) . '.',
                'code' => 404,
                'data' => null
            ]);
        }

        if (!isset($request->landing_runways) || !isset($request->departure_runways)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You must select at least one landing and departing runway to generate your ATIS.',
                'code' => 400,
                'data' => null
            ]);
        }

        // TODO: Generate ATIS from METAR
        $spoken_atis = new AtisGenerator($icao, $request->ident, $request->landing_runways, $request->departure_runways, $request->remarks_1, $request->remarks_2, $request->override_runway);
        $text_atis = new AtisGenerator($icao, $request->ident, $request->landing_runways, $request->departure_runways, $request->remarks_1, $request->remarks_2, $request->override_runway);

        return response()->json([
            'status' => 'success',
            'message' => 'ATIS generated successfully.',
            'code' => 200,
            'data' => [
                'spoken' => $spoken_atis->parse_atis(true),
                'text' => $text_atis->parse_atis(false),
            ]
        ]);
    }

    public function metar($icao)
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
