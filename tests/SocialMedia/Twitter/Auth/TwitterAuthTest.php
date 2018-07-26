<?php

namespace App\Tests\SocialMedia\Twitter\Connection\Auth;


use App\Application\Connection\DTO\ApiRequest;
use App\SocialMedia\Twitter\Auth\TwitterAuth;
use PHPUnit\Framework\TestCase;

class TwitterAuthTest extends TestCase
{

    /** @var TwitterAuth */
    private $twitterAuth;

    protected function setUp()
    {
        $this->twitterAuth = new TwitterAuth();
    }


    public function testProvideAuthParameters()
    {
        $expectedKeys = [
            'oauth_consumer_key',
            'oauth_nonce',
            'oauth_signature_method',
            'oauth_timestamp',
            'oauth_token',
            'oauth_version',
        ];

        $authParams = $this->twitterAuth->provideAuthParameters();
        $this->assertSame($expectedKeys, array_keys($authParams));
    }


    public function testCreateParameterString()
    {
        $params = ['b' => '1', 'd' => '2', 'a' => '3'];
        $authParams = ['f' => '4', 'c' => '5', 'e' => '6'];

        $result = $this->twitterAuth->createParameterString($authParams, $params);

        $this->assertSame(
            1,
            preg_match('/^a=3&b=1&c=5&d=2&e=6&f=4$/',$result),
            'result must be alphabetically ordered and in http query format'
        );

    }

    public function testCreateSignatureBaseString()
    {
        $result = $this->twitterAuth->createSignatureBaseString(
            'a=3&b=1&c=5&d=2&e=6&f=4',
            'GET',
            'http://www.google.es'
        );

        $this->assertSame(
            1,
            preg_match('/^GET&http%3A%2F%2Fwww.google.es&a%3D3%26b%3D1%26c%3D5%26d%3D2%26e%3D6%26f%3D4$/', $result));
    }

    public function testCreateSigningKey()
    {
        $signingKey = $this->twitterAuth->createSigningKey();
        $this->assertSame(1, preg_match('/^\w+&\w+$/', $signingKey));
    }


    public function testSign()
    {
        // we want to ensure the returned header string is well-formed
        // (ie: contains the exact number of oauth_ keys, in the correct order...)
        // the concrete values are irrelevant: for those we have tests who ensure that

        $regex =
            '/^OAuth oauth_consumer_key="\w+",' .
            ' oauth_nonce="\w+",' .
            ' oauth_signature_method="HMAC-SHA1",' .
            ' oauth_timestamp="\d{10}",' .
            ' oauth_token="\d+-\w+",' .
            ' oauth_version="1\.0",'.
            ' oauth_signature="[A-Za-z0-9&%]+"/';

        $apiRequest = new ApiRequest();
        $apiRequest->setUrl('https://api.twitter.com/1.1/search/tweets.json');
        $requestParams = [
            'count' => '10',
            'q' => 'from:twitterdev',
            'result_type' => 'recent'
        ];
        $apiRequest->setParameters($requestParams);

        $this->twitterAuth->sign($apiRequest);
        // preg_match returns int 1 in case of match
        $this->assertSame(1, preg_match($regex, $apiRequest->getAuthString()),
            "bad-formed authorization header string: unable to match\n".$apiRequest->getAuthString()."\nagainst regex\n". $regex);
        echo "\n".'curl --request GET --url \''.$apiRequest->getFullUrl().'\' --header \'Authorization: '. $apiRequest->getAuthString().'\'';
    }


    public function testCreateNonce()
    {
        $nonce1 = $this->twitterAuth->createNonce();
        $nonce2 = $this->twitterAuth->createNonce();

        $this->assertFalse($nonce1 === $nonce2,
            'nonce generation should be random, or at least not same as just the previous one generated');
    }

    /**
     * Regression test for bug on api calls where the query parameters are triple-encoded
     * when forming the parameter string and signature base string
     *
     * First encode transforms string from:twitterdev to from%3Atwitterdev
     * this encode MUST occur when forming the parameter string
     *
     * A second encoding (as required by twitter authentication method)
     * encodes the percent symbol, so  from%3Atwitterdev becomes from%253Atwitterdev
     * this occurs when forming the complete signature base string
     *
     * this test watches over a bug when the encoding occurred a third time
     * under some circumstances, like in the example, the parameter becomes from%25253Atwitterdev (note the double 25)
     * rendering as incorrect all the signature base string, thus making the request fail
     *
     */
    public function testSignatureBaseStringDoesNotEncodesThreeTimesPercentSymbol()
    {

        $requestParams = [
            'count' => '10',
            'q' => 'from:twitterdev',
            'result_type' => 'recent'
        ];

        $apiRequest = new ApiRequest();
        $apiRequest->setUrl('https://api.twitter.com/1.1/search/tweets.json');
        $apiRequest->setParameters($requestParams);

        $mysign = $this->twitterAuth->createSignatureBaseString(
            $this->twitterAuth->createParameterString(
                $this->twitterAuth->provideAuthParameters(),
                $requestParams
                ),
            $apiRequest->getHttpVerb(),
            $apiRequest->getUrl()
        );
        $this->assertSame(0, preg_match('/from%25253Atwitterdev/', $mysign));
        $this->assertSame(1, preg_match('/from%253Atwitterdev/', $mysign));
    }
}
