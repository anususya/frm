<?php

declare(strict_types=1);

namespace Core\App;

enum Superglobals
{
    use SanitizeData;

    case Globals;
    case Server;
    case Get;
    case Post;
    case Files;
    case Cookie;
    case Session;
    case Request;
    case Env;

    /**
     * @return array<mixed>
     */
    public function get(): array
    {
        return match ($this) {
            Superglobals::Globals => $GLOBALS,
            Superglobals::Server => $_SERVER,
            Superglobals::Get => $_GET,
            Superglobals::Post => $_POST,
            Superglobals::Files => $_FILES,
            Superglobals::Cookie => $_COOKIE,
            Superglobals::Session => $_SESSION,
            Superglobals::Request => $_REQUEST,
            Superglobals::Env => $_ENV
        };
    }

    /**
     * @param array<string> $names
     * @param bool  $sanitize
     *
     * @return array<mixed>
     */
    public function getParamsValue(array $names = [], bool $sanitize = true): array
    {
        if (empty($names)) {
            return $sanitize ? self::cleanParams($this->get()) : $this->get();
        }

        $result = [];

        foreach ($names as $name) {
            $result[$name] = $this->getParamValue($name, $sanitize);
        }

        return $result;
    }

    /**
     * @param string $name
     * @param bool   $sanitize
     *
     * @return null|string|array<mixed>
     */
    public function getParamValue(string $name, bool $sanitize = true): string|array|null
    {
        $value = $this->get()[$name] ?? null;

        if (!$value || !$sanitize) {
            return $value;
        } else {
            return is_string($value) ? self::cleanParam($value) : self::cleanParams($value);
        }
    }
}
