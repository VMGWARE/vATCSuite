<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class WebTest extends TestCase
{
    /**
     * This is a test function that checks if the home page returns a 200 status code.
     */
    public function test_home_page()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
