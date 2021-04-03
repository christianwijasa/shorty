<?php

namespace App\Handlers;

use App\Models\Link;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LinkHandler
{
    private function generateRandomString(int $length): string
    {
        $permittedChars = '_0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        return substr(str_shuffle($permittedChars), 0, $length);
    }

    public function shortenURL(Request $request): string
    {
        if (empty($request->url)) {
            throw new \Exception('url is not present.', 400);
        }

        if (!empty($request->shortCode)) {
            $ok = preg_match('/^[0-9a-zA-Z_]{6}$/', $request->shortCode);
            if (!$ok) {
                throw new \Exception('The shortcode fails to meet the following regexp: ^[0-9a-zA-Z_]{6}$.', 422);
            }
        }

        $shortCode = $request->shortCode;
        if (empty($request->shortCode)) {
            $shortCode = $this->generateRandomString(6);
        }

        $link = Link::where('short_code', $shortCode)->first();
        if (!empty($link)) {
            throw new \Exception('The desired shortcode is already in use. Shortcodes are case-sensitive.', 409);
        }

        $link = Link::create([
            'url' => $request->url,
            'short_code' => $shortCode,
        ]);

        return $link->short_code;
    }

    public function getURLByShortCode(string $shortCode): string
    {
        $link = Link::where('short_code', $shortCode)->first();
        if (empty($link)) {
            throw new \Exception('The shortcode cannot be found in the system.', 404);
        }

        $link->increment('redirect_count', 1, ['last_seen_date' => Carbon::now()]);

        return $link->url;
    }
}
