<?php

namespace App\Controller;

use App\SocialMedia\Common\SocialFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class FrontController extends AbstractController
{

    /**
     * Front controller method. Dispatches the current request using a social media client as use case
     * Handles the use case result an returns a json response
     *
     * Catches any unhandled exception and returns a formatted json with the error
     *
     * @param Request $request
     * @param SocialFactory $factory
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function dispatch(Request $request, SocialFactory $factory): JsonResponse
    {
        try {
            $client = $factory->loadSocialMediaClient(
                $request->get('socialMedia'),
                $request->get('clientMethod'),
                $request->query
            );

            $response = $client->{$request->get('clientMethod')}();
            return $this->json($response->getContent(), $response->getHttpCode());
        } catch (\Throwable $throwable) {
            return $this->json((object)['error' => $throwable->getMessage()], 500);
        }

    }

}