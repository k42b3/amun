<?php
/*
 * amun
 * A social content managment system based on the psx framework. For
 * the current version and informations visit <http://amun.phpsx.org>
 *
 * Copyright (c) 2010-2013 Christoph Kappestein <k42b3.x@gmail.com>
 *
 * This file is part of amun. amun is free software: you can
 * redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation, either version 3 of the License, or any later version.
 *
 * amun is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with amun. If not, see <http://www.gnu.org/licenses/>.
 */

namespace AmunService\Installer;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;

/**
 * ServicePlugin
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class ServicePlugin implements PluginInterface
{
	public function activate(Composer $composer, IOInterface $io)
	{
		// if the autoload.php was already generated use this for autoloading
		// else register a new autoloader
		$file = $composer->getConfig()->get('vendor-dir') . '/autoload.php';

		if(is_file($file))
		{
			include $file;
		}
		else
		{
			// register class loader to load amun classes
			$generator   = $composer->getAutoloadGenerator();
			$classLoader = $generator->createLoader($composer->getPackage()->getAutoload());
			$classLoader->register();
		}

		// register installer
		$installer = new ServiceInstaller($io, $composer);

		$composer->getInstallationManager()->addInstaller($installer);
	}
}
