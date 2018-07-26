<?php

namespace App\Application\Input;

use Symfony\Component\HttpFoundation\ParameterBag;

class ParameterExtractor
{
    /**
     * Extracts requested parameters from the parameter bag
     * Throws exception if the requested parameter not exists
     *
     * @param array $required
     * @param ParameterBag $parameterBag
     * @return array
     * @throws \Exception
     */
    public static function extractRequired(array $required, ParameterBag $parameterBag): array
    {
        $params = [];

        foreach ($required as $reqItem) {
            if (!$parameterBag->has($reqItem)) {
                throw new \Exception('required parameter '.$reqItem. ' not found');
            }
            $params[$reqItem] = $parameterBag->get($reqItem);
        }
        return $params;
    }
}