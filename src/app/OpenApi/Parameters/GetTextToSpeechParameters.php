<?php

namespace App\OpenApi\Parameters;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Parameter;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Vyuldashev\LaravelOpenApi\Factories\ParametersFactory;

class GetTextToSpeechParameters extends ParametersFactory
{
    /**
     * @return Parameter[]
     */
    public function build(): array
    {
        return [

            Parameter::query()
                ->name('icao')
                ->description('ICAO code of the airport')
                ->required(true)
                ->schema(Schema::string())
                ->example('KJAX'),
            Parameter::query()
                ->name('id')
                ->description('ID of the ATIS audio file')
                ->required(true)
                ->schema(Schema::string())
                ->example('1'),

        ];
    }
}
