<?php

namespace App\Custom;

use Illuminate\Support\Facades\Log;
use Exception;
use FFMpeg\FFMpeg;
use FFMpeg\Format\Audio\Mp3;

/**
 * Text to Speech Generator.
 * 
 * Supported TTS engines:
 * - VoiceRSS   
 * - Eleven Labs
 * - Larynx (Beta)
 */
class TextToSpeech
{
    private $text;
    private mixed $language;
    private mixed $engine;
    private array $API_KEYS;
    private array $OPTIONS;
    private array $ELEVEN_LABS_VOICE_MAP;

    /**
     * Create a new TextToSpeech instance.
     *
     * @param string $text The text to be converted to speech.
     * @param string $language The language of the text (currently only used with VoiceRSS).
     * @param string $engine The TTS engine to be used ('Larynx', 'VoiceRSS', 'ElevenLabs', ...).
     * @param array $options Additional options for the TTS engine:
     *      - VoiceRSS:
     *          - format: 'MP3' (default), ...
     *          - voice: 'John' (default), ...
     *          - rate: '16khz_16bit_stereo' (default), ...
     *      - ElevenLabs:
     *          - voice: 'DefaultVoice' (default), 'Rachel', 'Clyde', ... (refer to $ELEVEN_LABS_VOICE_MAP)
     *          - model_id: 'eleven_monolingual_v1' (default), ...
     *          - voice_settings: 
     *              - stability: int (default 0), ...
     *              - similarity_boost: int (default 0), ...
     *              - style: int (default 0), ...
     *              - use_speaker_boost: bool (default true), ...
     *      - Larynx:
     *         - voice: 'en-us/harvard-glow_tts' (default), ... 
     *         - vocoder: 'hifi_gan/universal_large' (default), ...  
     *         - denoiserStrength: float (default 0.01), ...
     *         - noiseScale: float (default 0.333), ...
     *         - lengthScale: float (default 1.0), ...
     *         - ssml: bool (default false), ...
     * @param array|null $apiKeys (Optional) The API keys for the TTS engines. Keys should be engine names with associated API key as value.
     * @throws Exception If the TTS engine is not supported.
     */
    public function __construct($text, $language = 'en-us', $engine = 'VoiceRSS', $options = [], $apiKeys = [])
    {
        // Set the properties
        $this->text = $text;
        $this->language = $language;
        $this->engine = $engine;

        // Prioritize the provided API keys over the default ones
        $this->API_KEYS = array_merge([
            'VoiceRSS' => config('app.voice-rss-key'),
            'ElevenLabs' => config('app.eleven-labs-key'),
        ], $apiKeys);

        // Voice mapping for Eleven Labs API
        $this->ELEVEN_LABS_VOICE_MAP = [
            'DefaultVoice' => 'pNInz6obpgDQGcFmaJgB', // Default voic is Adam
            'Rachel' => '21m00Tcm4TlvDq8ikWAM',
            'Clyde' => '2EiwWnXFnvU5JabPnv8n',
            'Domi' => 'AZnzlk1XvdvUeBnXmlld',
            'Dave' => 'CYw3kZ02Hs0563khs1Fj',
            'Fin' => 'D38z5RcWu1voky8WS1ja',
            'Bella' => 'EXAVITQu4vr4xnSDxMaL',
            'Antoni' => 'ErXwobaYiN019PkySvjV',
            'Thomas' => 'GBv7mTt0atIp3Br8iCZE',
            'Charlie' => 'IKne3meq5aSn9XLyUdCD',
            'Emily' => 'LcfcDJNUP1GQjkzn1xUU',
            'Elli' => 'MF3mGyEYCl7XYWbV9V6O',
            'Callum' => 'N2lVS1w4EtoT3dr4eOWO',
            'Patrick' => 'ODq5zmih8GrVes37Dizd',
            'Harry' => 'SOYHLrjzK2X1ezoPC6cr',
            'Liam' => 'TX3LPaxmHKxFdv7VOQHJ',
            'Dorothy' => 'ThT5KcBeYPX3keUQqHPh',
            'Josh' => 'TxGEqnHWrfWFTfGW9XjX',
            'Arnold' => 'VR6AewLTigWG4xSOukaG',
            'Charlotte' => 'XB0fDUnXU5powFXDhCwa',
            'Matilda' => 'XrExE9yKIg1WjnnlVkGX',
            'Matthew' => 'Yko7PKHZNXotIFUBG7I9',
            'James' => 'ZQe5CZNOzWyzPSCn5a3c',
            'Joseph' => 'Zlb1dXrM653N07WRdFW3',
            'Jeremy' => 'bVMeCyTHy58xNoL34h3p',
            'Michael' => 'flq6f7yk4E4fJM5XTYuZ',
            'Ethan' => 'g5CIjZEefAph4nQFvHAz',
            'Gigi' => 'jBpfuIE2acCO8z3wKNLl',
            'Freya' => 'jsCqWAovK2LkecY7zXl4',
            'Grace' => 'oWAxZDx7w5VEj9dCyTzz',
            'Daniel' => 'onwK4e9ZLuTAKqWW03F9',
            'Serena' => 'pMsXgVXv3BLzUgSXRplE',
            'Adam' => 'pNInz6obpgDQGcFmaJgB',
            'Nicole' => 'piTKgcLEGmPE4e6mEKli',
            'Jessie' => 't0jbNlBVZ17f02VDIeMI',
            'Ryan' => 'wViXBPUzp2ZZixB1xQuM',
            'Sam' => 'yoZ06aMxZJJ28mfd3POQ',
            'Glinda' => 'z9fAnlkpzviPz146aGWa',
            'Giovanni' => 'zcAOhNBS3c14rBihAFp1',
            'Mimi' => 'zrHiDhphv9ZnVXBqCLjz',
        ];

        // Set default options for each engine and merge with provided options
        $defaultOptions = [
            'VoiceRSS' => [
                'format' => 'MP3',
                'voice' => 'John',
                'rate' => '16khz_16bit_stereo',
            ],
            'ElevenLabs' => [
                'voice' => 'DefaultVoice',
                'model_id' => 'eleven_monolingual_v1',
                'voice_settings' => [
                    'stability' => 0.50,
                    'similarity_boost' => 0.75,
                    'style' => 0,
                    'use_speaker_boost' => true
                ]
            ],
            'Larynx' => [
                // TODO: Find a better voice
                'voice' => 'en-us/harvard-glow_tts',
                'vocoder' => 'hifi_gan/universal_large',
                'denoiserStrength' => 0.002,
                'noiseScale' => 0.667,
                'lengthScale' => 0.85,
                'ssml' => 'false',
            ]
        ];

        // Check if the engine is supported
        if (!array_key_exists($this->engine, $defaultOptions)) {
            throw new Exception('Unsupported TTS Engine');
        }

        // Merge default options with provided options
        $this->OPTIONS = array_merge($defaultOptions[$this->engine], $options);
    }

    /**
     * Generate the audio file.
     *
     * @return bool|string Returns the generated audio data or false on failure
     */
    public function generateAudio(): bool|string
    {
        // Generate the audio file using the specified engine
        switch ($this->engine) {
                // VoiceRSS
            case 'VoiceRSS':
                return $this->generateWithVoiceRSS();
                // Eleven Labs
            case 'ElevenLabs':
                return $this->generateWithElevenLabs();
                // Larynx
            case 'Larynx':
                return $this->generateWithLarynx();
                // Unsupported engine
            default:
                throw new Exception('Unsupported TTS Engine');
        }
    }

    /**
     * Generate the audio file using VoiceRSS.
     *
     * @return bool|string Returns the generated audio data or false on failure
     */
    private function generateWithVoiceRSS()
    {
        $ch = curl_init(
            "https://api.voicerss.org/?key=" . $this->API_KEYS['VoiceRSS'] .
                "&hl=" . $this->language .
                "&c=" . $this->OPTIONS['format'] .
                "&v=" . $this->OPTIONS['voice'] .
                "&f=" . $this->OPTIONS['rate'] .
                "&src=" . rawurlencode($this->text)
        );

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
            return $output;
        }

        throw new Exception('VoiceRSS API Error');  // or return null or handle as per your requirements
    }

    /**
     * Generate the audio file using Eleven Labs.
     *
     * @return bool|string Returns the generated audio data or false on failure
     * @throws Exception If the API request fails
     */
    private function generateWithElevenLabs()
    {
        // Voice ID mapped from the voice name provided in options
        $voiceId = $this->ELEVEN_LABS_VOICE_MAP[$this->OPTIONS['voice']] ?? null;

        if (!$voiceId) {
            throw new Exception('Invalid voice name for Eleven Labs API');
        }

        // Endpoint
        $url = "https://api.elevenlabs.io/v1/text-to-speech/{$voiceId}";

        // Curl request setup
        $ch = curl_init($url);

        $headers = [
            'accept: audio/mpeg',
            'xi-api-key: ' . $this->API_KEYS['ElevenLabs'],
            'Content-Type: application/json'
        ];

        $postData = [
            "text" => $this->text,
            "model_id" => $this->OPTIONS['model_id'],
            "voice_settings" => $this->OPTIONS['voice_settings']
        ];

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $output = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($status == 200) {
            return $output;  // Return the generated audio data
        }

        throw new Exception('Eleven Labs API Error');  // Handle error as per your requirements
    }

    /**
     * Generate the audio file using Larynx.
     *
     * @return bool|string Returns the generated audio data or false on failure
     * @throws Exception If the API request fails
     */
    private function generateWithLarynx()
    {
        $requesturl = "http://voice.vmgware.dev/api/tts?" .
            "text=" . rawurlencode($this->text) .
            "&voice=" . $this->OPTIONS['voice'] .
            "&vocoder=" . $this->OPTIONS['vocoder'] .
            "&denoiserStrength=" . $this->OPTIONS['denoiserStrength'] .
            "&noiseScale=" . $this->OPTIONS['noiseScale'] .
            "&lengthScale=" . $this->OPTIONS['lengthScale'] .
            "&ssml=" . $this->OPTIONS['ssml'];

        $ch = curl_init($requesturl);

        $tmpfname = tempnam(sys_get_temp_dir(), 'tts');
        $fp = fopen($tmpfname, 'wb');

        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60); // Adjust timeout as needed
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        curl_exec($ch);
        if (curl_errno($ch)) {
            throw new Exception('Larynx API Error: ' . curl_error($ch));
        }

        fclose($fp);
        curl_close($ch);

        if (filesize($tmpfname) > 0) {
            // return $tmpfname; // Return the path to the temporary file
            // Return the audio data
            $audioData = file_get_contents($tmpfname);
            unlink($tmpfname); // Delete the temporary file
            return $audioData;
        } else {
            throw new Exception('Larynx API Error: No response or empty file');
        }
    }



    /**
     * Convert the provided audio data to MP3. (Beta)
     *
     * @param string $audioData The raw audio data.
     * @param string $outputFormat The desired output format ('mp3', 'wav', etc.)
     * @return string Returns the converted audio data.
     * @throws Exception if the conversion fails.
     */
    public function convertToFormat(string $audioData, string $outputFormat = 'mp3'): string
    {
        // Save the audio data to a temporary file
        $tempInputFile = tempnam(sys_get_temp_dir(), 'tts_');
        file_put_contents($tempInputFile, $audioData);

        $tempOutputFile = tempnam(sys_get_temp_dir(), 'tts_converted_');

        // Initialize FFMpeg
        $ffmpeg = FFMpeg::create();

        // Open the temporary audio file
        $audio = $ffmpeg->open($tempInputFile);

        // Convert to desired format
        switch ($outputFormat) {
            case 'mp3':
                $format = new Mp3();
                break;
                // Add other cases as necessary (e.g., 'wav', 'ogg', etc.)
            default:
                throw new Exception("Unsupported output format: $outputFormat");
        }

        $audio->save($format, $tempOutputFile);

        // Read the converted audio and return
        $convertedAudioData = file_get_contents($tempOutputFile);

        // Cleanup temporary files
        unlink($tempInputFile);
        unlink($tempOutputFile);

        return $convertedAudioData;
    }

    /**
     * Check if the API keys are valid.
     *
     * @return bool Returns true if the API keys are valid.
     */
    public static function hasApiKey(): bool
    {
        $apiKeys = config('app.voice-rss-key') . config('app.eleven-labs-key');

        return !empty($apiKeys);
    }

    /**
     * Validate a custom configuration for the TTS engine.
     *
     * @param string $engine The TTS engine to validate the configuration for ('VoiceRSS', 'ElevenLabs', etc.).
     * @param array $customConfig The custom configuration to validate.
     * @return bool True if the custom config is valid, false otherwise.
     */
    public static function validateCustomConfig(array $customConfig): bool
    {
        $engine = $customConfig['engine'] ?? null;

        // Make sure the api key is provided
        if (!isset($customConfig['api_key']) || empty($customConfig['api_key'])) {
            return false;
        }

        if ($engine === 'VoiceRSS') {
            // Validate custom config for VoiceRSS engine (example rules)
            $validOptions = ['format', 'voice', 'rate'];

            foreach ($customConfig[$engine] as $key => $value) {
                if (!in_array($key, $validOptions)) {
                    return false; // Invalid option found
                }
            }

            return true; // Custom config is valid for VoiceRSS
        }
        if ($engine === 'ElevenLabs') {
            // Validate custom config for ElevenLabs engine (example rules)
            $validOptions = ['voice', 'model_id', 'voice_settings'];

            foreach ($customConfig[$engine] as $key => $value) {
                if (!in_array($key, $validOptions)) {
                    return false; // Invalid option found
                }
            }

            // Additional validation for specific options within voice_settings if needed
            if (isset($customConfig[$engine]['voice_settings'])) {
                $voiceSettings = $customConfig[$engine]['voice_settings'];
                if (!is_array($voiceSettings) || !self::validateElevenLabsVoiceSettings($voiceSettings)) {
                    return false; // Invalid voice_settings
                }
            }

            return true; // Custom config is valid for ElevenLabs
        }
        if ($engine === 'Larynx') {
            // Validate custom config for Larynx engine (example rules)
            $validOptions = ['voice', 'vocoder', 'denoiserStrength', 'noiseScale', 'lengthScale', 'ssml'];

            foreach ($customConfig[$engine] as $key => $value) {
                if (!in_array($key, $validOptions)) {
                    return false; // Invalid option found
                }
            }

            return true; // Custom config is valid for Larynx
        }

        return false; // Unsupported engine
    }

    /**
     * Validate the ElevenLabs voice_settings configuration.
     *
     * @param array $voiceSettings The voice_settings configuration to validate.
     * @return bool True if voice_settings is valid, false otherwise.
     */
    private static function validateElevenLabsVoiceSettings(array $voiceSettings): bool
    {
        // Add validation rules for ElevenLabs voice_settings here
        $validOptions = ['stability', 'similarity_boost', 'style', 'use_speaker_boost'];

        foreach ($voiceSettings as $key => $value) {
            if (!in_array($key, $validOptions)) {
                return false; // Invalid option found in voice_settings
            }
        }

        return true; // voice_settings is valid
    }
}
