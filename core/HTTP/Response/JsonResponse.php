<?php

declare(strict_types=1);

namespace Core\HTTP\Response;

class JsonResponse extends Response
{
    /**
     * @param array<mixed> $data
     * @param int    $statusCode
     * @param array<int|string, mixed>  $headers
     */
    public function __construct(array $data, int $statusCode = 200, array $headers = [])
    {
        // Convert data to JSON
        $jsonContent = (string) json_encode($data);

        // Ensure the response header is set for JSON content type
        $headers['Content-Type'] = 'application/json';

        parent::__construct($jsonContent, $statusCode, $headers);
    }
}
