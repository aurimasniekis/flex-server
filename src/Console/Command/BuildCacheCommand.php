<?php

namespace AurimasNiekis\FlexServer\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

/**
 * Class BuildCacheCommand
 *
 * @package AurimasNiekis\FlexServer\Console\Command
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
class BuildCacheCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('build');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $finder = new Finder();
        $finder->in(__DIR__ . '/../../../recipes/')->name('manifest.json');

        $recipes = [];
        $aliases = [];

        foreach ($finder as $fileInfo) {
            $manifest = json_decode($fileInfo->getContents(), true);

            [$vendor, $project, $version] = explode('/', $fileInfo->getRelativePath());

            if (isset($manifest['aliases'])) {
                foreach ($manifest['aliases'] as $alias) {
                    $aliases[$alias] = $vendor . '/' . $project;
                }

                unset($manifest['aliases']);
            }

            $projectRecipes = $recipes[$vendor][$project] ?? [
                'versions' => [],
                'recipes' => [],
            ];

            $versionParts = explode('.', $version);
            if (count($versionParts) < 3) {
                $versionParts[] = '0';
            }

            $fullVersion = implode('.', $versionParts);

            $projectRecipes['versions'][] = $fullVersion;
            $projectRecipes['recipes'][$fullVersion] = [
                'repository' => 'private',
                'package' => $vendor . '/' . $project,
                'version' => $version,
                'manifest' => $manifest,
                'files' => [

                ],
                'origin' => $vendor . '/' . $project . ':' . $version . '@private:master',
                'not_installable' => false,
            ];

            $recipes[$vendor][$project] = $projectRecipes;
        }

        file_put_contents(__DIR__ . '/../../../data/aliases.json', json_encode($aliases));
        file_put_contents(__DIR__ . '/../../../data/recipes.json', json_encode($recipes));
    }
}