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
        // TODO: Fetch METAR from NOAA
    }

    private function getAirport($icao)
    {
        // TODO: Get airport data from database
    }
}
