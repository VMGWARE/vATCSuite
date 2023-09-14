<?php

namespace App\OpenApi\RequestBodies\TTS;

use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\RequestBody;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Vyuldashev\LaravelOpenApi\Factories\RequestBodyFactory;

class GenerateRequestBody extends RequestBodyFactory
{
    public function build(): RequestBody
    {
        // Define the schema for the request body
        $response = Schema::object()->properties(
            Schema::string('icao')
                ->example('KJAX')
                ->description('The ATIS ICAO code.')
                ->required(),
            Schema::string('ident')
                ->example('A')
                ->description('The ATIS ident letter.')
                ->required(),
            Schema::string('atis')
                ->example('Jacksonville International Airport, information Alpha, time 1700 Zulu, wind 180 at 10, visibility 10, sky clear, temperature 27, dew point 23, altimeter 3002, landing and departing runway 8, advise on initial contact you have information Alpha.')
                ->description('The ATIS message.')
                ->required(),
            // Options
            Schema::object('options')
                ->description('Additional options for TTS engine.')
                ->properties(
                    // TTS Engine: VoiceRSS, ElevenLabs
                    Schema::string('engine')
                        ->description('The TTS engine to be used (e.g., VoiceRSS, ElevenLabs).')
                        ->enum('VoiceRSS', 'ElevenLabs')
                        ->example('VoiceRSS'),
                    // API Key
                    Schema::string('api_key')
                        ->description('The API key for the TTS engine.')
                        ->example('1234567890'),
                    // VoiceRSS
                    Schema::object('VoiceRSS')
                        ->description('VoiceRSS-specific options.')
                        ->properties(
                            Schema::string('format')
                                ->description('The audio format (default is MP3).')
                                ->example('MP3'),
                            Schema::string('voice')
                                ->description('The voice (default is John).')
                                ->example('John'),
                            Schema::string('rate')
                                ->description('The rate (default is 16khz_16bit_stereo).')
                                ->example('16khz_16bit_stereo'),
                        ),
                    // ElevenLabs
                    Schema::object('ElevenLabs')
                        ->description('ElevenLabs-specific options.')
                        ->properties(
                            Schema::string('voice')
                                ->description('The voice (default is Adam).')
                                ->example('Adam'),
                            Schema::string('model_id')
                                ->description('The model ID (default is eleven_monolingual_v1).')
                                ->example('eleven_monolingual_v1'),
                            Schema::object('voice_settings')
                                ->description('Voice settings.')
                                ->properties(
                                    Schema::number('stability')
                                        ->description('Stability setting (default is 0.50).')
                                        ->example(0.50),
                                    Schema::number('similarity_boost')
                                        ->description('Similarity boost setting (default is 0.75).')
                                        ->example(0.75),
                                    Schema::number('style')
                                        ->description('Style setting (default is 0).')
                                        ->example(0),
                                    Schema::boolean('use_speaker_boost')
                                        ->description('Use speaker boost (default is true).')
                                        ->example(true),
                                ),
                        ),
                ),
        );

        return RequestBody::create('GenerateTTS')
            ->description('Generate TTS file for an airport.')
            ->content(
                MediaType::json()->schema($response)
            );
    }
}
