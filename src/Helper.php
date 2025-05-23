<?php

namespace App;

class Helper
{
    public static function buildUrlForTopics(string $url, array $topics = []): string
    {
        $separator = '?';
        foreach ($topics as $topic) {
            $url .= $separator.'topic='.rawurlencode($topic);
            if ('?' === $separator) {
                $separator = '&';
            }
        }

        return $url;
    }
}
