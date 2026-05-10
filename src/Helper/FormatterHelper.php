<?php

namespace Smerteliko\MicroCli\Helper;

class FormatterHelper
{
    public function truncate(string $message, int $length, string $suffix = '...'): string
    {
        $cleanMessage = strip_tags($message);

        if (mb_strlen($cleanMessage) <= $length) {
            return $message;
        }

        $computedLength = $length - mb_strlen($suffix);

        return mb_substr($cleanMessage, 0, $computedLength) . $suffix;
    }

    public function formatSection(string $section, string $message, string $style = 'info'): string
    {
        return sprintf('<%s>[%s]</%s> %s', $style, $section, $style, $message);
    }
}