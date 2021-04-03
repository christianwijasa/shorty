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

    public function testShortenWithoutShortCode()
    {
        $this->json('POST', '/shorten', [
            'url' => 'https://google.com',
        ])->assertResponseStatus(201);
    }

    public function testShortenExisted()
    {
        Link::create([
            'url' => 'testURL',
            'short_code' => '_12ABz',
        ]);

        $this->json('POST', '/shorten', [
            'url' => 'https://google.com',
            'shortCode' => '_12ABz',
        ])->seeJsonEquals([
            'message' => 'The desired shortcode is already in use. Shortcodes are case-sensitive.',
        ])->assertResponseStatus(409);
    }

    public function testShortenWithoutURLParameter()
    {
        $this->json('POST', '/shorten', [
            'shortCode' => '_12ABz',
        ])->seeJsonEquals([
            'message' => 'url is not present.',
        ])->assertResponseStatus(400);
    }

    public function testShortenShortCodeDoesntPassRegex()
    {
        $this->json('POST', '/shorten', [
            'url' => 'https://google.com',
            'shortCode' => '!qwert',
        ])->seeJsonEquals([
            'message' => 'The shortcode fails to meet the following regexp: ^[0-9a-zA-Z_]{6}$.',
        ])->assertResponseStatus(422);
    }

    public function testGetByShortCodeSuccess()
    {
        Link::create([
            'url' => 'https://google.com',
            'short_code' => 'abcdef',
        ]);

        $this->json('GET', '/abcdef')
            ->seeHeader('Location', 'https://google.com')
            ->assertResponseStatus(301);

        $link1 = Link::where('short_code', 'abcdef')->first();
        $this->assertEquals(1, $link1->redirect_count);

        sleep(1);

        $this->json('GET', '/abcdef')
            ->seeHeader('Location', 'https://google.com')
            ->assertResponseStatus(301);

        $link2 = Link::where('short_code', 'abcdef')->first();
        $this->assertEquals(2, $link2->redirect_count);
        $this->assertTrue(strtotime($link2->last_seen_date) > strtotime($link1->last_seen_date));
    }

    public function testGetByShortCodeNotFound()
    {
        $this->json('GET', '/abcdef')
            ->seeJsonEquals([
                'message' => 'The shortcode cannot be found in the system.',
            ])->assertResponseStatus(404);
    }

    public function testGetStatsByShortCodeSuccess()
    {
        Link::create([
            'url' => 'https://google.com',
            'short_code' => 'abcdef',
        ]);

        $link = Link::where('short_code', 'abcdef')->first();

        $this->json('GET', '/abcdef/stats')
            ->seeJson([
                "startDate" => $link->start_date,
                "lastSeenDate" => null,
                "redirectCount" => 0,
            ])->assertResponseStatus(200);
    }

    public function testGetStatsByShortCodeNotFound()
    {
        $this->json('GET', '/abcdef/stats')
            ->seeJsonEquals([
                'message' => 'The shortcode cannot be found in the system.',
            ])->assertResponseStatus(404);
    }
}
