<?php
if(!isset($_GET) || empty($_GET) || strlen($_GET['atis']) < 20){
    return false;
}
$ch = curl_init("http://api.voicerss.org/?key=92a278f391ff4c4fb65e6fbc69c10e5f&hl=en-us&c=MP3&v=John&f=16khz_16bit_stereo&src=" . rawurlencode($_GET['atis']));
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_NOBODY, 0);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
$output = curl_exec($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
if ($status == 200) {
    header("Content-type: application/octet-stream");     
    header("Content-Disposition: attachment; filename=ATIS_".gmdate("YmdHis",time()).".mp3"); 
    echo $output;
    die();
}
?>