<?php

declare(strict_types=1);

namespace App\Utilities;

use InvalidArgumentException;

final class JsonCleaner
{
    public static function extractAndValidateJson(string $response): string
    {
        $response = preg_replace('/```(?:json)?\s*(.*?)\s*```/s', '$1', $response) ?? $response;

        $response = mb_trim($response);

        if (! str_starts_with($response, '{') && ! str_starts_with($response, '[')) {
            if (preg_match('/(\{.*\}|\[.*\])/s', $response, $matches)) {
                $response = $matches[1];
            } else {
                throw new InvalidArgumentException('No valid JSON found in AI response');
            }
        }

        json_decode($response, true, 512, JSON_THROW_ON_ERROR);

        return $response;
    }
}
