<?php

namespace App\OpenApi\Schemas;

use GoldSpecDigital\ObjectOrientedOAS\Contracts\SchemaContract;
use GoldSpecDigital\ObjectOrientedOAS\Objects\AllOf;
use GoldSpecDigital\ObjectOrientedOAS\Objects\AnyOf;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Not;
use GoldSpecDigital\ObjectOrientedOAS\Objects\OneOf;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Vyuldashev\LaravelOpenApi\Factories\SchemaFactory;

class ATISAudioFileSchema extends SchemaFactory
{
    /**
     * @return AllOf|OneOf|AnyOf|Not|Schema
     */
    public function build(): SchemaContract
    {
        return Schema::object('ATISAudioFile')
            ->properties(
                Schema::string('id')->default(null),
                Schema::string('icao')->default(null),
                Schema::string('ident')->default(null),
                Schema::string('atis')->default(null),
                Schema::string('output_type')->default('atis'),
                Schema::string('zulu')->default(null),
                Schema::string('url')->default(null),
                Schema::string('file_name')->default(null),
                Schema::string('password')->default(null),
                Schema::string('expires_at')->format(Schema::FORMAT_DATE_TIME)->default(null),
                Schema::string('created_at')->format(Schema::FORMAT_DATE_TIME)->default(null),
                Schema::string('updated_at')->format(Schema::FORMAT_DATE_TIME)->default(null)
            );
    }
}
