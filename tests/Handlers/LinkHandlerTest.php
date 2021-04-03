<?php

namespace Tests\Handlers;

use App\Models\Link;
use Laravel\Lumen\Testing\DatabaseMigrations;
use TestCase;

class LinkHandlerCase extends TestCase
{
    use DatabaseMigrations;

    public function testShortenSuccess()
    {
        $this->json('POST', '/shorten', [
            'url' => 'https://google.com',
            'shortCode' => '_12ABz',
        ])->seeJsonEquals([
            'shortCode' => '_12ABz',
        ])->assertResponseStatus(201);

        $link = Link::where('short_code', '_12ABz')->first();
        $this->assertEquals('https://google.com', $link->url);
        $this->assertEquals('_12ABz', $link->short_code);
        $this->assertEquals(date('Y-m-d'), date('Y-m-d', strtotime($link->start_date)));
        $this->assertEquals(0, $link->redirect_count);
        $this->assertEquals(null, $link->last_seen_date);
    }

    // public function testShortenWithoutShortCode()
    // {
    //     $this->json('POST', '/shorten', [
    //         'url' => 'https://google.com',
    //     ])->assertResponseStatus(201);
    // }

    // public function testShortenExisted()
    // {
    //     Link::create([
    //         'url' => 'testURL',
    //         'short_code' => '_12ABz',
    //     ]);

    //     $this->json('POST', '/shorten', [
    //         'url' => 'https://google.com',
    //         'shortCode' => '_12ABz',
    //     ])->seeJsonEquals([
    //         'message' => 'The desired shortcode is already in use. Shortcodes are case-sensitive.',
    //     ])->assertResponseStatus(409);
    // }

    // public function testShortenWithoutURLParameter()
    // {
    //     $this->json('POST', '/shorten', [
    //         'shortCode' => '_12ABz',
    //     ])->seeJsonEquals([
    //         'message' => 'url is not present.',
    //     ])->assertResponseStatus(400);
    // }

    // public function testShortenShortCodeDoesntPassRegex()
    // {
    //     $this->json('POST', '/shorten', [
    //         'url' => 'https://google.com',
    //         'shortCode' => '!qwert',
    //     ])->seeJsonEquals([
    //         'message' => 'The shortcode fails to meet the following regexp: ^[0-9a-zA-Z_]{6}$.',
    //     ])->assertResponseStatus(422);
    // }
}
