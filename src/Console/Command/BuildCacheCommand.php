<?php

namespace AurimasNiekis\FlexServer\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Class BuildCacheCommand
 *
 * @package AurimasNiekis\FlexServer\Console\Command
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
class BuildCacheCommand extends Command
{
    const IGNORED_FILES = [
        'manifest.json' => true,
    ];

    const FILES_TO_APPEND_TO_MANIFEST = [
        'Makefile' => 'makefile',
        'post-install.txt' => 'post-install-output',
    ];

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
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception(sprintf(
                    'An error ocurred parsing %s: %s',
                    realpath($fileInfo->getPathname()),
                    json_last_error_msg()));
            }

            [$vendor, $project, $version] = explode(DIRECTORY_SEPARATOR, $fileInfo->getRelativePath());

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

            $files = $this->buildFiles($fileInfo->getRealPath());

            $entry = [
                'repository' => 'private',
                'package' => $vendor . '/' . $project,
                'version' => $version,
                'manifest' => $manifest,
                'files' => $files['files'],
                'origin' => $vendor . '/' . $project . ':' . $version . '@private:master',
                'not_installable' => false,
            ];

            $entry['manifest'] = array_merge($entry['manifest'], $files['manifest']);

            $projectRecipes['recipes'][$fullVersion] = $entry;
            $projectRecipes['versions'][] = $fullVersion;

            $recipes[$vendor][$project] = $projectRecipes;
        }

        file_put_contents(__DIR__ . '/../../../data/aliases.json', json_encode($aliases));
        file_put_contents(__DIR__ . '/../../../data/recipes.json', json_encode($recipes));
    }

    private function buildFiles($path): array
    {
        $path = str_replace('manifest.json', '', $path);
        $result = [
            'manifest' => [],
            'files' => []
        ];

        $finder = new Finder();
        /** @var SplFileInfo[] $files */
        $files = $finder->ignoreDotFiles(false)->in($path)->files();

        foreach ($files as $file) {
            if (isset(self::IGNORED_FILES[$file->getRelativePathname()])) {
                continue;
            }

            if (isset(self::FILES_TO_APPEND_TO_MANIFEST[$file->getRelativePathname()])) {
                $fileContent = [];

                foreach (preg_split('/(\r\n|\n|\r)/', $file->getContents()) as $line) {
                    $fileContent[] = $line;
                }
                
                $result['manifest'][self::FILES_TO_APPEND_TO_MANIFEST[$file->getRelativePathname()]] = $fileContent;
                continue;
            }

            $path  = str_replace($path, '', $file->getRelativePathname());

            $result['files'][$path] = [
                'contents' => $file->getContents(),
                'executable' => $file->isExecutable()
            ];
        }

        return $result;
    }
}