<?php

declare(strict_types=1);

namespace CloudLayer\Tests\Unit\Api;

use CloudLayer\Api\Account;
use CloudLayer\Tests\Unit\TestCase;
use CloudLayer\Types\AccountInfo;
use CloudLayer\Types\StatusResponse;
use PHPUnit\Framework\Attributes\Test;

final class AccountTest extends TestCase
{
    #[Test]
    public function getAccountReturnsAccountInfo(): void
    {
        $http = $this->createMockTransport([
            $this->mockJsonResponse(200, $this->loadFixture('account.json')),
        ]);

        $account = Account::getAccount($http);

        self::assertInstanceOf(AccountInfo::class, $account);
        self::assertSame('user@example.com', $account->email);
        self::assertSame(25.50, $account->credit);
        self::assertTrue($account->subActive);
    }

    #[Test]
    public function getStatusReturnsStatusResponse(): void
    {
        $http = $this->createMockTransport([
            $this->mockJsonResponse(200, $this->loadFixture('status.json')),
        ]);

        $status = Account::getStatus($http);

        self::assertInstanceOf(StatusResponse::class, $status);
        self::assertSame('ok ', $status->status); // trailing space preserved
    }
}
