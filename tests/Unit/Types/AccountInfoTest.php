<?php

declare(strict_types=1);

namespace CloudLayer\Tests\Unit\Types;

use CloudLayer\Tests\Unit\TestCase;
use CloudLayer\Types\AccountInfo;
use PHPUnit\Framework\Attributes\Test;

final class AccountInfoTest extends TestCase
{
    #[Test]
    public function fromArrayWithFullData(): void
    {
        $data = $this->loadFixture('account.json');
        $account = AccountInfo::fromArray($data);

        self::assertSame('user@example.com', $account->email);
        self::assertSame(1000, $account->callsLimit);
        self::assertSame(42, $account->calls);
        self::assertSame('usage', $account->subType);
        self::assertSame(25.50, $account->credit);
        self::assertTrue($account->subActive);
    }

    #[Test]
    public function fromArrayWithExtraFields(): void
    {
        $data = $this->loadFixture('account.json');
        $data['jobCountToday'] = 15;
        $data['jobCountMonth'] = 200;
        $account = AccountInfo::fromArray($data);

        self::assertNotNull($account->extra);
        self::assertSame(15, $account->extra['jobCountToday']);
        self::assertSame(200, $account->extra['jobCountMonth']);
    }

    #[Test]
    public function fromArrayWithUnlimitedComputeTime(): void
    {
        $data = $this->loadFixture('account.json');
        self::assertSame(-1, AccountInfo::fromArray($data)->computeTimeLimit);
    }
}
