<?php

namespace AurimasNiekis\FlexServer;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Thruster\Component\HttpMessage\Uri;

/**
 * Class SymfonyShProxy
 *
 * @package AurimasNiekis\FlexServer
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
class SymfonyShProxy
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var string
     */
    private $endpoint;

    /**
     * @var string
     */
    private $symfonyEndpoint;

    public function __construct(Client $client = null, string $endpoint = null)
    {
        $this->client   = $client ?? new Client();
        $this->symfonyEndpoint = 'https://symfony.sh';
        $this->endpoint = $endpoint ?? $this->symfonyEndpoint;
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $sfRequest = $this->getRequest($request, $this->symfonyEndpoint);

        $sfResponse = $this->client->send($sfRequest);

        $json = $sfResponse->getBody();

        if ($this->symfonyEndpoint != $this->endpoint) {

            $sfArray = \json_decode($json, true);

            $customRequest = $this->getRequest($request, $this->endpoint);

            $customResponse = $this->client->send($customRequest);

            $customJson = $customResponse->getBody();

            $array = \json_decode($customJson, true);

            $merged = \array_merge($array, $sfArray);

            $json = \json_encode($merged);
        }

        $finalResponse = new Response($sfResponse->getStatusCode(), $sfResponse->getHeaders(), $json);

        return $finalResponse;
    }

    public function getRequest(RequestInterface $request, $endpoint): RequestInterface
    {
        $originalUri = new Uri($endpoint);

        $uri = $request->getUri();

        $uri = $uri
            ->withHost($originalUri->getHost())
            ->withPort($originalUri->getPort())
            ->withScheme($originalUri->getScheme());

        return $request->withUri($uri);
    }
}