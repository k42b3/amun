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

namespace Amun\Api\My;

use Amun\Api\ApiTest;
use PSX\Sql\Condition;
use PSX\Url;
use PSX\Http\GetRequest;

/**
 * VerifyCredentialsTest
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 * @backupStaticAttributes disabled
 */
class VerifyCredentialsTest extends ApiTest
{
	protected function setUp()
	{
		parent::setUp();

		if(!$this->hasService('amun/my'))
		{
			$this->markTestSkipped('Service my not installed');
		}
	}

	public function getEndpoint()
	{
		return $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/my/verifyCredentials';
	}

	public function testGet()
	{
		$url      = new Url($this->getEndpoint() . '?format=xml');
		$response = $this->signedRequest('GET', $url);

		$this->assertEquals(200, $response->getCode());

		$xml = simplexml_load_string($response->getBody());

		// check subject
		$this->assertEquals(true, isset($xml->name));
		$this->assertEquals(getContainer()->get('user')->getName(), (string) $xml->name);
	}
}

