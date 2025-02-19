<?php

declare(strict_types=1);

namespace Core\App\Superglobals;

class Variables
{
    public const TYPE_GLOBALS = 0;
    public const TYPE_SERVER = 1;
    public const TYPE_GET = 2;
    public const TYPE_POST = 3;
    public const TYPE_FILES = 4;
    public const TYPE_COOKIE = 5;
    public const TYPE_SESSION = 6;
    public const TYPE_REQUEST = 7;
    public const TYPE_ENV = 8;

    /**
     * @param int $type
     *
     * @return array<mixed>
     */
    public static function get(int $type): array
    {
        return match ($type) {
            self::TYPE_GLOBALS => $GLOBALS,
            self::TYPE_SERVER => $_SERVER,
            self::TYPE_GET => $_GET,
            self::TYPE_POST => $_POST,
            self::TYPE_FILES => $_FILES,
            self::TYPE_COOKIE => $_COOKIE,
            self::TYPE_SESSION => $_SESSION,
            self::TYPE_REQUEST => $_REQUEST,
            self::TYPE_ENV => $_ENV,
            default => []
        };
    }

    public static function getParamValue(int $type, string $name): mixed
    {
        return self::get($type)[$name] ?? null;
    }
}
