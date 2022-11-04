<?php
include_once('./includes/constants.php');

/**
 *   It takes an ICAO code, gets the METAR for that ICAO, parses the METAR for the wind direction, then
 *   gets the runways for that ICAO from a database, and calculates the difference between the wind
 *   direction and the runway heading. 
 */
class Runways
{
    private $icao;
    private $metar;
    private $runways;
    private $wind_dir;
    private $wind_diff;

    public function __construct()
    {
        $this->icao = strtoupper(preg_replace("@[^a-z0-9]@i", "", $_POST["icao"]));
        $this->metar = explode(" ", $this->url_get_data($this->icao));

        while ($part = current($this->metar)) {
            if (!preg_match("@^([0-9]{3}|VRB)([0-9]{2,3})(G([0-9]{2,3}))?KT$@", $part, $return)) {
                next($this->metar);
                continue;
            }

            $this->wind_dir = $return[1];

            next($this->metar);

            return true;
        }
    }

    /**
     * It takes the ICAO code as a parameter, and returns the METAR data from the NOAA website
     * 
     * @param string $icao The ICAO code of the airport you want to get the METAR for.
     * 
     * @return string Returns METAR data
     */
    private function url_get_data(string $icao)
    {
        $this->icao = strtoupper($_POST["icao"]);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://tgftp.nws.noaa.gov/data/observations/metar/stations/" . strtoupper($icao) . ".TXT");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $exec = curl_exec($ch);
        curl_close($ch);

        $lines = explode("\n", $exec);

        return trim($lines[1]);
    }

    /**
     * It returns the shortest angle between two angles.
     * 
     * @param mixed $angle_start The starting angle of the needle.
     * @param mixed $angle_target The angle you want to rotate to.
     * 
     * @return mixed difference between the two angles.
     */
    private function get_angle_diff(mixed $angle_start, mixed $angle_target)
    {
        $delta = intval($angle_target) - intval($angle_start);
        $direction = ($delta > 0) ? -1 : 1;
        $delta1 = abs($delta);
        $delta2 = 360 - $delta1;
        return $direction * ($delta1 < $delta2 ? $delta1 : $delta2);
    }

    /**
     * It takes the runways from the database, and then compares the wind direction to the runway
     * heading to determine the difference between the two, and displays them
     */
    public function parse_runways()
    {
        $mysqli = new mysqli(constant('HOST'), constant('USERNAME'), constant('PASSWORD'), constant('DATABASE'));
        // deepcode ignore Sqli: <database user should only be permitted to SELECT data, nothing else.>
        $query = $mysqli->query("SELECT runways FROM airports WHERE icao='" . htmlspecialchars($this->icao) . "' limit 1");
        $result = $query->fetch_row();

        $runways = explode(",", $result[0]);
        $output = array();



        $i = 0;
        while ($i < sizeof($runways)) {
            $runway_hdg = str_pad(substr($runways[$i], 0, 2), 3, "0");

            if ($this->wind_dir == "0" || $this->wind_dir == "VRB") {
                $wind_dir = "-";
                $wind_diff = "-";
                $no_val = true;
            } else {
                $wind_dir = $this->wind_dir;
                $wind_diff = abs($this->get_angle_diff($this->wind_dir, $runway_hdg));
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

        echo '<table class="table">';
        if ($no_val == true) {
            echo '
                    <thead>
                        <tr>
                            <th colspan="5" class="small">Winds at <strong>' . htmlspecialchars(strtoupper($_POST["icao"])) . '</strong> are either calm or variable, so runway/wind heading variance cannot be calculated.</th>
                        </tr>
                    </thead>';
        }
        echo '
                <thead>
                    <tr>
                        <th scope="col"><i class="fa-solid fa-road"></i></th>
                        <th scope="col"><i class="fa-solid fa-wind"></i></th>
                        <th scope="col"><i class="fa-solid fa-plus-minus"></i><i class="fa-solid fa-wind"></i></th>
                        <th scope="col"><i class="fa-solid fa-plane-arrival"></i></th>
                        <th scope="col"><i class="fa-solid fa-plane-departure"></i></th>
                    </tr>
                </thead>
                <tbody>';
        while ($part = current($output)) {
            echo '
                    <tr>
                        <th scope="row">' . $part["runway"] . '</th>
                        <td>' . $part["wind_dir"] . '</td>
                        <td>' . $part["wind_diff"] . '</td>
                        <td><input class="form-check-input" type="checkbox" name="landing_runways[]" value="' . $part["runway"] . '"></td>
                        <td><input class="form-check-input" type="checkbox" name="departing_runways[]" value="' . $part["runway"] . '"></td>
                    </tr>';
            next($output);
        }
        echo '
                </tbody>
            </table>';
    }
}
