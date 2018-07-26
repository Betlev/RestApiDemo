<?php

namespace App\Application\Connection\DTO;

use App\Application\Connection\Connection;

/**
 * Class ApiRequest
 * Simple DTO class for transfer and encapsulation of request data
 * @package App\Application\Connection
 */
class ApiRequest
{
    /** @var string */
    protected $httpVerb;
    /** @var string */
    protected $url;
    /** @var array */
    protected $parameters;
    /** @var array */
    protected $authParams;
    /** @var string */
    protected $authString;

    /**
     * Request constructor.
     */
    public function __construct()
    {
        $this->httpVerb = Connection::HTTP_GET;
        $this->url = '';
        $this->parameters = [];
        $this->authParams = [];
        $this->authString = '';
    }

    /**
     * @param string $httpVerb
     */
    public function setHttpVerb(string $httpVerb): void
    {
        $this->httpVerb = $httpVerb;
    }

    /**
     * @param string $url
     */
    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    /**
     * @param array $parameters
     */
    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }

    /**
     * @param string $name
     * @param string $value
     */
    public function addParameter(string $name, string $value): void
    {
        $this->parameters[$name] = $value;
    }

    /**
     * @return string
     */
    public function getHttpVerb(): string
    {
        return $this->httpVerb;
    }

    /**
     * @return string
     */
    public function getFullUrl(): string
    {
        $qs = http_build_query($this->parameters,null,'&',PHP_QUERY_RFC3986);
        return $qs !== '' ? $this->url.'?'.$qs : $this->url;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @param array $parameters
     */
    public function appendParameters(array $parameters): void
    {
        $this->parameters = array_merge($this->parameters, $parameters);
    }

    /**
     * @param array $parameters
     */
    public function setAuthParams(array $parameters): void
    {
        $this->authParams = $parameters;
    }

    /**
     * @param string $authString
     */
    public function setAuthString(string $authString): void
    {
        $this->authString = $authString;
    }

    /**
     * @return array
     */
    public function getAuthParams(): array
    {
        return $this->authParams;
    }

    /**
     * @return string
     */
    public function getAuthString(): string
    {
        return $this->authString;
    }


}