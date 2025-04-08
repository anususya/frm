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

    public static function cleanParam(mixed $data): mixed
    {
        return is_string($data) ? trim(htmlspecialchars($data, ENT_QUOTES)) : $data;
    }
}
