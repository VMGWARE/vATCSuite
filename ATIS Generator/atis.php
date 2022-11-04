<?php
include_once('./includes/constants.php');

if(!isset($_POST["icao"]) || is_null($_POST["icao"])){
    return false;
}

/**
 * It takes an ICAO code as a string, and returns the METAR data as a string.
 * 
 * @param string $icao The ICAO code of the airport you want to get the METAR from.
 * 
 * @return string METAR data
 */
function url_get_data(string $icao){
    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL,"https://tgftp.nws.noaa.gov/data/observations/metar/stations/".strtoupper($icao).".TXT");
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
    $exec = curl_exec($ch);
    curl_close($ch);
    
    $lines = explode("\n", $exec);
    
    return trim($lines[1]);
}

if(url_get_data($_POST["icao"]) == false){
    echo '
        <div class="modal fade" id="atis-modal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Oops!</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center">
                        <p class="fs-3 text-danger"><i class="fa-solid fa-circle-xmark"></i><br/>Generation Failed</p>
                        <p>AviationWeather.gov does not have any weather information available for <strong>' . htmlspecialchars(strtoupper($_POST["icao"])) . '</strong>. Please try again with a different airport.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    ';
    
    return false;
}

if(!isset($_POST["landing_runways"]) || !isset($_POST["departing_runways"])){
    echo '
        <div class="modal fade" id="atis-modal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Oops!</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center">
                        <p class="fs-3 text-danger"><i class="fa-solid fa-circle-xmark"></i><br/>Generation Failed</p>
                        <p>You must select at least one landing and departing runway to generate your ATIS. Please click "List Runways" and try again.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    ';
    
    return false;
}
include("./classes/class.atis.php");
$atis1 = new AtisGenerator();
$atis2 = new AtisGenerator();
echo '
        <div class="modal fade" id="atis-modal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Success!</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center">
                        <p class="fs-3 text-success"><i class="fa-solid fa-circle-check"></i><br/>Generation Success!</p>
                        <p>Your input has been serialized, we\'ve gotten the weather and it\'s all been run through the processor. It\'s parsed and your ATIS ready to go! Click on the buttons below to use it.</p>
                        <div id="atis1" class="hide">' . $atis1->parse_atis(false) . '</div>
                        <div id="atis2" class="hide">' . $atis2->parse_atis(true) . '</div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" id="copy-atis">Copy ATIS To Clipboard</button>
                        <a class="btn btn-primary" id="download-atis">Download .mp3</a>
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
';

return true;
?>