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

/**
 * RegistryTest
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 * @backupStaticAttributes disabled
 */
class RegistryTest extends HandlerTest
{
	public function testGetClassNameFromTable()
	{
		$this->assertEquals('AmunService\Core\Service', $this->registry->getClassNameFromTable('amun_core_service'));
		$this->assertInstanceOf('AmunService\Core\Service\Handler', $this->getHandler($this->registry->getClassNameFromTable('amun_core_service')));
	}

	public function testHasService()
	{
		$this->assertEquals(false, $this->registry->hasService(null));
		$this->assertEquals(false, $this->registry->hasService('foo'));

		$services = $this->sql->getCol('SELECT `name` FROM ' . $this->registry['table.core_service']);

		foreach($services as $serviceName)
		{
			$this->assertEquals(true, $this->registry->hasService($serviceName));
		}
	}
}
