<?php

namespace App\Tests\SocialMedia\Common;

use App\SocialMedia\Common\Client;
use App\SocialMedia\Common\Exception\IncorrectSocialMediaEndpointException;
use App\SocialMedia\Common\Exception\SocialMediaNotExistsException;
use App\SocialMedia\Common\SocialFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;

class SocialFactoryTest extends TestCase
{
    /** @var SocialFactory */
    private $socialFactory;

    protected function setUp()
    {
        $this->socialFactory = new SocialFactory();
    }

    public function testLoadSocialMediaClient()
    {
        $instance1 = $this->socialFactory->loadSocialMediaClient(
            'Twitter',
            'lastestTweetsFromUser',
            $this->createMock(ParameterBag::class)
        );
        $this->assertInstanceOf(Client::class, $instance1);
        // is case-insensitive
        $instance2 = $this->socialFactory->loadSocialMediaClient('twitter', 'lastestTweetsFromUser', $this->createMock(ParameterBag::class));
        $this->assertInstanceOf(Client::class, $instance2);
    }

    public function testLoadSocialMediaThrowsExceptionOnNonExistantSocialMedia()
    {
        $this->expectException(SocialMediaNotExistsException::class);
        $this->socialFactory->loadSocialMediaClient('NonSocialMedia', 'lastestTweetsFromUser', $this->createMock(ParameterBag::class));
    }

    public function testLoadSocialMediaThrowsExceptionOnWrongMethod()
    {
        $this->expectException(IncorrectSocialMediaEndpointException::class);
        $this->socialFactory->loadSocialMediaClient('twitter', 'wrongMethod', $this->createMock(ParameterBag::class));
    }
}
