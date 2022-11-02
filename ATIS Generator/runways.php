<?
if(!isset($_POST["icao"]) || is_null($_POST["icao"])){
    return false;
}

function db_get_contents($icao){
    	$mysqli = new mysqli('localhost','redbbqhz_Vbyn','WentNeed$l5FQ','redbbqhz_atis_generator');
		$query = $mysqli->query("SELECT runways FROM airports WHERE icao='" . strtoupper($icao) . "' limit 1");
		$result = $query->fetch_row();
		
		
		if(!$result){
		    return false;
		}
		
		return true;
}

function url_get_contents($icao){
	$ch = curl_init();
	curl_setopt($ch,CURLOPT_URL,"https://tgftp.nws.noaa.gov/data/observations/metar/stations/".strtoupper($icao).".TXT");
	curl_setopt($ch, CURLOPT_NOBODY, true);
	$response = curl_exec($ch);
	if(curl_getinfo($ch, CURLINFO_HTTP_CODE) != 200){
	    return false;
	}
	curl_close($ch);
	
	return true;
}
if($_POST["icao"] == ""){
    echo '
        <div class="modal fade" id="runway-modal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Oops!</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center">
                        <p class="fs-3 text-danger"><i class="fa-solid fa-circle-xmark"></i><br/>Generation Failed</p>
                        <p>No ICAO code provided. Please enter an ICAO code and try again.</p>
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

if(url_get_contents($_POST["icao"]) == false){
    echo '
        <div class="modal fade" id="runway-modal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Oops!</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center">
                        <p class="fs-3 text-danger"><i class="fa-solid fa-circle-xmark"></i><br/>Generation Failed</p>
                        <p>AviationWeather.gov does not have any weather information available for <strong>' . strtoupper($_POST["icao"]) . '</strong>. Please try again with a different airport.</p>
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

if(db_get_contents($_POST["icao"]) == false){
echo '
        <div class="modal fade" id="runway-modal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Oops!</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center">
                        <p class="fs-3 text-danger"><i class="fa-solid fa-circle-xmark"></i><br/>Generation Failed</p>
                        <p>There is no runway data available for <strong>' . strtoupper($_POST["icao"]) . '</strong></p>
                        <p>
                            <input type="checkbox" id="override-runways" name="override-runways" class="form-input-check">
                            <label for="override-runways" class="form-check-label">Override Runway Selection (Not Recommended)</label>
                        </p>
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

    include("./classes/class.runways.php");
    $runways = new Runways();
    
echo '
        <div class="modal fade" id="runway-modal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Runway List for ' . strtoupper($_POST["icao"]) . '</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center">
';
                        $runways->parse_runways();
echo '
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
';
    return true;

?>