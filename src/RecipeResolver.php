<?php

namespace AurimasNiekis\FlexServer;

use LogicException;

/**
 * Class RecipeResolver
 *
 * @package AurimasNiekis\FlexServer
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
class RecipeResolver
{
    /**
     * @var array
     */
    private $recipes;

    public function __construct()
    {
        $this->recipes = [];
        if (file_exists(__DIR__ . '/../data/recipes.json')) {
            $this->recipes = json_decode(file_get_contents(__DIR__ . '/../data/recipes.json'), true);
        }
    }

    public function resolve(array $packages = []): array
    {
        $result = [
            'unresolved' => [],
            'resolved'   => [],
        ];

        foreach ($packages as $package) {
            [$vendor, $project, $version, $timestamp] = explode(',', $package);

            $version = preg_replace('/[iurv]+/', '', $version);

            if (false === $this->isLocal($vendor, $project, $version)) {
                $result['unresolved'][] = $package;

                continue;
            }

            $result['resolved'][$vendor . '/' . $project] = $this->getRecipe($vendor, $project, $version);
        }

        return $result;
    }

    private function isLocal(string $vendor, string $project, string $version): bool
    {
        if (false === $this->isLocalVendor($vendor)) {
            return false;
        }

        if (false === $this->isLocalProject($vendor, $project)) {
            return false;
        }

        if (false === $this->isLocalProjectVersionValid($vendor, $project, $version)) {
            return false;
        }

        return true;
    }

    private function isLocalVendor(string $vendor): bool
    {
        return array_key_exists($vendor, $this->recipes);
    }

    private function isLocalProject(string $vendor, string $project): bool
    {
        return array_key_exists($project, $this->recipes[$vendor]);
    }

    private function isLocalProjectVersionValid(string $vendor, string $project, string $version): bool
    {
        $versions = $this->recipes[$vendor][$project]['versions'];

        foreach ($versions as $availableVersion) {
            if (version_compare($version, $availableVersion, '>=')) {
                return true;
            }
        }

        return false;
    }

    private function getRecipe(string $vendor, string $project, string $version): array
    {
        $versions = $this->recipes[$vendor][$project]['versions'];

        foreach ($versions as $availableVersion) {
            if (version_compare($version, $availableVersion, '>=')) {
                return $this->recipes[$vendor][$project]['recipes'][$availableVersion];
            }
        }

        throw new LogicException('This should never been reached');
    }

    private function filterName(string $name): string
    {
        return preg_replace('/[^A-Za-z0-9\s\-\_]/', '', $name);
    }
}