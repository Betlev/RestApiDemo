<?php

namespace App\SocialMedia\Twitter\Auth;


use App\Application\Connection\DTO\ApiRequest;
use App\Application\Connection\Auth;


class TwitterAuth implements Auth
{
    private const TWITTER_CONSUMER_KEY = 'aaaa';
    private const TWITTER_CONSUMER_SECRET = 'bbbb';
    private const TWITTER_ACCESS_TOKEN = 'cccc';
    private const TWITTER_TOKEN_SECRET = 'dddd';


    /**
     * From Twitter Docs:
     *
     * Creating the signature base string
     * * Convert the HTTP Method to uppercase and set the output string equal to this value.
     * * Append the ‘&’ character to the output string.
     * * Percent encode the URL and append it to the output string.
     * * Append the ‘&’ character to the output string.
     * * Percent encode the parameter string and append it to the output string.
     *
     * Getting a signing key
     * The signing key is simply the percent encoded consumer secret, followed by an
     * ampersand character ‘&’, followed by the percent encoded token secret
     *
     * Calculating the signature
     * The output of the HMAC signing function is a binary string.
     * This needs to be base64 encoded to produce the signature string
     *
     * @param \App\Application\Connection\DTO\ApiRequest $request
     * @return void
     */
    public function sign(ApiRequest $request): void
    {
        $authHeaders = $this->provideAuthParameters();

        $parameterString = $this->createParameterString($authHeaders, $request->getParameters());

        $signatureBaseString = $this->createSignatureBaseString(
            $parameterString, $request->getHttpVerb(), $request->getUrl()
        );

        $signingKey = $this->createSigningKey();

        $authHeaders['oauth_signature'] = base64_encode(
            hash_hmac('sha1', $signatureBaseString, $signingKey, true)
        );

        $request->setAuthParams($authHeaders);
        //we cannot use http_build_query because of the double quotes
        $request->setAuthString('OAuth '.
            implode(', ', $this->arrayKeyConcatEncode($authHeaders, '=', '"'))
        );
    }

    /**
     * @param array $authParameters
     * @param array $rawParameters
     * @return string
     */
    public function createParameterString(array $authParameters, array $rawParameters): string
    {
        $percentEncodedParameters = array_merge($rawParameters, $authParameters);

        uksort($percentEncodedParameters, 'strcmp');

        return http_build_query($percentEncodedParameters, null, '&', PHP_QUERY_RFC3986);
    }

    /**
     * @param string $parameterString
     * @param string $httpVerb
     * @param string $endpoint
     * @return string
     */
    public function createSignatureBaseString(string $parameterString, string $httpVerb, string $endpoint): string
    {
        return implode(
            '&',
            [strtoupper($httpVerb), $this->percentEncode($endpoint),$this->percentEncode($parameterString)]
        );
    }

    /**
     * @return string
     */
    public function createSigningKey(): string
    {
        return implode(
            '&',
            [$this->percentEncode(self::TWITTER_CONSUMER_SECRET), $this->percentEncode(self::TWITTER_TOKEN_SECRET)]
        );
    }


    /**
     * Given an associative array, concatenates each key and value into a single string, becoming values of the returned array
     *
     * Additionally applies the percentEncode() function to the key and value separately.
     *
     * Also, its possible to specify a concatenation char between the percentEncoded key-value pair and
     * a value-surround char that will be added before and after the percentEncoded value
     *
     *
     * @param array $array
     * @param string $concatChar
     * @param string $surroundValue
     * @return array
     */
    private function arrayKeyConcatEncode(array $array, string $concatChar = '', string $surroundValue = ''): array
    {
        return array_map(
            function ($k, $v) use ($concatChar, $surroundValue) {
                return $this->percentEncode($k).$concatChar.$surroundValue.$this->percentEncode($v).$surroundValue;
            },
            array_keys($array),
            $array
        );
    }

    /**
     * Applies a so called "percent encode" by twitter to given string
     * It turns out its actually an URL-encode according to RFC 3986
     *
     * @param string $string
     * @return string
     */
    private function percentEncode(string $string): string
    {
        return rawurlencode($string);
    }

    /**
     * @return array
     */
    public function provideAuthParameters(): array
    {
        return [
            'oauth_consumer_key' => self::TWITTER_CONSUMER_KEY,
            'oauth_nonce' => $this->createNonce(),
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => (new \DateTime())->getTimestamp(),
            'oauth_token' => self::TWITTER_ACCESS_TOKEN,
            'oauth_version' => '1.0',
        ];
    }

    /**
     * Generates a random alphanumeric string
     * @return string
     */
    public function createNonce(): string
    {
        return md5(microtime());
    }

}