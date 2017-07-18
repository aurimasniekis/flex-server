<?php

namespace AurimasNiekis\FlexServer;

use GuzzleHttp\Client;
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

    public function __construct(Client $client = null, string $endpoint = null)
    {
        $this->client   = $client ?? new Client();
        $this->endpoint = $endpoint ?? 'https://symfony.sh';
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $request = $this->modifyRequest($request);

        $response = $this->client->send($request);

        return $this->modifyResponse($response);
    }

    public function modifyRequest(RequestInterface $request): RequestInterface
    {
        $originalUri = new Uri($this->endpoint);

        $uri = $request->getUri();

        $uri = $uri
            ->withHost($originalUri->getHost())
            ->withPort($originalUri->getPort())
            ->withScheme($originalUri->getScheme());

        return $request->withUri($uri);
    }

    public function modifyResponse(ResponseInterface $response): ResponseInterface
    {
        return $response;
    }
}