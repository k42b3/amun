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

namespace AmunService\Login\Application;

use Amun\Module\ApplicationAbstract;
use AmunService\Login\HandlerFactory;
use AmunService\Login\Handler\CallbackInterface;

/**
 * Callback
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class Callback extends ApplicationAbstract
{
	/**
	 * @httpMethod GET
	 * @path /{loginHandler}
	 */
	public function doGetCallback()
	{
		$this->handleCallback();
	}

	/**
	 * @httpMethod POST
	 * @path /{loginHandler}
	 */
	public function doPostCallback()
	{
		$this->handleCallback();
	}

	protected function handleCallback()
	{
		$handler = $this->getUriFragments('loginHandler');
		$handler = HandlerFactory::factory($handler, $this->container);

		if($handler instanceof CallbackInterface)
		{
			try
			{
				$handler->setPageUrl($this->page->getUrl());
				$handler->callback();
			}
			catch(\Exception $e)
			{
				$this->template->assign('error', $e->getMessage());
			}
		}
		else
		{
			$this->template->assign('error', 'Invalid callback handler');
		}
	}
}
