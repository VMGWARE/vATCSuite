<?php

namespace App\OpenApi\Responses\Airport;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;
use Vyuldashev\LaravelOpenApi\Factories\ResponseFactory;

class RunwayResponse extends ResponseFactory
{
    public function build(): Response
    {
        return Response::ok()->description('Successful response');
    }
}
