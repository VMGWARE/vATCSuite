<?php

namespace App\OpenApi\Responses\Airport;

use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Vyuldashev\LaravelOpenApi\Contracts\Reusable;
use Vyuldashev\LaravelOpenApi\Factories\ResponseFactory;

class AtisResponse extends ResponseFactory implements Reusable
{
    public function build(): Response
    {
        $response = Schema::object()->properties(
            Schema::string('status')
                ->example('success'),
            Schema::string('message')
                ->example('ATIS generated successfully.'),
            Schema::integer('code')
                ->example(200),
            Schema::object('data')->properties(
                Schema::string('spoken')
                    ->example('JACKSONVILLE INTERNATIONAL AIRPORT INFORMATION ALPHA...  ONE THREE FIVE SIX ZULU...  WINND TWO FOUR ZERO AT SIX KNOTS...  VISIBILITY 10 OR MORE MILES...  CEILING TWO FIVE THOUSAND BROKEN...  TEMPERATURE TWO FIVE, DEWPOINT ONE SEVEN...  ALTIMETER TWO NINER NINER FOUR, QNH ONE ZERO ONE FOUR...  SIMULTANEOUS ILS AND VISUAL APPROACHES IN USE...  LANDING AND DEPARTING RUNWAY TWO SIX...  TEST...  SESSION ON TFL / VIRGIN XL JOINFS SERVER. SESSION ON TFL FSX MULTIPLAYER SERVER. FIELD IS IFR ONLY. NO EMERGENCIES...  ADVISE CONTROLLER ON INITIAL CONTACT THAT YOU HAVE INFORMATION ALPHA... ')
                    ->description('Spoken ATIS text.'),
                Schema::string('text')
                    ->example('JACKSONVILLE INTERNATIONAL AIRPORT INFORMATION A...  1356Z...  WIND 240 AT 6 KNOTS...  VISIBILITY 10 OR MORE MILES...  CEILING 25000 BROKEN...  TEMPERATURE 25, DEWPOINT 17...  ALTIMETER 2994, QNH 1014...  SIMULTANEOUS ILS AND VISUAL APPROACHES IN USE...  LANDING AND DEPARTING RUNWAY 26...  TEST...  SESSION ON TFL / VIRGIN XL JOINFS SERVER. SESSION ON TFL FSX MULTIPLAYER SERVER. FIELD IS IFR ONLY. NO EMERGENCIES...  ADVISE CONTROLLER ON INITIAL CONTACT THAT YOU HAVE INFORMATION A... ')
                    ->description('Raw ATIS text, without any formatting or converting of letters to words.'),
            )
        );

        return Response::create('AtisSuccess')
            ->description('Successful ATIS text generation.')
            ->content(MediaType::json()->schema($response));
    }
}
