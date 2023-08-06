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
            Schema::string('status')
                ->example('success')
                ->description('The status of the response indicating the success or failure of the operation.'),
            Schema::string('message')
                ->example('Successfully generated TTS file.')
                ->description('A human-readable message providing additional information about the status.'),
            Schema::integer('code')
                ->example(200)
                ->description('An optional status code for the response. It typically indicates the HTTP status code.'),
            Schema::object('data')->properties(
                Schema::string('id')
                    ->example('6')
                    ->description('The ID of the generated TTS file.'),
                Schema::string('name')
                    ->example('KJAX_ATIS_A_261700Z.mp3')
                    ->description('The filename of the generated TTS file.'),
                Schema::string('url')
                    ->example('/storage/atis/6/KJAX_ATIS_A_261700Z.mp3')
                    ->description('The URL where the generated TTS file can be accessed.'),
            )
        );


        return Response::create('TTSSuccess', 'Successful TTS file generation.')
            ->description('Successful TTS file generation.')
            ->content(MediaType::json()->schema($response));
    }
}
