<?php

declare(strict_types=1);

namespace CloudLayer\Api;

use CloudLayer\HttpTransport;
use CloudLayer\Types\AccountInfo;
use CloudLayer\Types\StatusResponse;

/**
 * Account API methods.
 */
final class Account
{
    public static function getAccount(HttpTransport $http): AccountInfo
    {
        $response = $http->get('/account', ['retryable' => true]);

        /** @var array<string, mixed> $data */
        $data = $response->data;

        return AccountInfo::fromArray($data);
    }

    public static function getStatus(HttpTransport $http): StatusResponse
    {
        $response = $http->get('/getStatus', ['retryable' => true]);

        /** @var array<string, mixed> $data */
        $data = $response->data;

        return StatusResponse::fromArray($data);
    }

    private function __construct()
    {
    }
}
