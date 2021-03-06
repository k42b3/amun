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

namespace AmunService\Phpinfo\Api;

use Amun\Module\ApiAbstract;
use Amun\Exception;
use DOMDocument;
use PSX\Data\ArrayList;
use PSX\Data\Message;
use PSX\Data\ResultSet;

/**
 * Index
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class Index extends ApiAbstract
{
	/**
	 * Returns the phpinfo() result
	 *
	 * @httpMethod GET
	 * @path /
	 * @nickname getPhpInfo
	 * @parameter [query startIndex integer]
	 * @parameter [query count integer]
	 * @parameter [query sortBy integer]
	 * @parameter [query sortOrder string ascending|descending]
	 * @parameter [query filterBy integer]
	 * @parameter [query filterOp integer contains|equals|startsWith|present]
	 * @parameter [query filterValue string]
	 * @parameter [query updatedSince DateTime]
	 * @responseClass PSX_Data_ResultSet
	 */
	public function getPhpInfo()
	{
		if($this->user->hasRight('phpinfo_view'))
		{
			try
			{
				$params    = $this->getRequestParams();
				$resultSet = $this->getPhpInfoResult($params['fields'], $params['startIndex'], $params['count'], $params['sortBy'], $params['sortOrder'], $params['filterBy'], $params['filterOp'], $params['filterValue'], $params['updatedSince']);

				$this->setResponse($resultSet);
			}
			catch(\Exception $e)
			{
				$msg = new Message($e->getMessage(), false);

				$this->setResponse($msg);
			}
		}
		else
		{
			$msg = new Message('Access not allowed', false);

			$this->setResponse($msg, null, $this->user->isAnonymous() ? 401 : 403);
		}
	}

	/**
	 * Returns all available fields 
	 *
	 * @httpMethod GET
	 * @path /@supportedFields
	 * @nickname getSupportedFields
	 * @responseClass PSX_Data_Array
	 */
	public function getSupportedFields()
	{
		if($this->user->hasRight('phpinfo_view'))
		{
			try
			{
				$array = new ArrayList(array('group', 'key', 'value'));

				$this->setResponse($array);
			}
			catch(Exception $e)
			{
				$msg = new Message($e->getMessage(), false);

				$this->setResponse($msg);
			}
		}
		else
		{
			$msg = new Message('Access not allowed', false);

			$this->setResponse($msg, null, $this->user->isAnonymous() ? 401 : 403);
		}
	}

	public function onPost()
	{
		$msg = new Message('Create a phpinfo record is not possible', false);

		$this->setResponse($msg, null, 500);
	}

	public function onPut()
	{
		$msg = new Message('Update a phpinfo record is not possible', false);

		$this->setResponse($msg, null, 500);
	}

	public function onDelete()
	{
		$msg = new Message('Delete a phpinfo record is not possible', false);

		$this->setResponse($msg, null, 500);
	}

	private function getPhpInfoResult($fields, $startIndex, $count, $sortBy, $sortOrder, $filterBy, $filterOp, $filterValue, $updatedSince)
	{
		$start = $startIndex !== null ? (integer) $startIndex : 0;
		$count = $count      !== null ? (integer) $count      : 16;

		$dom = new DOMDocument();
		$dom->loadHtml($this->getPhpInfoHtml());

		$result = array();
		$i = $j = $k = 0;
		$tables = $dom->getElementsByTagName('table');

		foreach($tables as $table)
		{
			// get group
			if(isset($table->previousSibling->previousSibling))
			{
				$h2 = $table->previousSibling->previousSibling;

				if($h2->nodeName == 'h2')
				{
					$group = trim($h2->firstChild->textContent);
				}
			}

			if(empty($group))
			{
				$group = 'General';
			}

			// get data
			$trs = $table->getElementsByTagName('tr');

			foreach($trs as $tr)
			{
				$tds   = $tr->getElementsByTagName('td');
				$key   = $tds->item(0) !== null ? $tds->item(0)->textContent : null;
				$value = $tds->item(1) !== null ? $tds->item(1)->textContent : null;

				if(!empty($key) && !empty($value))
				{
					$row = array(
						'group' => $group,
						'key'   => $key,
						'value' => $value,
					);

					// filter if set
					if(isset($row[$filterBy]) && !empty($filterValue))
					{
						$filtered = false;

						switch($filterOp)
						{
							case 'contains':
								$filtered = stripos($row[$filterBy], $filterValue) === false;
								break;

							case 'equals':
								$filtered = strcmp($row[$filterBy], $filterValue) !== 0;
								break;

							case 'startsWith':
								$filtered = substr($row[$filterBy], 0, strlen($filterValue)) != $filterValue;
								break;

							case 'present':
								$filtered = !isset($row[$filterBy]);
								break;
						}

						if($filtered)
						{
							continue;
						}
					}

					if($j < $start)
					{
						$j++;
						$k++;
						continue;
					}

					if($i <= $count)
					{
						$result[] = $row;
					}

					$i++;
					$k++;
				}
			}
		}

		return new ResultSet($k, $start, $count, $result);
	}

	private function getPhpInfoHtml()
	{
		ob_start();

		phpinfo();

		$html = ob_get_contents();

		ob_end_clean();

		return $html;
	}
}
