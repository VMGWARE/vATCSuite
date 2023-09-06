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

    /**
     * Create a new TextToSpeech instance.
     *
     * @param string $text The text to be converted to speech
     * @param string $language The language of the text
     * @param string $engine The TTS engine to be used
     */
    public function __construct($text, $language = 'en-us', $engine = 'VoiceRSS')
    {
        $this->text = $text;
        $this->language = $language;
        $this->engine = $engine;

        // You can store API keys in a config file and access them here
        $this->API_KEYS = [
            'VoiceRSS' => config('app.voice-rss-key'),
            // 'AnotherTTSAPI' => config('app.another-tts-key'),
        ];
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
        $ch = curl_init("https://api.voicerss.org/?key=" . $this->API_KEYS['VoiceRSS'] . "&hl=$this->language&c=MP3&v=John&f=16khz_16bit_stereo&src=" . rawurlencode($this->text));
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
            return $output;  // Return the generated audio data
        }

        throw new Exception('VoiceRSS API Error');  // or return null or handle as per your requirements
    }

    // Implement other TTS engines similarly:
    // private function generateWithAnotherTTSAPI() { ... }
}
