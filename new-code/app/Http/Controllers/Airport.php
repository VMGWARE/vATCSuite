<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class Airport extends Controller
{
    public function __construct()
    {
    }

    public function index($icao, Request $request)
    {
        // TODO: Return airport data from database
    }

    public function runways($icao, Request $request)
    {
        // TODO: Return runway data from database
    }

    public function atis($icao, Request $request)
    {
        // TODO: Generate ATIS from METAR
    }

    public function metar($icao, Request $request)
    {
        if (!$this->validateIcao($icao)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid ICAO code.',
                'code' => 400,
                'data' => null
            ]);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://tgftp.nws.noaa.gov/data/observations/metar/stations/" . strtoupper($icao) . ".TXT");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $exec = curl_exec($ch);
        curl_close($ch);

        // If the icao is not found in the response, return an error
        if (!strpos($exec, strtoupper($icao))) {
            return response()->json([
                'status' => 'error',
                'message' => 'Could not find METAR data for ' . strtoupper($icao) . '.',
                'code' => 404,
                'data' => null
            ]);
        }

        $lines = explode("\n", $exec);

        return response()->json([
            'status' => 'success',
            'message' => 'METAR data retrieved successfully.',
            'code' => 200,
            'data' => [
                'icao' => strtoupper($icao),
                'metar' => trim($lines[1])
            ]
        ]);
    }

    /**
     * Validate ICAO code
     *
     * @param string $icao The ICAO code to validate
     * @return boolean Returns true if ICAO code is valid, false if not
     */
    private function validateIcao($icao)
    {
        if (strlen($icao) != 4) {
            return false;
        }

        if (!preg_match("@^[a-z0-9]+$@i", $icao)) {
            return false;
        }

        return true;
    }
}
