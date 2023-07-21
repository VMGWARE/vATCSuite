<?php

namespace App\OpenApi\Parameters\TTS;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Parameter;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Vyuldashev\LaravelOpenApi\Factories\ParametersFactory;

class DeleteParameters extends ParametersFactory
{
    /**
     * @return Parameter[]
     */
    public function build(): array
    {
        return [

            Parameter::query()
                ->name('id')
                ->description('The ID of the ATIS audio file.')
                ->required()
                ->schema(Schema::string()),
            Parameter::query()
                ->name('password')
                ->description('The password for the ATIS audio file if it is protected.')
                ->schema(Schema::string()),

        ];
    }
}
