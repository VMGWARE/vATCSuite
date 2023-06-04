<?php

namespace App\OpenApi\Parameters;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Parameter;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Vyuldashev\LaravelOpenApi\Factories\ParametersFactory;

class GetAirportParameters extends ParametersFactory
{
    /**
     * @return Parameter[]
     */
    public function build(): array
    {
        return [
            Parameter::query()
                ->name('icao')
                ->in(Parameter::IN_PATH)
                ->description('ICAO code of the airport')
                ->required(true)
                ->schema(Schema::string())
                ->example('KJFK'),
        ];
    }
}
