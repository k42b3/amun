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

namespace AmunService\My\Gadget;

use Amun\Module\GadgetAbstract;

/**
 * UserMap
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class UserMap extends GadgetAbstract
{
	/**
	 * onLoad
	 */
	public function onLoad()
	{
		parent::onLoad();

		$chld = array();
		$chd  = array();
		$sql  = <<<SQL
SELECT
	`country`.`id`,
	`country`.`code`,
	COUNT(`country`.`id`) AS `count`
FROM 
	{$this->registry['table.user_account']} `account`
INNER JOIN 
	{$this->registry['table.country']} `country`
	ON `account`.`countryId` = `country`.`id`
GROUP BY 
	`account`.`countryId`
ORDER BY 
	`account`.`id` ASC
SQL;

		$result = $this->getSql()->getAll($sql);

		foreach($result as $row)
		{
			if($row['id'] > 1)
			{
				$chld[] = $row['code'];
				$chd[]  = $row['count'];
			}
		}

		$params = array(

			'cht'  => 't',
			'chs'  => '260x140',
			'chtm' => 'world',
			'chld' => implode('', $chld),
			'chd'  => 't:' . implode(',', $chd),
			'chco' => 'CCCCCC,002200,00EE00'

		);


		$this->display($params);
	}

	private function display(array $params)
	{
		$param = '';

		foreach($params as $k => $v)
		{
			$param.= $k . '=' . $v . '&';
		}

		$param = substr($param, 0, -1);

		echo '<img src="http://chart.apis.google.com/chart?' . $param . '" alt="User Map" />';
	}
}


