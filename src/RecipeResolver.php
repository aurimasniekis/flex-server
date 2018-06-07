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
            if (in_array($this->compareVersion($version, $availableVersion), [0, 1])) {
                return true;
            }
        }

        return false;
    }

    private function getRecipe(string $vendor, string $project, string $version): array
    {
        usort($this->recipes[$vendor][$project]['versions'], 'version_compare');
        $versions = array_reverse($this->recipes[$vendor][$project]['versions']);

        foreach ($versions as $availableVersion) {
            if (in_array($this->compareVersion($version, $availableVersion), [0, 1])) {
                return $this->recipes[$vendor][$project]['recipes'][$availableVersion];
            }
        }

        throw new LogicException('This should never been reached');
    }

    /**
     * Returns 0 if both are equal, 1 if A > B, and -1 if B < A.
     *
     * @param $a
     * @param $b
     *
     * @return int
     */
    private function compareVersion($a, $b)
    {
        $a = explode(".", rtrim($a, ".0"));
        $b = explode(".", rtrim($b, ".0"));

        foreach ($a as $depth => $aVal) {
            if (isset($b[$depth])) {
                if ($aVal > $b[$depth]) return 1;
                else if ($aVal < $b[$depth]) return -1;
            } else {
                return 1;
            }
        }

        return (count($a) < count($b)) ? -1 : 0;
    }
}
