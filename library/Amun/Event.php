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

namespace Amun;

use ReflectionClass;
use ReflectionException;

/**
 * Event
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class Event
{
	protected $container;
	protected $config;
	protected $sql;
	protected $registry;
	protected $user;

	public function __construct($container)
	{
		$this->container = $container;
		$this->config    = $container->get('config');
		$this->sql       = $container->get('sql');
		$this->registry  = $container->get('registry');
	}

	/**
	 * Returns an array of ReflectionClass wich are subscribed to this event
	 *
	 * @return array<ReflectionClass>
	 */
	public function getListener($name)
	{
		$sql = <<<SQL
SELECT
	`event`.`interface`,
	`listener`.`class`
FROM 
	{$this->registry['table.core_event_listener']} `listener`
INNER JOIN 
	{$this->registry['table.core_event']} `event`
	ON `listener`.`eventId` = `event`.`id`
WHERE 
	`event`.`name` = ?
ORDER BY 
	`listener`.`priority` DESC
SQL;

		$result   = $this->sql->getAll($sql, array($name));
		$listener = array();

		foreach($result as $row)
		{
			try
			{
				$class = new ReflectionClass($row['class']);

				if(!empty($row['interface']))
				{
					if($class->implementsInterface($row['interface']))
					{
						$listener[] = new ReflectionClass($row['class']);
					}
				}
				else
				{
					$listener[] = new ReflectionClass($row['class']);
				}
			}
			catch(ReflectionException $e)
			{
				// the class doesnt exist
			}
		}

		return $listener;
	}

	/**
	 * Notifies all listeners of an specific event and passes the $args array
	 * to the notify method. The method is only called if it has the same 
	 * parameter count as the $args array. Optional as third argument the user
	 * under wich the actions should be made
	 *
	 * @param $name
	 * @param array $args
	 * @param Amun\User $user
	 * @return void
	 */
	public function notifyListener($name, array $args, $user = null)
	{
		$listeners = $this->getListener($name);

		foreach($listeners as $listener)
		{
			try
			{
				$method = $listener->getMethod('notify');
				$obj    = $listener->newInstance($this->container, $user);
				$resp   = $method->invokeArgs($obj, $args);

				if($resp === false)
				{
					break;
				}
			}
			catch(ReflectionException $e)
			{
				// the method notify doesnt exist in the listener
			}
			catch(\Exception $e)
			{
				// all other exceptions wich are thrown by the listener. If we
				// are in debug mode redirect the exception
				if($this->config['psx_debug'] === true)
				{
					throw $e;
				}
			}
		}
	}
}

