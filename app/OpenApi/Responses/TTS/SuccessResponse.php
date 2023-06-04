<?php

namespace App\OpenApi\Responses\TTS;

use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Vyuldashev\LaravelOpenApi\Factories\ResponseFactory;
use Vyuldashev\LaravelOpenApi\Contracts\Reusable;

class SuccessResponse extends ResponseFactory implements Reusable
{
    public function build(): Response
    {
        $response = Schema::object()->properties(
            Schema::string('status')->example('success'),
            Schema::string('message')->example('Successfully generated TTS file.'),
            Schema::integer('code')->example(200),
            Schema::object('data')->properties(
                Schema::string('id')->example('6'),
                Schema::string('name')->example('KJAX_ATIS_A_261700Z.mp3'),
                Schema::string('url')->example('/storage/atis/6/KJAX_ATIS_A_261700Z.mp3'),
            )
        );

        return Response::create('TTSSuccess', 'Successful TTS file generation.')
            ->description('Successful TTS file generation.')
            ->content(MediaType::json()->schema($response));
    }
}
