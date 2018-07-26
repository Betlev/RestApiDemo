<?php


namespace App\Application\Connection;

use App\Application\Connection\DTO\ApiRequest;
use App\Application\Connection\DTO\ApiResponse;
use App\Application\Connection\DTO\DataTransferFactory;


/**
 * Class ConnectionProvider
 *
 * Uses a fluent factory/builder pattern to provide abstraction on the request/signature/connectivity phase.
 *
 * This way, the classes used for making api calls can vary independently,
 * without affecting the clients or other parts of our code
 *
 * Maybe by substituting these custom classes for the gluzzle http/promises lib.
 * @package App\Application\Connection
 */
class ConnectionProvider
{
    public const TYPE_CURL = 'Curl';

    /** @var Connection */
    protected $connection;
    /** @var ApiRequest */
    protected $apiRequest;
    /** @var Auth */
    protected $auth;
    /** @var DataTransferFactory */
    private $dataTransferFactory;
    /**
     * @var ConnectionFactory
     */
    private $connectionFactory;

    /**
     * ConnectionProvider constructor.
     * @param DataTransferFactory $dataTransferFactory
     * @param ConnectionFactory $connectionFactory
     */
    public function __construct(DataTransferFactory $dataTransferFactory, ConnectionFactory $connectionFactory)
    {
        $this->dataTransferFactory = $dataTransferFactory;
        $this->connectionFactory = $connectionFactory;
        $this->reset();
    }

    /**
     *
     */
    public function reset(): void
    {
        $this->apiRequest = $this->dataTransferFactory->createRequest();
        $this->connection = null;
        $this->auth = null;
    }

    /**
     * @param $url
     * @return ConnectionProvider
     */
    public function destination(string $url): ConnectionProvider
    {
        $this->apiRequest->setUrl($url);
        return $this;
    }

    /**
     * @param $httpVerb
     * @return ConnectionProvider
     */
    public function httpVerb(string $httpVerb): ConnectionProvider
    {
        $this->apiRequest->setHttpVerb($httpVerb);
        return $this;
    }

    /**
     * @param array $parameters
     * @return ConnectionProvider
     */
    public function queryParameters(array $parameters): ConnectionProvider
    {
        $this->apiRequest->setParameters($parameters);
        return $this;
    }

    /**
     * @param Auth $auth
     * @return ConnectionProvider
     */
    public function withSignature(Auth $auth): ConnectionProvider
    {
        $this->auth = $auth;
        return $this;
    }

    /**
     * @return bool
     */
    public function isConnectionReady(): bool
    {
        return $this->connection instanceof Connection;
    }

    /**
     * @return bool
     */
    public function isRequestSigned(): bool
    {
        return $this->auth instanceof Auth;
    }

    /**
     * @param string $type
     * @return ApiResponse
     * @throws Exception\ConnectionNotFoundException
     */
    public function execute(string $type = self::TYPE_CURL): ApiResponse
    {
        if (!$this->isConnectionReady()) {
            $this->getConnection($type);
        }

        if ($this->isRequestSigned()) {
            $this->auth->sign($this->apiRequest);
        }

        return $this->performCall();
    }

    /**
     * @return ApiResponse
     */
    protected function performCall(): ApiResponse
    {
        $this->connection->configure($this->apiRequest);
        $apiResponse = $this->connection->call();
        $this->connection->close();
        return $apiResponse;
    }

    /**
     * @param string $type
     * @throws Exception\ConnectionNotFoundException
     */
    protected function getConnection(string $type): void
    {
        $this->connection = $this->connectionFactory->getConnection($type);
    }
}
