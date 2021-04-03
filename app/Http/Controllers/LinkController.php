<?php

namespace App\Http\Controllers;

use App\Handlers\LinkHandler;
use Illuminate\Http\Request;

class LinkController
{
    protected $linkHandler;

    public function __construct()
    {
        $this->linkHandler = new LinkHandler();
    }

    public function shorten(Request $request)
    {
        try {
            $shortCode = $this->linkHandler->shortenURL($request);

            $res = new \stdClass();
            $res->shortCode = $shortCode;

            return response()->json($res, 201);
        } catch (\Exception $e) {
            $err = new \stdClass();
            $err->message = $e->getMessage();

            return response()->json($err, $e->getCode());
        }
    }

    public function getByShortCode(string $shortCode)
    {
        try {
            $url = $this->linkHandler->getURLByShortCode($shortCode);

            return response()->json([], 301)->header('Location', $url);
        } catch (\Exception $e) {
            $err = new \stdClass();
            $err->message = $e->getMessage();

            return response()->json($err, $e->getCode());
        }
    }
}
