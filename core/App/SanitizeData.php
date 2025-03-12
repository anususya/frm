<?php

declare(strict_types=1);

namespace Core\App;

trait SanitizeData
{
    /**
     * @param array<mixed> $data
     *
     * @return array<mixed>
     */
    public static function cleanParams(array $data): array
    {
        return array_map(function ($value) {
            return self::cleanParam($value);
        }, $data);
    }

    public static function cleanParam(string $data): string
    {
        return trim(htmlspecialchars($data, ENT_QUOTES));
    }
}
