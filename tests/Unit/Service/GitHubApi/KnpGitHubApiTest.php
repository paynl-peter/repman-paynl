<?php

declare(strict_types=1);

namespace Buddy\Repman\Tests\Unit\Service\GitHubApi;

use Buddy\Repman\Service\GitHubApi\KnpGitHubApi;
use Github\Api\CurrentUser;
use Github\Api\CurrentUser\Emails;
use Github\Client;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class KnpGitHubApiTest extends TestCase
{
    /**
     * @var MockObject|Client
     */
    private $clientMock;

    private KnpGitHubApi $api;

    protected function setUp(): void
    {
        $this->clientMock = $this->getMockBuilder(Client::class)->getMock();
        $this->clientMock->expects($this->once())->method('authenticate');

        $this->api = new KnpGitHubApi($this->clientMock);
    }

    public function testReturnPrimaryEmail(): void
    {
        $currentUser = $this->getMockBuilder(CurrentUser::class)->disableOriginalConstructor()->getMock();
        $emails = $this->getMockBuilder(Emails::class)->disableOriginalConstructor()->getMock();

        $currentUser->method('emails')->willReturn($emails);
        $emails->method('all')->willReturn([
            [
                'email' => 'test@buddy.works',
                'verified' => true,
                'primary' => true,
                'visibility' => 'public',
            ], [
                'email' => 'octocat@github.com',
                'verified' => true,
                'primary' => false,
                'visibility' => 'public',
            ],
        ]);
        $this->clientMock->method('__call')->with('currentUser')->willReturn($currentUser);

        self::assertEquals('test@buddy.works', $this->api->primaryEmail('token'));
    }

    public function testThrowExceptionWhenPrimaryEmailNotFound(): void
    {
        $currentUser = $this->getMockBuilder(CurrentUser::class)->disableOriginalConstructor()->getMock();
        $emails = $this->getMockBuilder(Emails::class)->disableOriginalConstructor()->getMock();

        $currentUser->method('emails')->willReturn($emails);
        $emails->method('all')->willReturn([]);
        $this->clientMock->method('__call')->with('currentUser')->willReturn($currentUser);

        $this->expectException(\RuntimeException::class);
        $this->api->primaryEmail('token');
    }
}
