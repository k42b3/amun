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

namespace Amun\Gadget;

use Amun\Registry;
use Amun\User;
use Amun\Page;
use Amun\Html;
use ArrayObject;
use PSX\Loader;
use PSX\Sql;

/**
 * Container
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class Container extends ArrayObject
{
	private $config;
	private $sql;
	private $registry;
	private $user;

	private $gadgets = array();
	private $iterator;

	public function __construct(Registry $registry, User $user)
	{
		$this->config   = $registry->getConfig();
		$this->sql      = $registry->getSql();
		$this->registry = $registry;
		$this->user     = $user;
	}

	public function valid()
	{
		return $this->iterator->valid();
	}

	/**
	 * Returns the gadget with the given name or the current gadget
	 *
	 * @return Amun_Gadget_Item
	 */
	public function get($name = null)
	{
		if($name !== null)
		{
			foreach($this as $gadget)
			{
				if($gadget->getName() == $name)
				{
					return $gadget;
				}
			}

			return false;
		}
		else
		{
			return $this->iterator->current();
		}
	}

	public function next()
	{
		$this->iterator->next();
	}

	/**
	 * Loads all gadgets for the specific page and adds the required css files
	 *
	 * @param PSX_Loader $loader
	 * @param Amun_Page $page
	 * @param Amun_Html_Css $htmlCss
	 * @return void
	 */
	public function load(Loader $loader, Page $page, Html\Css $htmlCss)
	{
		$this->exchangeArray($this->gadgets = array());

		$sql = <<<SQL
SELECT
	`pageGadget`.`id`,
	`gadget`.`rightId`,
	`gadget`.`type`,
	`gadget`.`name`,
	`gadget`.`title`,
	`gadget`.`class`,
	`gadget`.`cache`,
	`gadget`.`expire`,
	`service`.`name` AS `serviceName`
FROM 
	{$this->registry['table.content_page_gadget']} `pageGadget`
INNER JOIN 
	{$this->registry['table.content_gadget']} `gadget`
	ON `pageGadget`.`gadgetId` = `gadget`.`id`
INNER JOIN 
	{$this->registry['table.core_service']} `service`
	ON `gadget`.`serviceId` = `service`.`id`
WHERE 
	`pageGadget`.`pageId` = ?
ORDER BY 
	`pageGadget`.`sort` ASC
SQL;

		$result = $this->sql->getAll($sql, array($page->getId()), Sql::FETCH_OBJECT, '\Amun\Gadget\Item', array($this->config, $loader));

		foreach($result as $gadget)
		{
			// execute gadget
			try
			{
				if(empty($gadget->rightId) || $this->user->hasRightId($gadget->rightId))
				{
					// append css
					$parts = explode('/', $gadget->serviceName);
					$name  = end($parts);

					$htmlCss->add($name);

					// build content
					$gadget->buildContent();

					$this->append($gadget);
				}
			}
			catch(Exception $e)
			{
				$msg = $e->getMessage();

				if($this->config['psx_debug'] === true)
				{
					$msg.= "\n\n" . $e->getTraceAsString();
				}

				$gadget->setBody($msg);

				$this->append($gadget);
			}
		}

		$this->iterator = $this->getIterator();
		$this->iterator->rewind();
	}
}

