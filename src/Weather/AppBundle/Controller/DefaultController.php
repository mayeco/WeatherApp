<?php

namespace Weather\AppBundle\Controller;

use APY\BreadcrumbTrailBundle\Annotation\Breadcrumb;
use SW\Helper\Controller\Controller as BaseController;
use FOS\RestBundle\Controller\Annotations as Rest;
use GuzzleHttp\Client as GuzzleHttp_Client;


/**
 * @Breadcrumb("Portada", route={"name"="app_index"})
 */
class DefaultController extends BaseController
{
    const VALID_CITY = [
        'santiago,cl' => 'Santiago',
        'oslo,no' => 'Oslo',
        'mexico,mx' => 'Mexico',
        'londres,uk' => 'Londres',
        'beijing,cn' => 'Hong Kong',
        'tokio,jp' => 'Tokio',
    ];

    /**
     * @Rest\Route("/", name="app_index")
     * @Rest\View("WeatherFrontendBundle::Default/index.html.twig")
     */
    public function indexAction()
    {
        return [];
    }
    /**
     * @Rest\Route("/{city}", name="check_whether")
     * @Rest\View("WeatherFrontendBundle::Default/detail.html.twig")
     * @Breadcrumb("{city}")
     */
    public function checkWhetherAction($city)
    {
        if(!in_array($city, array_keys(self::VALID_CITY))) {
            throw new \InvalidArgumentException('Invalid City.');
        }

        $aerisapi_client_id = $this->getParameter('aerisapi_client_id');
        $aerisapi_client_secret = $this->getParameter('aerisapi_client_secret');

        $secret = 'client_id=' . $aerisapi_client_id . '&client_secret=' . $aerisapi_client_secret;
        $url_base = 'https://api.aerisapi.com/forecasts/%s?from=today&to=today&';

        $url = sprintf($url_base . $secret, $city);

        $client = new GuzzleHttp_Client();
        $response = $client->request('GET', $url);

        $body = json_decode($response->getBody(), true);

        if(!$body['success']) {
            throw new \InvalidArgumentException('Error in request.');
        }

        $response = $body['response'];
        $response = $response[0];
        $response = $response['periods'];
        $weather = $response[0];

        return compact('city', 'weather');
    }
}
