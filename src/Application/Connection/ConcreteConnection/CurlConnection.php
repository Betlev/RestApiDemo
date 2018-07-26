<?php

namespace App\Application\Connection\ConcreteConnection;

use App\Application\Connection\Connection;
use App\Application\Connection\DTO\ApiRequest;
use App\Application\Connection\DTO\ApiResponse;

/**
 * Class CurlConnection
 * Simple curl wrapper for basic usage
 * @package App\Application\Connection
 */
class CurlConnection implements Connection
{
    /** @var resource */
    protected $curl;

    /**
     * CurlConnection constructor.
     * Initializes a new curl resource
     */
    public function __construct()
    {
        $this->reset();
    }

    /**
     * resets current curl handler, closing the previous one.
     */
    public function reset(): void
    {
        $this->close();
        $this->curl = curl_init();
    }

    /**
     * Executes current curl request
     *
     * @return ApiResponse
     * @throws \Exception on curl error
     */
    public function call(): ApiResponse
    {
        $response = curl_exec($this->curl);
        if (false === $response) {
            throw new \Exception('Error on curl call: '.curl_error($this->curl), curl_errno($this->curl));
        }

        return $this->buildResponse($response);
    }


    protected function isResponseJson(): bool
    {
        $contentType = curl_getinfo($this->curl, CURLINFO_CONTENT_TYPE);

        return strpos($contentType, 'json');
    }

    protected function buildResponse(string $response): ApiResponse
    {
        $responseArray = [];

        list ($headerContent, $content) = explode("\r\n\r\n", $response);

        $responseArray['content'] = $content;
        $responseArray['http-code'] = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);

        $headerFragment = explode("\r\n", $headerContent);
        array_shift($headerFragment);

        foreach ($headerFragment as $line) {
            list ($key, $value) = explode(': ', $line);
            $responseArray[$key] = $value;
        }

        return ApiResponse::createFromCurl($responseArray);
    }


    /**
     * Closes the current curl resource if open
     */
    public function close(): void
    {
        if (is_resource($this->curl)) {
            curl_close($this->curl);
        }
    }

    /**
     * Destructor
     * Closes the curl resource if open
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * sets curl options for request
     * @param ApiRequest $request
     */
    public function configure(ApiRequest $request): void
    {
        curl_setopt_array(
            $this->curl,
            [
                CURLOPT_URL => $request->getFullUrl(),
                CURLOPT_HTTPHEADER => ['Accept: application/json','Authorization: '. $request->getAuthString()],
                CURLOPT_HEADER => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => $request->getHttpVerb(),
                CURLOPT_TIMEOUT => 15,
                CURLOPT_CONNECTTIMEOUT => 15,
            ]
        );
    }
}