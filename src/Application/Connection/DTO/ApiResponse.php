<?php


namespace App\Application\Connection\DTO;


class ApiResponse
{

    protected $httpCode;

    protected $contentType;

    protected $content;

    protected $date;


    public static function createFromCurl(array $curlResponse): ApiResponse
    {
        return new self(
            $curlResponse['http-code'],
            $curlResponse['content-type'],
            $curlResponse['content'],
            new \DateTime($curlResponse['date'])
        );
    }

    public function __construct(int $httpCode, string $contentType, string $content, \DateTime $date)
    {
        $this->httpCode = $httpCode;
        $this->contentType = $contentType;
        $this->content = $this->isResponseOk() ? json_decode($content, true) : $this->getErrorMessage();
        $this->date = $date;
    }

    /**
     * @return int
     */
    public function getHttpCode(): int
    {
        return $this->httpCode;
    }

    /**
     * @return string
     */
    public function getContentType(): string
    {
        return $this->contentType;
    }

    /**
     * @return array
     */
    public function getContent(): array
    {
        return $this->content;
    }

    /**
     * @return \DateTime
     */
    public function getDate(): \DateTime
    {
        return $this->date;
    }

    /**
     * @return bool
     */
    public function isResponseOk(): bool
    {
        return 200 === $this->httpCode;
    }

    /**
     * @return array
     */
    public function getErrorMessage(): array
    {
        $errorMsg = [];
        switch ($this->httpCode) {
            case 401:
                $errorMsg[] = 'Unauthorized Request';
                break;
            case 403:
                $errorMsg[] = 'Forbidden';
                break;
            case 404:
                $errorMsg[] = 'Not Found';
                break;
            case 500:
                $errorMsg[] = 'Internal Server Error from Endpoint';
                break;
            default:
                $errorMsg[] = 'Unknown error with code '.$this->httpCode;
                break;
        }
        return $errorMsg;
    }

    /**
     * @return string
     */
    public function getErrorMessageAsJson(): string
    {
        return json_encode($this->getErrorMessage());
    }
}