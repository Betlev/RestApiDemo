<?php

namespace App\SocialMedia\Common;


use App\Application\Connection\ConnectionFactory;
use App\Application\Connection\ConnectionProvider;
use App\Application\Connection\DTO\DataTransferFactory;
use App\SocialMedia\Common\Exception\IncorrectSocialMediaEndpointException;
use App\SocialMedia\Common\Exception\SocialMediaNotExistsException;
use Symfony\Component\HttpFoundation\ParameterBag;


/**
 * Class SocialFactory
 *
 * Factory method for social media client instantiation
 *
 * Another possible implementation could be a chain of responsibility,
 * by finding the social media client capable of handle the method
 *
 * @package App\SocialMedia\Common
 */
class SocialFactory
{

    /**
     * Factory method for creating social media clients at runtime, based on requested url
     *
     * Checks social media name and client method
     *
     * @param string $socialMediaName
     * @param string $socialMediaMethod
     * @param ParameterBag $parameterBag
     * @throws SocialMediaNotExistsException on social media client name mismatch
     * @throws IncorrectSocialMediaEndpointException on wrong client method request
     * @return Client
     */
    public function loadSocialMediaClient(string $socialMediaName, string $socialMediaMethod, ParameterBag $parameterBag) :Client
    {
        $socialMediaName = ucfirst(strtolower($socialMediaName));
        $ns = str_replace('Common', $socialMediaName, __NAMESPACE__).'\Client';
        $fqcn = $ns.'\\'.$socialMediaName.'Client';

        if (!class_exists($fqcn)) {
            throw new SocialMediaNotExistsException();
        };

        $instance = new $fqcn($parameterBag, new ConnectionProvider(new DataTransferFactory(), new ConnectionFactory()));

        if (!method_exists($instance, $socialMediaMethod) || $socialMediaMethod === 'apiCall') {
            throw new IncorrectSocialMediaEndpointException();
        }

        return $instance;
    }

}