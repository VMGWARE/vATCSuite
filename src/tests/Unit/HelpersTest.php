<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Custom\Helpers;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class HelpersTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_calculates_angle_difference_correctly()
    {
        $result = Helpers::get_angle_diff(10, 350);
        $this->assertEquals(-20, $result);

        $result = Helpers::get_angle_diff(350, 10);
        $this->assertEquals(20, $result);
    }

    //... Similarly, you can write tests for other methods.

    /** @test */
    public function it_validates_icao_code()
    {
        $this->assertTrue(Helpers::validateIcao('KJFK'));
        $this->assertFalse(Helpers::validateIcao('12345'));
    }

    /** @test */
    public function it_parses_metar_for_wind_information()
    {
        $metarString = "XXXX 05015G25KT ..."; // Example METAR string
        $result = Helpers::get_wind($metarString);
        $this->assertEquals('050', $result['dir']);
        $this->assertEquals('15', $result['speed']);
        $this->assertEquals('25', $result['gust_speed']);
    }
}
