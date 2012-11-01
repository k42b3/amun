<?php
/*
 *  $Id: Service.php 840 2012-09-11 22:19:37Z k42b3.x@googlemail.com $
 *
 * amun
 * A social content managment system based on the psx framework. For
 * the current version and informations visit <http://amun.phpsx.org>
 *
 * Copyright (c) 2010-2012 Christoph Kappestein <k42b3.x@gmail.com>
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

/**
 * AmunService_Core_Content_Service_Record
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Content_Service
 * @version    $Revision: 840 $
 */
class AmunService_Core_Content_Service_Record extends Amun_Data_RecordAbstract
{
	const NORMAL = 0x1;
	const SYSTEM = 0x2;

	protected $_date;

	public function setId($id)
	{
		$id = $this->_validate->apply($id, 'integer', array(new Amun_Filter_Id($this->_table)), 'id', 'Id');

		if(!$this->_validate->hasError())
		{
			$this->id = $id;
		}
		else
		{
			throw new PSX_Data_Exception($this->_validate->getLastError());
		}
	}

	public function setStatus($status)
	{
		$status = $this->_validate->apply($status, 'string', array(new AmunService_Core_Content_Service_Filter_Status()), 'status', 'Status');

		if(!$this->_validate->hasError())
		{
			$this->status = $status;
		}
		else
		{
			throw new PSX_Data_Exception($this->_validate->getLastError());
		}
	}

	public function setName($name)
	{
		$name = $this->_validate->apply($name, 'string', array(new PSX_Filter_Length(2, 64)), 'name', 'Name');

		if(!$this->_validate->hasError())
		{
			$this->name = strtolower($name);
		}
		else
		{
			throw new PSX_Data_Exception($this->_validate->getLastError());
		}
	}

	public function setType($type)
	{
		$type = $this->_validate->apply($type, 'string', array(new PSX_Filter_Length(7, 256), new PSX_Filter_Url()), 'type', 'Type');

		if(!$this->_validate->hasError())
		{
			$this->type = $type;
		}
		else
		{
			throw new PSX_Data_Exception($this->_validate->getLastError());
		}
	}

	public function setLink($link)
	{
		$link = $this->_validate->apply($link, 'string', array(new PSX_Filter_Length(7, 256), new AmunService_Core_Content_Service_Filter_Link()), 'link', 'Link');

		if(!$this->_validate->hasError())
		{
			$this->link = $link;
		}
		else
		{
			throw new PSX_Data_Exception($this->_validate->getLastError());
		}
	}

	public function setAuthor($author)
	{
		$this->author = $author;
	}

	public function setLicense($license)
	{
		$this->license = $license;
	}

	public function setVersion($version)
	{
		$this->version = $version;
	}

	public function getId()
	{
		return $this->_base->getUrn('content', 'service', $this->id);
	}

	public function getDate()
	{
		if($this->_date === null)
		{
			$this->_date = new DateTime($this->date, $this->_registry['core.default_timezone']);
		}

		return $this->_date;
	}

	public static function getStatus($status = false)
	{
		$s = array(

			self::NORMAL => 'Normal',
			self::SYSTEM => 'System',

		);

		if($status !== false)
		{
			$status = intval($status);

			if(array_key_exists($status, $s))
			{
				return $s[$status];
			}
			else
			{
				return false;
			}
		}
		else
		{
			return $s;
		}
	}
}

