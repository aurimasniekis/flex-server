<?php

namespace AurimasNiekis\FlexServer\Console;

use AurimasNiekis\FlexServer\Console\Command\BuildCacheCommand;
use Symfony\Component\Console\Application as BaseApplication;

/**
 * Class Application
 *
 * @package AurimasNiekis\FlexServer\Console
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
class Application extends BaseApplication
{
    public function __construct()
    {
        parent::__construct('Flex Server', '1.0');

        $this->add(new BuildCacheCommand());
    }

}