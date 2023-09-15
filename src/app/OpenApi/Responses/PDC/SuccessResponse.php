<?php

namespace App\OpenApi\Responses\PDC;

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
                ->enum('success', 'error')
                ->description('The status of the response indicating the success or failure of the operation.'),
            Schema::string('message')
                ->example('PDC generated successfully')
                ->description('A human-readable message providing additional information about the status.'),
            Schema::integer('code')
                ->example(200)
                ->description('An optional status code for the response. It typically indicates the HTTP status code.'),
            Schema::object('data')->properties(
                Schema::string('pdc')
                    ->example('PDC FOR AAL123\nDEPARTING KJAX\nARRIVING KDFW\nVIA JAX5 J14 MEI J17 MEM J6 PLESS J180 CYN\nAIRCRAFT B737\nALTITUDE FL350\nSQUAWK 1234\nDEPARTURE TIME 2023-09-15T14:00:00Z\nREMARKS VIP onboard, special meal requests\nEND PDC')
                    ->description('The generated PDC.'),
            )
        );

        return Response::create('GeneratePDC')
            ->description('Generate a Pre-Departure Clearance (PDC) for a flight.')
            ->content(
                MediaType::json()->schema($response)
            );
    }
}
