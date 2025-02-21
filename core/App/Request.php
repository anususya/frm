<?php

namespace Core\App;

class Request
{
    /**
     * @param bool $sanitized
     *
     * @return array <mixed>
     */
    public static function post(bool $sanitized = true): array
    {
        return Superglobals::Post->getParamsValue([], $sanitized);
    }

    /**
     * @param bool $sanitized
     *
     * @return array<mixed>
     */
    public static function get(bool $sanitized = true): array
    {
        return Superglobals::Get->getParamsValue([], $sanitized);
    }

    /**
     * @param string $name
     * @param bool   $sanitized
     *
     * @return string|null|array<mixed>
     */
    public static function getParam(string $name, bool $sanitized = true): null|array|string
    {
        return Superglobals::Get->getParamValue($name, $sanitized) ?:
            Superglobals::Post->getParamValue($name, $sanitized);
    }
}
