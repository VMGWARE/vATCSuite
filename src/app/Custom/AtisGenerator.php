<?php

namespace App\Custom;

use \App\Models\Airport;
use \App\Custom\Helpers;

class AtisGenerator
{
    private string $icao;
    private mixed $ident;
    private array $metar;
    private mixed $landing_runways;
    private mixed $departing_runways;
    private mixed $remarks1;
    private mixed $remarks2;
    private bool $ceiling = true;
    private array $parts = array();
    private mixed $override_runways;
    private mixed $output_type;
    private array $approaches;
    private array $weather_codes  = array(
        "VA"        => "volcanic ash",
        "HZ"        => "haze",
        "DU"        => "dust",
        "SA"        => "sand",
        "BLDU"        => "blowing dust",
        "BLSA"        => "blowing sand",
        "PO"        => "dust whirls",
        "SS"        => "sand storm",
        "BR"        => "mist",
        "TS"        => "thunderstorm",
        "SQ"        => "squall",
        "FC"        => "funnel cloud or tornado",
        "BLSN"        => "blowing snow",
        "DRSN"        => "drifting snow",
        "FG"        => "fog",
        "BC"        => "patchy fog",
        "FZFG"        => "freezing fog",
        "DZ"        => "drizzle",
        "FZDZ"        => "freezing drizzle",
        "DZRA"        => "drizzle and rain",
        "RA"        => "rain",
        "FZRA"        => "freezing rain",
        "RASN"        => "rain and snow mix",
        "SN"        => "snow",
        "SG"        => "snow grains",
        "IC"        => "ice crystals",
        "PE"        => "ice pellets",
        "PL"        => "ice pellets",
        "SHRA"        => "rain showers",
        "SHRASN"    => "rain and snow showers",
        "SHSN"        => "snow showers",
        "GR"        => "hail",
        "TSRA"        => "thunderstorm with rain",
        "TSGR"        => "thunderstorm with hail"
    );
    private array $sky_cover  = array(
        "BKN"   => "broken",
        "CLR"   => "sky clear",
        "FEW"   => "few clouds",
        "NCD"   => "no clouds detected",
        "OVC"   => "overcast",
        "SCT"   => "scattered clouds",
        "SKC"   =>   "sky clear"
    );
    private array $sky_type   = array(
        "CB"    => "cumulonimbus",
        "TCU"   => "towering cumulus"
    );
    private array $spoken_letters = array("a" => "alpha", "b" => "bravo", "c" => "charlie", "d" => "delta", "e" => "echo", "f" => "foxtrot", "g" => "golf", "h" => "hotel", "i" => "india", "j" => "juliet", "k" => "kilo", "l" => "lima", "m" => "mike", "n" => "november", "o" => "oscar", "p" => "papa", "q" => "quebec", "r" => "romeo", "s" => "sierra", "t" => "tango", "u" => "uniform", "v" => "victor", "w" => "whiskey", "x" => "x-ray", "y" => "yankee", "z" => "zulu");
    private array $spoken_numbers = array("0" => "zero", "1" => "one", "2" => "two", "3" => "three", "4" => "four", "5" => "five", "6" => "six", "7" => "seven", "8" => "eight", "9" => "niner");
    private array $spoken_runways = array("c" => "center", "l" => "left", "r" => "right");


    public function __construct($icao = null, $ident = null, $landing_runways = [], $departing_runways = [], $remarks1 = null, $remarks2 = null, $override_runways = null, $output_type = null, $approaches = [])
    {
        $this->icao                 = strtoupper($icao);
        $this->ident                = $ident;
        $this->landing_runways      = $landing_runways;
        $this->departing_runways    = $departing_runways;
        $this->remarks1             = $remarks1;
        $this->remarks2             = $remarks2;
        $this->override_runways     = $override_runways;
        $this->metar    = explode(" ", Helpers::fetch_metar($this->icao));
        $this->output_type          = $output_type;
        $this->approaches          = $approaches;
    }

    /**
     * It takes a string, splits it into an array, and then replaces each character with a spoken word
     *
     * @param mixed $part The part of the ICAO code to be spoken.
     * @param bool $runway If the part is a runway, set this to true.
     * @param bool $speak If true, the function will return the spoken version of the part.
     *
     * @return string the spoken version of the input.
     */
    private function spoken(mixed $part, bool $runway = false, bool $speak = false): string
    {
        if (!$speak) {
            return $part;
        }

        if (!$runway) {
            $characters = array_merge($this->spoken_letters, $this->spoken_numbers);
        } else {
            $characters = array_merge($this->spoken_runways, $this->spoken_numbers);
        }

        $output = array();
        $parts = str_split(strtolower($part));

        while (($part = current($parts)) !== false) {
            $output[] = $characters[$part];
            next($parts);
        }

        return implode(" ", $output);
    }

    /**
     * It takes the ICAO code and looks up the name of the airport in a database
     *
     * @param bool $speak true/false
     *
     * @return bool the name of the airport.
     */
    private function station_name(bool $speak = false): bool
    {
        if (!preg_match("@^([A-Z]{1}[A-Z0-9]{3})$@", $this->icao, $return) || isset($this->parts["station_name"])) {
            return false;
        }

        $icao   = strtoupper($return[1]);
        $airport = Airport::where('icao', $icao)->first();

        if (!$airport) {
            $this->parts["station_name"] = $this->spoken($icao, false, $speak);
            return true;
        }

        $this->parts["station_name"] = $airport->name;

        return true;
    }

    /**
     * This function adds the ATIS ident to the ATIS message.
     *
     * @param bool $speak Whether to speak the ATIS.
     *
     * @return bool the value of the variable ->parts["atis_ident"]
     */
    private function atis_ident(bool $speak = false): bool
    {
        if (isset($this->parts["atis_ident"])) {
            return false;
        }
        if ($this->output_type == "awos") {
            $this->parts["atis_ident"] = " automated weather observation";
        } else {
            $this->parts["atis_ident"] = " information " . $this->spoken($this->ident, false, $speak);
        }
        return true;
    }

    /**
     * This function takes the station name and the ATIS ident and combines them into one string.
     *
     * @param bool $speak true/false
     *
     * @return true.
     */
    private function station_info(bool $speak = false)
    {
        $this->station_name($speak);
        $this->atis_ident($speak);

        $this->parts["station_info"] = $this->parts["station_name"] . $this->parts["atis_ident"];

        unset($this->parts["station_name"]);
        unset($this->parts["atis_ident"]);

        return true;
    }

    /**
     * `zulu_time` is a function that takes a string and returns a string.
     *
     * The function is called `zulu_time` because it parses a string that represents a time in the Zulu
     * timezone.
     *
     * The function takes two arguments:
     *
     * 1. `` is a string that represents a time in the Zulu timezone.
     * 2. `` is a boolean that determines whether the function returns a string that is spoken or
     * written.
     *
     * The function returns a string that represents a time in the Zulu timezone.
     *
     * The function returns `false` if the string that is passed to it does not represent a time in the
     * Zulu timezone.
     *
     * The function returns `false` if the string that is passed to it represents a time in the Zulu
     * timezone, but the function
     *
     * @param mixed $part The part of the METAR to be parsed.
     * @param bool $speak true/false
     *
     * @return bool the value of the ->parts["zulu_time"] variable.
     */
    private function zulu_time(mixed $part, bool $speak = false)
    {
        if (!preg_match("@^([0-9]{2})([0-9]{4})(Z)$@", $part, $return) || isset($this->parts["pressure"])) {
            return false;
        }

        $this->parts["zulu_time"] = $this->spoken($return[2] . $return[3], false, $speak);

        return true;
    }

    /**
     * It takes a string, checks if it's a valid wind string, and if it is, it adds it to the
     * ->parts array.
     *
     * @param mixed $part The part of the METAR that is being parsed.
     * @param bool $speak true/false
     *
     * @return bool true/false
     */
    private function winds_basic(mixed $part, bool $speak = false)
    {
        if (!preg_match("@^([0-9]{3}|VRB)([0-9]{2,3})(G([0-9]{2,3}))?KT$@", $part, $return) || isset($this->parts["pressure"])) {
            return false;
        }

        $wind_parts = array();
        $prefix = "";

        if ($speak == true) {
            $wind_parts[] = "winnd";
        } else {
            $wind_parts[] = "wind";
        }

        if ($return[1] == "VRB") {
            $wind_parts[] = "variable at";
            $wind_parts[] = $return[2];
            $this->parts["winds"][] = implode(" ", $wind_parts);

            return true;
        }

        if ($return[1] == "000" || $return[2] < "3") {
            $wind_parts[] = "calm";
            $this->parts["winds"][] = implode(" ", $wind_parts);

            return true;
        }

        $wind_parts[] = $this->spoken($return[1], false, $speak) . " at";
        $wind_parts[] = $this->spoken(abs($return[2]), false, $speak);
        if (!isset($return[3]) || empty($return[3])) {
            $wind_parts[] = "knots";
        }
        $this->parts["winds"][] = implode(" ", $wind_parts);

        if (isset($return[3]) && !empty($return[3])) {
            $this->parts["winds"][] = "gusting " . $this->spoken($return[4], false, $speak) . " knots";
        }

        return true;
    }

    /**
     * If the string matches the pattern, then it's a variable wind direction
     *
     * @param mixed $part The part of the METAR that we're working with.
     * @param bool $speak true/false
     *
     * @return bool true/false
     */
    private function winds_variable(mixed $part, bool $speak = false)
    {
        if (!preg_match("@^([0-9]{3})V([0-9]{3})$@", $part, $return) || isset($this->parts["pressure"])) {
            return false;
        }

        $wind_parts     = array();
        $wind_dir_lo    = $this->spoken($return[1], false, $speak);
        $wind_dir_hi    = $this->spoken($return[2], false, $speak);

        $wind_parts[] = "variable between";
        $wind_parts[] = $wind_dir_lo . " and";
        $wind_parts[] = $wind_dir_hi . " degrees";

        $this->parts["winds"][] = implode(" ", $wind_parts);

        return true;
    }

    /**
     * > This function is a wrapper for the basic and variable functions
     *
     * @param mixed $part The part of the forecast you want to get.
     * @param bool $speak If true, the function will speak the wind direction.
     *
     * @return True
     */
    private function winds_full(mixed $part, bool $speak = false)
    {
        $this->winds_basic($part, $speak);
        $this->winds_variable($part, $speak);

        return true;
    }

    /**
     * If the input is CAVOK, then the output is CAVOK. If the input is a number between 0 and 999,
     * then the output is "visibility [number] meters". If the input is a number between 1000 and 9999,
     * then the output is "visibility [number] kilometers". If the input is a number between 1 and 10,
     * then the output is "visibility [number] miles". If the input is 10, then the output is
     * "visibility 10 or more miles". If the input is M, then the output is "visibility less than one
     * mile".
     *
     * @param mixed $part The part of the METAR to parse.
     * @param bool $speak true/false
     *
     * @return bool true/false
     */
    private function visibility(mixed $part, bool $speak = false)
    {
        if (!preg_match("@^(CAVOK|////|([0-9]{4})|([0-9]{1,2})(SM)?|(M)?(([1357])/(2|4|8|16)SM))$@", $part, $return) || isset($this->parts["visibility"])) {
            return FALSE;
        }

        if ($return[1] == "CAVOK") {
            $this->parts["visibility"] = "CAVOK";

            return true;
        }
        if (isset($return[2]) && !empty($return[2])) {
            if ($return[2] < 1000) {
                $return[2] = abs($return[2]);
                $this->parts["visibility"] = "visibility " . $return[2] . " meters";

                return true;
            }

            if ($return[2] < 1500) {
                $suffix = " kilometer";
            } else {
                $suffix = " kilometers";
            }

            $modifier = "";
            if ($return[2] == 9999) {
                $modifier = " or more";
            }

            $return[2] = round($return[2] / 1000);

            if ($return[2] < 10) {
                $return[2] = $this->spoken($return[2], false, $speak);
            }

            $this->parts["visibility"] = "visibility " . $return[2] . $modifier . $suffix;

            return true;
        }

        if (isset($return[3]) && !empty($return[3])) {
            if ($return[3] == 1) {
                $suffix = " mile";
            } else {
                $suffix = " miles";
            }

            $modifier = "";
            if ($return[3] == 10) {
                $modifier = " or more";
            }

            if ($return[3] < 10) {
                $return[3] = $this->spoken($return[3], false, $speak);
            }

            $this->parts["visibility"] = "visibility " . $return[3] . $modifier . $suffix;

            return true;
        }

        if (isset($return[6]) && !empty($return[6])) {
            $this->parts["visibility"] = "visibility less than one mile";

            return true;
        }
    }

    /**
     * If the input string matches the regex pattern, then the function returns true. Otherwise, it
     * returns false.
     *
     * The regex pattern is:
     *
     * `@^([-+])?(VC)?(" .  . ")$@`
     *
     * The `` variable is a string of pipe-separated weather codes.
     *
     * The `` variable is an array of the regex matches.
     *
     * The ``, ``, and `` variables are strings that are used to build the
     * final output.
     *
     * The `->parts["weather"]` array is used to store the final output.
     *
     * The `->weather_codes` array is used to translate the weather codes into human-readable
     * text.
     *
     * The `` variable is the input string.
     *
     * @param mixed $part The part of the METAR to be parsed.
     * @param bool $speak Whether or not to speak the weather.
     *
     * @return bool a boolean value.
     */
    private function weather(mixed $part, bool $speak = false)
    {
        $weather_codes = implode("|", array_keys($this->weather_codes));
        $severity   = "";
        $type       = "";
        $proximity  = "";

        if (!preg_match("@^([-+])?(VC)?(" . $weather_codes . ")$@", $part, $return)) {
            return false;
        }

        if (isset($return[1]) && !empty($return[1])) {
            if ($return[1] == "-") {
                $severity = "light ";
            } else {
                $severity = "heavy ";
            }
        }

        $type = $this->weather_codes[$return[3]];

        if (isset($return[2]) && !empty($return[2])) {
            $proximity = " in vicinity";
        }

        $this->parts["weather"][] = $severity . $type . $proximity;

        return true;
    }

    /**
     * It takes a string, checks if it's a valid vertical visibility, and if it is, it adds it to the
     * parts array.
     *
     * @param mixed $part The part of the METAR that we're trying to parse.
     * @param bool $speak If true, the function will return the spoken version of the part.
     *
     * @return float the visibility in feet.
     */
    private function vertical_visibility(mixed $part, bool $speak = false)
    {
        if (!preg_match("@^(VV)([0-9]{3})$@", $part, $return) || isset($this->parts["vertical_visibility"])) {
            return false;
        }

        $return[2] *= 100;

        $this->parts["vertical_visibility"] = "vertical visibility " . $return[2] . " feet";

        return true;
    }

    /**
     * The function takes a string, checks if it matches a pattern, and if it does, it adds the string
     * to an array
     *
     * @param mixed $part The part of the METAR that is being parsed.
     * @param bool $speak true/false
     *
     * @return bool boolean value.
     */
    public function sky_cover(mixed $part, bool $speak = false)
    {
        $sky_cover  = implode("|", array_keys($this->sky_cover));
        $sky_type   = implode("|", array_keys($this->sky_type));

        if (!preg_match("@^(" . $sky_cover . ")([0-9]{3})?(" . $sky_type . ")?$@", $part, $return) || isset($this->parts["pressure"])) {
            return false;
        }

        $sky_type = "";

        if (isset($return[3]) && !empty($return[3])) {
            $sky_type = " " . $this->sky_type[$return[3]];
        }

        if ($return[1] == "NCD" || $return[1] == "CLR" || $return[1] == "SKC") {
            $this->parts["sky_cover"] = $this->sky_cover[$return[1]];

            return true;
        }

        $return[2] *= 100;

        if ($return[2] > 8999 && $return[2] < 10000 && $speak == true) {
            $thou = substr($return[2], 0, 1);
            $hund = " " . substr($return[2], 1, 4);
            if ($hund == "000") {
                $hund = "";
            }
            $return[2] = $this->spoken($thou, false, true) . " thousand " . $hund;
        } elseif ($return[2] > 9999 && $speak == true) {
            $ten_thou = substr($return[2], 0, 2);
            $return[2] = $this->spoken($ten_thou, false, true) . " thousand";
        }

        if ($return[1] == "BKN" || $return[1] == "OVC") {
            if ($this->ceiling == true) {
                $this->parts["sky_cover"][] = "ceiling " . $return[2] . " " . $this->sky_cover[$return[1]];
                $this->ceiling = false;

                return true;
            }

            $this->parts["sky_cover"][] = $this->sky_cover[$return[1]] . " sky at " . $return[2];

            return true;
        }

        $this->parts["sky_cover"][] = $this->sky_cover[$return[1]] . " at " . $return[2] . $sky_type;

        return true;
    }

    /**
     * It takes a string like "M02/M04" and converts it to "minus two degrees Celsius, minus four
     * degrees Celsius"
     *
     * @param mixed $part The part of the METAR to be parsed.
     * @param bool $speak true/false
     *
     * @return bool a boolean value.
     */
    private function temperature_dewpoint(mixed $part, bool $speak = false)
    {
        if (!preg_match("@^(M)?([0-9]{2})/(M)?([0-9]{2})$@", $part, $return) || isset($this->parts["temperature_dewpoint"])) {
            return false;
        }

        $temperature    = array("temperature");
        $dewpoint       = array("dewpoint");

        if (isset($return[1]) && !empty($return[1])) {
            $temperature[] = "minus";
        }
        $temperature[] = $this->spoken(abs($return[2]), false, $speak);

        if (isset($return[3]) && !empty($return[3])) {
            $dewpoint[] = "minus";
        }
        $dewpoint[] = $this->spoken(abs($return[4]), false, $speak);

        $this->parts["temperature_dewpoint"][] = implode(" ", $temperature);
        $this->parts["temperature_dewpoint"][] = implode(" ", $dewpoint);

        return true;
    }

    /**
     * It takes a string, checks if it's a valid pressure, and if it is, it converts it to the other
     * type of pressure and stores it in an array.
     *
     * @param mixed $part The part of the METAR to be parsed.
     * @param bool $speak true/false
     *
     * @return true if the part is valid and false if it is not.
     */
    private function pressure(mixed $part, bool $speak = false)
    {
        if (!preg_match("@^(A|Q)([0-9]{4})$@", $part, $return) || isset($this->parts["pressure"])) {
            return false;
        }

        $inhg   = array();
        $qnh    = array();
        if ($return[1] == "A") {
            $inhg = $return[2];
            $qnh  = round(number_format($return[2] / 100, 2) *  33.86389);
            $this->parts["pressure"][] = "altimeter " . $this->spoken($inhg, false, $speak);
            $this->parts["pressure"][] = "qnh " . $this->spoken($qnh, false, $speak);

            return true;
        }

        $qnh  = $return[2];
        $inhg = round(($return[2] * 100) / 33.86);
        $this->parts["pressure"][] = "qnh " . $this->spoken($qnh, false, $speak);
        $this->parts["pressure"][] = "altimeter " . $this->spoken($inhg, false, $speak);

        return true;
    }

    /**
     * `approaches` takes an array of strings and returns a string.
     *
     * @param mixed $parts The array of parts that the parser has already parsed.
     *
     * @return bool a boolean value.
     */
    private function approaches(mixed $parts)
    {
        if (!isset($parts)) {
            return false;
        }

        if (sizeof($parts) == 1) {
            $this->parts["approaches"] = $parts[0] . " approaches in use";

            return true;
        } else {
            $this->parts["approaches"] = "simultaneous ils and visual approaches in use";

            return true;
        }
    }

    /**
     * It takes two arrays of runways, one for landing and one for departing, and returns a string of
     * the runways in a human readable format
     *
     * @param mixed $landing_runways An array of runways that are available for landing.
     * @param mixed $_POSTdeparting_runways array of runways that are departing
     * @param bool $speak true/false
     *
     * @return bool a boolean value.
     */
    private function runways(mixed $landing_runways, mixed $departing_runways, bool $speak = false)
    {
        if (isset($this->override_runways)) {
            return false;
        }

        $runways_out            = array();
        $landing_runways_out    = array();
        $departing_runways_out  = array();
        if ($landing_runways == $departing_runways) {
            $runways = array_unique(array_merge($landing_runways, $departing_runways));

            if (sizeof($runways) == 1) {
                $prefix = "landing and departing runway ";
            } else {
                $prefix = "landing and departing runways ";
            }

            while ($runway = current($runways)) {
                $runways_out[] = $this->spoken($runway, true, $speak);
                next($runways);
            }

            $this->parts["runways"] = $prefix . implode(", ", $runways_out);

            return true;
        }

        if (sizeof($landing_runways) == 1) {
            $prefix1 = "landing runway ";
        } else {
            $prefix1 = "landing runways ";
        }

        if (sizeof($departing_runways) == 1) {
            $prefix2 = "departing runway ";
        } else {
            $prefix2 = "departing runways ";
        }

        while ($runway = current($landing_runways)) {
            $landing_runways_out[] = $this->spoken($runway, true, $speak);
            next($landing_runways);
        }

        while ($runway = current($departing_runways)) {
            $departing_runways_out[] = $this->spoken($runway, true, $speak);
            next($departing_runways);
        }

        $this->parts["landing_runways"]      = $prefix1 . implode(", ", $landing_runways_out);
        $this->parts["departing_runways"]    = $prefix2 . implode(", ", $departing_runways_out);

        return true;
    }

    /**
     * `remarks1` is a private function that takes a mixed variable as a parameter and returns a
     * boolean value.
     *
     * @param mixed $parts The parts of the email address.
     */
    private function remarks1(mixed $parts)
    {
        if (!isset($parts) || empty($parts)) {
            return false;
        }

        $this->parts["remarks1"] = $this->remarks1;

        return true;
    }

    /**
     * It takes an array of strings and joins them together with a period.
     *
     * @param mixed $parts An array of strings that will be joined together with a period.
     *
     * @return mixed a boolean value.
     */
    private function remarks2(mixed $parts)
    {
        if (!isset($parts) || empty($parts)) {
            return false;
        }

        $this->parts["remarks2"] = implode(". ", $parts);

        return true;
    }

    /**
     * "advise controller on initial contact that you have information " . ->spoken(->ident,
     * false, );
     *
     * @param bool $speak true/false
     *
     * @return bool a boolean value.
     */
    private function ident_end(bool $speak = false)
    {
        if (!isset($this->ident)) {
            return false;
        }

        $this->parts["ident_end"] = "advise controller on initial contact that you have information " . $this->spoken($this->ident, false, $speak);

        return true;
    }

    /**
     * It takes a METAR string and parses it into an ATIS string
     *
     * @param mixed $speak The language to speak in.
     *
     * @return string the array.
     */
    public function parse_atis(mixed $speak)
    {
        if ($this->output_type == "atis") {
            $metar = $this->metar;
            $this->station_info($speak);
            while ($part = current($metar)) {
                $this->zulu_time($part, $speak);
                $this->winds_full($part, $speak);
                $this->visibility($part, $speak);
                $this->weather($part);
                $this->vertical_visibility($part, $speak);
                $this->sky_cover($part, $speak);
                $this->temperature_dewpoint($part, $speak);
                $this->pressure($part, $speak);
                next($metar);
            }

            $this->approaches($this->approaches);
            $this->runways($this->landing_runways, $this->departing_runways, $speak);
            $this->remarks1($this->remarks1);
            $this->remarks2($this->remarks2);
            $this->ident_end($speak);
        } elseif ($this->output_type == "awos") {
            $metar = $this->metar;
            $this->station_info($speak);
            while ($part = current($metar)) {
                $this->zulu_time($part, $speak);
                $this->winds_full($part, $speak);
                $this->visibility($part, $speak);
                $this->weather($part);
                $this->vertical_visibility($part, $speak);
                $this->sky_cover($part, $speak);
                $this->temperature_dewpoint($part, $speak);
                $this->pressure($part, $speak);
                next($metar);
            }
        } else {
            return false;
        }

        $output = array();
        while ($part = current($this->parts)) {
            $output[] = preg_replace("@\s+@", " ", strtoupper(Helpers::merge_recursive($part)) . "... ");
            next($this->parts);
        }

        return implode(" ", $output);
    }
}
