<?php

namespace App\Tests\Application\Connection;

use App\Application\Connection\Connection;
use App\Application\Connection\ConnectionFactory;
use App\Application\Connection\Exception\ConnectionNotFoundException;
use PHPUnit\Framework\TestCase;

class ConnectionFactoryTest extends TestCase
{

    /** @var ConnectionFactory */
    private $connectionFactory;

    protected function setUp()
    {
        $this->connectionFactory = new ConnectionFactory();
    }

    public function testGetConnection()
    {
        //case insensitive
        $instance1 = $this->connectionFactory->getConnection('curl');
        $this->assertInstanceOf(Connection::class, $instance1);
    }

    public function testGetCOnnectionThrowsExceptionIfClassNotFound()
    {
        $this->expectException(ConnectionNotFoundException::class);
        $this->connectionFactory->getConnection('NonExistantConnection');
    }


}
