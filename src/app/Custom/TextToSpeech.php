<?php

namespace App\Custom;

use Exception;

/**
 * Text to Speech Generator.
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
     * @param string $text The text to be converted to speech
     * @param string $language The language of the text
     * @param string $engine The TTS engine to be used
     * @param array $options Additional options for the TTS engine
     * @throws Exception If the TTS engine is not supported
     */
    public function __construct($text, $language = 'en-us', $engine = 'VoiceRSS', $options = [])
    {
        // Set the properties
        $this->text = $text;
        $this->language = $language;
        $this->engine = $engine;

        // You can store API keys in a config file and access them here
        $this->API_KEYS = [
            'VoiceRSS' => config('app.voice-rss-key'),
            'ElevenLabs' => config('app.eleven-labs-key'),
            // 'AnotherTTSAPI' => config('app.another-tts-key'),
        ];

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
                    'stability' => 0,
                    'similarity_boost' => 0,
                    'style' => 0,
                    'use_speaker_boost' => true
                ]
            ],
            // 'AnotherTTSAPI' => [ ... ]
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
                // case 'AnotherTTSAPI':
                //     return $this->generateWithAnotherTTSAPI();
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
     */
    private function generateWithElevenLabs()
    {
        $endpoint = "https://api.elevenlabs.com/v1/text-to-speech/" . $this->ELEVEN_LABS_VOICE_MAP[$this->OPTIONS['voice']];

        $ch = curl_init($endpoint);

        $postData = [
            'text' => $this->text,
            'model_id' => $this->OPTIONS['model_id'],
            'voice_settings' => $this->OPTIONS['voice_settings']
        ];

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'xi-api-key: ' . $this->API_KEYS['ElevenLabs']
        ]);
        $output = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($status == 200) {
            return $output;
        }

        throw new Exception('Eleven Labs API Error');
    }


    // Implement other TTS engines similarly:
    // private function generateWithAnotherTTSAPI() { ... }
}
