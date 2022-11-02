<?php
class AtisGenerator{
    private $icao;
    private $ident;
    private $metar;
    private $landing_runways;
    private $departing_runways;
    private $remarks1;
    private $remarks2;
    private $ceiling = true;
    private $parts = array();
    private $override_runways;
    private $weather_codes  = array(
        "VA"		=> "volcanic ash",
		"HZ"		=> "haze",
		"DU"		=> "dust",
		"SA"		=> "sand",
        "BLDU"		=> "blowing dust",
		"BLSA"		=> "blowing sand",
		"PO"		=> "dust whirls",
		"SS"		=> "sand storm",
        "BR"		=> "mist",
		"TS"		=> "thunderstorm",
		"SQ"		=> "squall",
		"FC"		=> "funnel cloud or tornado",
		"BLSN"		=> "blowing snow",
        "DRSN"		=> "drifting snow",
		"FG"		=> "fog",
		"BC"		=> "patchy fog",
		"FZFG"		=> "freezing fog",
        "DZ"		=> "drizzle",
		"FZDZ"		=> "freezing drizzle",
		"DZRA"		=> "drizzle and rain",
		"RA"		=> "rain",
        "FZRA"		=> "freezing rain",
		"RASN"		=> "rain and snow mix",
		"SN"		=> "snow",
		"SG"		=> "snow grains",
        "IC"		=> "ice crystals",
		"PE"		=> "ice pellets",
		"PL"		=> "ice pellets",
		"SHRA"		=> "rain showers",
        "SHRASN"	=> "rain and snow showers",
		"SHSN"		=> "snow showers",
		"GR"		=> "hail",
		"TSRA"		=> "thunderstorm with rain",
        "TSGR"		=> "thunderstorm with hail");
    private $sky_cover  = array(
        "BKN"   => "broken",
        "CLR"   => "sky clear",
        "FEW"   => "few clouds",
        "NCD"   => "no clouds detected",
        "OVC"   => "overcast",
        "SCT"   => "scattered clouds",
        "SKC"   =>   "sky clear");
    private $sky_type   = array(
        "CB"    => "cumulonimbus",
        "TCU"   => "towering cumulus"
    );
    private $spoken_letters = array("a" => "alpha", "b" => "bravo", "c" => "charlie", "d" => "delta", "e" => "echo", "f" => "foxtrot", "g" => "golf", "h" => "hotel", "i" => "india", "j" => "juliet", "k" => "kilo", "l" => "lima", "m" => "mike", "n" => "november", "o" =>"oscar", "p" => "papa", "q" => "quebec", "r" => "romeo", "s" => "sierra", "t" => "tango", "u" => "uniform", "v" => "victor", "w" => "whiskey", "x" => "x-ray", "y" => "yankee", "z" => "zulu");
    private $spoken_numbers = array("0" => "zero", "1" => "one", "2" => "two", "3" => "three", "4" => "four", "5" => "five", "6" => "six", "7" => "seven", "8" => "eight", "9" => "niner");
    private $spoken_runways = array("c" => "center", "l" => "left", "r" => "right");

    
    function __construct(){
        $this->icao                 = strtoupper(preg_replace("@[^a-z0-9]@i", "", $_POST["icao"]));
        $this->ident                = $_POST["ident"];
        $this->landing_runways      = $_POST["landing_runways"];
        $this->departing_runways    = $_POST["departing_runways"];
        $this->remarks1             = (isset($_POST["remarks1"])) ? preg_replace("@[^0-9a-z\,\.\-\/\s]@i", "", $_POST["remarks1"]) : null;
        $this->remarks2             = (isset($_POST["remarks2"]) && !empty($_POST["remarks2"])) ? $_POST["remarks2"] : null;
        $this->override_runways     = (isset($_POST["override-runways"])) ? $_POST["override-runways"] : null;
        $this->metar    = explode(" ", $this->url_get_contents($this->icao));
    }
    
    private function url_get_contents($str){
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,"https://tgftp.nws.noaa.gov/data/observations/metar/stations/" . strtoupper($str) . ".TXT");
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        $exec = curl_exec($ch);
        curl_close($ch);
        
        $lines = explode("\n", $exec);
        
        return trim($lines[1]);
    }
    
    private function merge_recursive($part){
        if(!is_array($part)){
            return $part;
        }
        return implode(", ", $part);
    }
    
    private function spoken($part, $runway = false, $speak = false){
        if($speak == false){
            return $part;
        }
        
        if($runway == false){
            $characters = array_merge($this->spoken_letters, $this->spoken_numbers);
        }
        else{
            $characters = array_merge($this->spoken_runways, $this->spoken_numbers);
        }
        
        $output = array();
        $parts = str_split(strtolower($part));
        
        while(($part = current($parts)) !== false){
            $output[] = $characters[$part];
            next($parts);
        }
        
        return implode(" ", $output);
    }
    
    private function station_name($speak = false){
        if(!preg_match("@^([A-Z]{1}[A-Z0-9]{3})$@", $this->icao, $return) || isset($this->parts["station_name"])){
            return false;
        }
        
        $icao   = strtoupper($return[1]);
        $mysqli = new mysqli('HOST','USERNAME','PASSWORD','DATABASE');
        $query  = $mysqli->query("SELECT name FROM airports WHERE icao ='" . $icao . "' LIMIT 1");
        $result = $query->fetch_row();
        
        if(!$result){
            $this->parts["station_name"] = $this->spoken($icao, false, $speak);
            return true;
        }
        
        $this->parts["station_name"] = $result[0];
        
        return true;
    }
    
    private function atis_ident($speak = false){
        if(isset($this->parts["atis_ident"])){
            return false;
        }
        
        $this->parts["atis_ident"] = " information " . $this->spoken($this->ident, false, $speak);
        
        return true;
    }
    
    private function station_info($speak = false){
        $this->station_name($speak);
        $this->atis_ident($speak);
        
        $this->parts["station_info"] = $this->parts["station_name"] . $this->parts["atis_ident"];
        
        unset($this->parts["station_name"]);
        unset($this->parts["atis_ident"]);

        return true;
    }
    
    private function zulu_time($part, $speak = false){
        if(!preg_match("@^([0-9]{2})([0-9]{4})(Z)$@", $part, $return) || isset($this->part["pressure"])){
            return false;
        }
        
        $this->parts["zulu_time"] = $this->spoken($return[2].$return[3], false, $speak);
        
        return true;
    }
    
    private function winds_basic($part, $speak = false){
        if(!preg_match("@^([0-9]{3}|VRB)([0-9]{2,3})(G([0-9]{2,3}))?KT$@", $part, $return) || isset($this->parts["pressure"])){
            return false;
        }
        
        $wind_parts = array();
        $prefix = "";
        
        if($speak == true){
            $wind_parts[] = "winnd";
        }
        else{
            $wind_parts[] = "wind"; 
        }
        
        if($return[1] == "VRB"){
            $wind_parts[] = "variable at";
            $wind_parts[] = $return[2];
            $this->parts["winds"][] = implode (" ", $wind_parts);
            
            return true;
        }
        
        if($return[1] == "000" || $return[2] < "3"){
            $wind_parts[] = "calm";
            $this->parts["winds"][] = implode(" ", $wind_parts);
            
            return true;
        }
        
        $wind_parts[] = $this->spoken($return[1], false, $speak) . " at";
        $wind_parts[] = $this->spoken(abs($return[2]), false, $speak);
        if(!isset($return[3]) || empty($return[3])){
            $wind_parts[] = "knots";
        }
        $this->parts["winds"][] = implode(" ", $wind_parts);
        
        if(isset($return[3]) && !empty($return[3])){
            $this->parts["winds"][] = "gusting " . $this->spoken($return[4], false, $speak) . " knots";
        }
        
        return true;
    }
    
    private function winds_variable($part, $speak = false){
        if(!preg_match("@^([0-9]{3})V([0-9]{3})$@", $part, $return) || isset($this->parts["pressure"])){
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
    
    private function winds_full($part, $speak = false){
        $this->winds_basic($part, $speak);
        $this->winds_variable($part, $speak);
        
        return true;
    }
    
    private function visibility($part, $speak = false){
        if(!preg_match("@^(CAVOK|////|([0-9]{4})|([0-9]{1,2})(SM)?|(M)?(([1357])/(2|4|8|16)SM))$@", $part, $return) || isset($this->parts["visibility"])){
            return FALSE;
        }
        
        if($return[1] == "CAVOK"){
            $this->parts["visibility"] = "CAVOK";
            
            return true;
        }
        if(isset($return[2]) && !empty($return[2])){
            if($return[2] < 1000){
                $return[2] = abs($return[2]);
                $this->parts["visibility"] = "visibility " . $return[2] . " meters";
                
                return true;
            }
            
            if($return[2] < 1500){
                $suffix = " kilometer";
            }
            else{
                $suffix = " kilometers";
            }
            
            $modifier = "";
            if($return[2] == 9999){
                $modifier = " or more";
            }
            
            $return[2] = round($return[2] / 1000);
            
            if($return[2] < 10){
                $return[2] = $this->spoken($return[2], false, $speak);
            }
            
            $this->parts["visibility"] = "visibility " . $return[2] . $modifier . $suffix;
            
            return true;
        }
        
        if(isset($return[3]) && !empty($return[3])){
            if($return[3] == 1){
                $suffix = " mile";
            }
            else{
                $suffix = " miles";
            }
            
            $modifier = "";
            if($return[3] == 10){
                $modifier = " or more";
            }
            
            if($return[3] < 10){
                $return[3] = $this->spoken($return[3], false, $speak);
            }
            
            $this->parts["visibility"] = "visibility " . $return[3] . $modifier . $suffix;
            
            return true;
        }
        
        if(isset($return[6]) && !empty($return[6])){
            $this->parts["visibility"] = "visibility less than one mile";
            
            return true;
        }
    }
    
    private function weather($part, $speak = false){
        $weather_codes = implode("|", array_keys($this->weather_codes));
        $severity   = "";
        $type       = "";
        $proximity  = "";
        
        if(!preg_match("@^([-+])?(VC)?(" . $weather_codes . ")$@", $part, $return)){
            return false;
        }
        
        if(isset($return[1]) && !empty($return[1])){
            if($return[1] == "-"){
                $severity = "light ";
            }
            else{
                $severity = "heavy ";
            }
        }
        
        $type = $this->weather_codes[$return[3]];
        
        if(isset($return[2]) && !empty($return[2])){
            $proximity = " in vicinity";
        }
        
        $this->parts["weather"][] = $severity . $type . $proximity;
        
        return true;
    }
    
    private function vertical_visibility($part, $speak = false){
        if(!preg_match("@^(VV)([0-9]{3})$@", $part, $return) || isset($this->parts["vertical_visibility"])){
            return false;
        }
        
        $return[2] *= 100;
        
        $this->parts["vertical_visibility"] = "vertical visibility " . $return[2] . " feet";
        
        return true;
    }
    
    public function sky_cover($part, $speak = false){
        $sky_cover  = implode("|", array_keys($this->sky_cover));
        $sky_type   = implode("|", array_keys($this->sky_type));
        
        if(!preg_match("@^(" . $sky_cover . ")([0-9]{3})?(" . $sky_type . ")?$@", $part, $return) || isset($this->parts["pressure"])){
            return false;
        }
        
        $sky_type = "";
        
        if(isset($return[3]) && !empty($return[3])){
            $sky_type = " " . $this->sky_type[$return[3]];
        }
        
        if($return[1] == "NCD" || $return[1] == "CLR" || $return[1] == "SKC"){
            $this->parts["sky_cover"] = $this->sky_cover[$return[1]];
            
            return true;
        }
        
        $return[2] *= 100;
        
        if($return[2] > 8999 && $return[2] < 10000 && $speak == true){
            $thou = substr($return[2], 0, 1);
            $hund = " " . substr($return[2], 1, 4);
            if($hund == "000"){
                $hund = "";
            }
            $return[2] = $this->spoken($thou, false, true) . " thousand " . $hund;
        }
        elseif($return[2] > 9999 && $speak == true){
            $ten_thou = substr($return[2], 0, 2);
            $return[2] = $this->spoken($ten_thou, false, true) . " thousand";
        }
        
        if($return[1] == "BKN" || $return[1] == "OVC"){
            if($this->ceiling == true){
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
    
    private function temperature_dewpoint($part, $speak = false){
        if(!preg_match("@^(M)?([0-9]{2})/(M)?([0-9]{2})$@", $part, $return) || isset($this->parts["temperature_dewpoint"])){
            return false;
        }
        
        $temperature    = array("temperature");
        $dewpoint       = array("dewpoint");
        
        if(isset($return[1]) && !empty($return[1])){
            $temperature[] = "minus";
        }
        $temperature[] = $this->spoken(abs($return[2]), false, $speak);
        
        if(isset($return[3]) && !empty($return[3])){
            $dewpoint[] = "minus";
        }
        $dewpoint[] = $this->spoken(abs($return[4]), false, $speak);
        
        $this->parts["temperature_dewpoint"][] = implode(" ", $temperature);
        $this->parts["temperature_dewpoint"][] = implode(" ", $dewpoint);
        
        return true;
    }
    
    private function pressure($part, $speak = false){
        if(!preg_match("@^(A|Q)([0-9]{4})$@", $part, $return) || isset($this->parts["pressure"])){
            return false;
        }
        
        $inhg   = array();
        $qnh    = array();
        if($return[1] == "A"){
            $inhg = $return[2];
            $qnh  = round(number_format($return[2]/100, 2) *  33.86389);
            $this->parts["pressure"][] = "altimeter " . $this->spoken($inhg, false, $speak);
            $this->parts["pressure"][] = "qnh " . $this->spoken($qnh, false, $speak);
            
            return true;
        }
        
        $qnh  = $return[2];
        $inhg = round(($return[2]*100) / 33.86);
        $this->parts["pressure"][] = "qnh " . $this->spoken($qnh, false, $speak);
        $this->parts["pressure"][] = "altimeter " . $this->spoken($inhg, false, $speak);
        
        return true;
    }
    
    private function approaches($parts){
        if(!isset($parts)){
            return false;
        }
        
        if(sizeof($parts) == 1){
            $this->parts["approaches"] = $parts[0] . " approaches in use";
            
            return true;
        }
        
        $this->parts["approaches"] = "simultaneous ils and visual approaches in use";
        
        return true;
    }
    
    private function runways($landing_runways, $departing_runways, $speak = false){
        if(isset($this->override_runways)){
            return false;
        }
        
        $runways_out            = array();
        $landing_runways_out    = array();
        $departing_runways_out  = array();
        if($landing_runways == $departing_runways){
            $runways = array_unique(array_merge($landing_runways, $departing_runways));
            
            if(sizeof($runways) == 1){
                $prefix = "landing and departing runway ";
            }
            else{
                $prefix = "landing and departing runways ";
            }
            
            while($runway = current($runways)){
                $runways_out[] = $this->spoken($runway, true, $speak);
                next($runways);
            }
            
            $this->parts["runways"] = $prefix . implode(", ", $runways_out);
            
            return true;
        }
        
        if(sizeof($landing_runways) == 1){
            $prefix1 = "landing runway ";
        }
        else{
            $prefix1 = "landing runways ";
        }
        
        if(sizeof($departing_runways) == 1){
            $prefix2 = "departing runway ";
        }
        else{
            $prefix2 = "departing runways ";
        }
        
        while($runway = current($landing_runways)){
            $landing_runways_out[] = $this->spoken($runway, true, $speak);
            next($landing_runways);
        }
        
        while($runway = current($departing_runways)){
            $departing_runways_out[] = $this->spoken($runway, true, $speak);
            next($departing_runways);
        }
        
        $this->parts["landing_runways"]      = $prefix1 . implode(", ", $landing_runways_out);
        $this->parts["departing_runways"]    = $prefix2 . implode(", ", $departing_runways_out);
        
        return true;
    }
    
    private function remarks1($parts){
        if(!isset($parts) || empty($parts)){
            return false;
        }
        
        $this->parts["remarks1"] = $this->remarks1;

        return true;
    }
    
    private function remarks2($parts){
        if(!isset($parts) || empty($parts)){
            return false;
        }
        
        $this->parts["remarks2"] = implode(". ", $parts);
        
        return true;
    }
    
    private function ident_end($speak = false){
        if(!isset($this->ident)){
            return false;
        }
        
        $this->parts["ident_end"] = "advise controller on initial contact that you have information " . $this->spoken($this->ident, false, $speak);
        
        return true;
    }
    
    public function parse_atis($speak){
        $metar = $this->metar;
        $this->station_info($speak);
        while($part = current($metar)){
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
        
        $this->approaches(array("ils","visual"));
        $this->runways($this->landing_runways, $this->departing_runways, $speak);
        $this->remarks1($this->remarks1);
        $this->remarks2($this->remarks2);
        $this->ident_end($speak);

        $output = array();
        while($part = current($this->parts)){
            
            $output[] = preg_replace("@\s+@", " ", strtoupper($this->merge_recursive($part)). "... ");
            next($this->parts);
        }
        
        return implode(" ", $output);
    }
}
?>