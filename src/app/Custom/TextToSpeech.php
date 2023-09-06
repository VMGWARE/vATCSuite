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
        $this->text = $text;
        $this->language = $language;
        $this->engine = $engine;

        // You can store API keys in a config file and access them here
        $this->API_KEYS = [
            'VoiceRSS' => config('app.voice-rss-key'),
            // 'AnotherTTSAPI' => config('app.another-tts-key'),
        ];

        // Set default options for each engine and merge with provided options
        $defaultOptions = [
            'VoiceRSS' => [
                'format' => 'MP3',
                'voice' => 'John',
                'rate' => '16khz_16bit_stereo',
            ],
            // 'AnotherTTSAPI' => [ ... ]
        ];

        if (!array_key_exists($this->engine, $defaultOptions)) {
            throw new Exception('Unsupported TTS Engine');
        }

        $this->OPTIONS = array_merge($defaultOptions[$this->engine], $options);
    }

    /**
     * Generate the audio file.
     *
     * @return bool|string Returns the generated audio data or false on failure
     */
    public function generateAudio(): bool|string
    {
        switch ($this->engine) {
            case 'VoiceRSS':
                return $this->generateWithVoiceRSS();
                // case 'AnotherTTSAPI':
                //     return $this->generateWithAnotherTTSAPI();
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
        $ch = curl_init("https://api.voicerss.org/?key=" . $this->API_KEYS['VoiceRSS'] .
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

    // Implement other TTS engines similarly:
    // private function generateWithAnotherTTSAPI() { ... }
}
