<?php
/*
 *  $Id: Api.php 683 2012-06-03 11:52:32Z k42b3.x@googlemail.com $
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
 * AmunService_Core_Content_Api_Record
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   AmunService
 * @package    AmunService_Content_Api
 * @version    $Revision: 683 $
 */
class AmunService_Xrds_Record extends Amun_Data_RecordAbstract
{
	protected $_service;

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

	public function setServiceId($serviceId)
	{
		$serviceId = $this->_validate->apply($serviceId, 'integer', array(new Amun_Filter_Id(Amun_Sql_Table_Registry::get('Core_Service'))), 'serviceId', 'Service Id');

		if(!$this->_validate->hasError())
		{
			$this->serviceId = $serviceId;
		}
		else
		{
			throw new PSX_Data_Exception($this->_validate->getLastError());
		}
	}

	public function setPriority($priority)
	{
		$this->priority = $priority;
	}

	public function setEndpoint($endpoint)
	{
		$this->endpoint = $endpoint;
	}

	public function getId()
	{
		return $this->_base->getUrn('content', 'api', $this->id);
	}

	public function getService()
	{
		if($this->_service === null)
		{
			$this->_service = Amun_Sql_Table_Registry::get('Core_Service')->getRecord($this->serviceId);
		}

		return $this->_service;
	}
}
