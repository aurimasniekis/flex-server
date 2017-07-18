<?php

namespace AurimasNiekis\FlexServer;

use Psr\Http\Message\ResponseInterface as R;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Sr;
use Thruster\Component\HttpMessage\Response;
use function Thruster\Component\HttpMessage\stream_for;
use Thruster\Component\HttpResponse\ResponseBuilder;
use Thruster\Component\WebApplication\BaseWebApplication;

/**
 * Class WebApplication
 *
 * @package AurimasNiekis\FlexServer
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
class WebApplication extends BaseWebApplication
{
    /**
     * @var SymfonyShProxy
     */
    private $proxy;

    /**
     * @var RecipeResolver
     */
    private $recipeResolver;

    /**
     * @var string
     */
    private $aliasesFile;

    public function __construct(SymfonyShProxy $proxy = null, RecipeResolver $recipeResolver = null)
    {
        $this->proxy          = $proxy ?? new SymfonyShProxy();
        $this->recipeResolver = $recipeResolver ?? new RecipeResolver();
        $this->aliasesFile    = __DIR__ . '/../data/aliases.json';
    }

    public function getRoutes(): array
    {
        return [
            'ulid'     => ['GET', '/ulid', 'proxyRequestAction'],
            'versions' => ['GET', '/versions.json', 'proxyRequestAction'],
            'aliases'  => ['GET', '/aliases.json', 'aliasesAction'],
            'paths'    => ['GET', '/p/{packages:.*}', 'packagesAction'],

        ];
    }

    public function proxyRequestAction(Sr $request): R
    {
        return $this->proxy->sendRequest($request);
    }

    public function aliasesAction(Sr $request, R $response): R
    {
        $symfonyResponse = $this->proxyRequestAction($request);

        $originalAliases = json_decode($symfonyResponse->getBody()->getContents(), true);
        $aliases         = [];

        if (file_exists($this->aliasesFile)) {
            $aliases = json_decode(file_get_contents($this->aliasesFile), true);
        }

        foreach ($aliases as $alias => $name) {
            $originalAliases[$alias] = $name;
        }

        return ResponseBuilder::init($response)->withJsonBody($originalAliases);
    }

    public function packagesAction(Sr $request, R $response): R
    {
        $routeParams = $request->getAttribute('route_params', []);
        $packages    = $routeParams['packages'] ?? '';
        $packages    = explode(';', $packages);

        $results = $this->recipeResolver->resolve($packages);

        $jsonResponse = [
            'manifests'       => $results['resolved'],
            'vulnerabilities' => [],
        ];

        if (count($results['unresolved']) > 0) {
            $notfoundPackages = implode(';', $results['unresolved']);

            $uri             = $request->getUri()->withPath('/p/' . $notfoundPackages);
            $symfonyResponse = $this->proxyRequestAction($request->withUri($uri));
            $symfonyResponse = json_decode($symfonyResponse->getBody()->getContents(), true);

            $jsonResponse['manifests']       = array_merge(
                $symfonyResponse['manifests'],
                $jsonResponse['manifests']
            );

            $jsonResponse['vulnerabilities'] = array_merge(
                $symfonyResponse['vulnerabilities'],
                $jsonResponse['vulnerabilities']
            );
        }

        return ResponseBuilder::init($response)->withJsonBody($jsonResponse);
    }

    public function handleRouteNotFound(
        Sr $request,
        R $response
    ): R {
        return $response
            ->withAddedHeader('Content-Type', 'text/plain; charset=utf-8')
            ->withBody(stream_for('404 page not found' . PHP_EOL));
    }
}