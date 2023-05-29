<?php

namespace App\Custom;

use Illuminate\Support\Facades\Cache;
use \App\Models\Airport;

/**
 * Helper functions.
 */
class Helpers
{
    /**
     * It returns the shortest angle between two angles.
     * 
     * @param mixed $angle_start The starting angle of the needle.
     * @param mixed $angle_target The angle you want to rotate to.
     * 
     * @return mixed difference between the two angles.
     */
    public static function get_angle_diff(mixed $angle_start, mixed $angle_target)
    {
        $delta = intval($angle_target) - intval($angle_start);
        $direction = ($delta > 0) ? -1 : 1;
        $delta1 = abs($delta);
        $delta2 = 360 - $delta1;
        return $direction * ($delta1 < $delta2 ? $delta1 : $delta2);
    }

    /**
     * The function fetches the METAR data for a given airport (specified by its ICAO code).
     * 
     * @param string $icao The ICAO code of an airport.
     * 
     * @return null|string Returns the METAR data for a given airport (specified by its ICAO code). Null if the metar data is not found.
     */
    public static function fetch_metar($icao)
    {
        // Cache the METAR data for 30 seconds
        if (Cache::has('metar_' . $icao)) {
            $metar_data = Cache::get('metar_' . $icao);
        } else {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://tgftp.nws.noaa.gov/data/observations/metar/stations/" . strtoupper($icao) . ".TXT");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $exec = curl_exec($ch);
            curl_close($ch);

            // If the icao is not found in the response, return an error
            if (strpos($exec, "Not Found") !== false || strpos($exec, strtoupper($icao)) === false) {
                return null;
            }

            $lines = explode("\n", $exec);

            $metar_data = trim($lines[1]);

            Cache::put('metar_' . $icao, $metar_data, 30);
        }

        return $metar_data;
    }

    /**
     * Validate ICAO code
     *
     * @param string $icao The ICAO code to validate
     * @return boolean Returns true if ICAO code is valid, false if not
     */
    public static function validateIcao($icao)
    {
        if (strlen($icao) != 4) {
            return false;
        }

        if (!preg_match("@^[a-z0-9]+$@i", $icao)) {
            return false;
        }

        return true;
    }

    /**
     * The function decodes a METAR string and returns an array of wind speed and direction
     * information.
     * 
     * @param metar The input parameter is a string containing a METAR (Meteorological Terminal
     * Aviation Routine Weather Report) which is a format used for reporting weather information for
     * aviation purposes.
     * 
     * @return array an array containing the wind direction, wind speed, and gust speed (if present) parsed
     * from the METAR string.
     */
    public static function get_wind($metar)
    {
        $metar = explode(" ", $metar);

        while ($part = current($metar)) {
            if (!preg_match("@^([0-9]{3}|VRB)([0-9]{2,3})(G([0-9]{2,3}))?KT$@", $part, $return)) {
                next($metar);
                continue;
            }

            return [
                "dir" => $return[1],
                "speed" => $return[2],
                "gust_speed" => $return[4] ?? null
            ];
        }
    }

    /**
     * It takes an array and returns a string of the array's values separated by commas.
     * 
     * @param mixed $part The part of the array to merge.
     * 
     * @return string the value of the variable.
     */
    public static function merge_recursive(mixed $part)
    {
        if (!is_array($part)) {
            return $part;
        }
        return implode(", ", $part);
    }

    /**
     * The function parses the runways of an airport and calculates the difference between the wind
     * direction and the runway heading.
     * 
     * @param string $icao The ICAO code of an airport.
     * @param mixed $wind_dir The wind direction in degrees or "VRB" (variable).
     * 
     * @return array an array of runway information for a given airport (specified by its ICAO code) and wind
     * direction. The array includes information such as the runway identifier, runway heading, wind
     * direction, and the difference between the wind direction and the runway heading. The array is
     * sorted in ascending order based on the wind difference.
     */
    public static function parse_runways($icao, $wind_dir)
    {
        $result = Airport::where('icao', $icao)->pluck('runways');

        $runways = explode(",", $result[0]);
        $output = array();

        $i = 0;
        while ($i < sizeof($runways)) {
            $runway_hdg = str_pad(substr($runways[$i], 0, 2), 3, "0");

            if ($wind_dir == "0" || $wind_dir == "VRB") {
                $wind_dir = "-";
                $wind_diff = "-";
                $no_val = true;
            } else {
                $wind_dir = $wind_dir;
                $wind_diff = abs(Helpers::get_angle_diff($wind_dir, $runway_hdg));
                $no_val = false;
            }

            $output[$i]["runway"] = $runways[$i];
            $output[$i]["runway_hdg"] = $runway_hdg;
            $output[$i]["wind_dir"] = $wind_dir;
            $output[$i]["wind_diff"] = $wind_diff;
            $i++;
        }

        $select_diff = array_column($output, "wind_diff");
        array_multisort($select_diff, SORT_ASC, $output);

        return $output;
    }
}
