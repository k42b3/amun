<?php
/*
 *  $Id: callback.php 875 2012-09-30 13:51:45Z k42b3.x@googlemail.com $
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

namespace pshb\api;

use AmunService_User_Activity_Handler;
use AmunService_Pshb_Subscription_Record;
use Amun_Dependency_Default;
use Amun_Exception;
use Amun_User;
use Exception;
use PSX_Atom;
use PSX_Base;
use PSX_Data_Exception;
use PSX_PubSubHubBub_CallbackAbstract;
use PSX_Rss;
use PSX_SQL;
use PSX_Sql_Condition;
use PSX_Url;

/**
 * callback
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   module
 * @package    api
 * @subpackage service_my
 * @version    $Revision: 875 $
 */
class callback extends PSX_PubSubHubBub_CallbackAbstract
{
	private $user;

	/**
	 * Endpoint wich receives new content notifications
	 *
	 * @httpMethod POST
	 * @path /
	 * @nickname doContentDistribution
	 * @responseClass PSX_Data_Message
	 */
	public function doContentDistribution()
	{
		try
		{
			$this->handle();
		}
		catch(Exception $e)
		{
			header('HTTP/1.1 404 Not Found');

			echo $e->getMessage();

			if($this->config['psx_debug'] === true)
			{
				echo "\n\n" . $e->getTraceAsString();
			}

			exit;
		}
	}

	public function getDependencies()
	{
		return new Amun_Dependency_Default($this->base->getConfig());
	}

	protected function onAtom(PSX_Atom $atom)
	{
		// find topic url
		$topic = null;

		foreach($atom->link as $link)
		{
			if($link['rel'] == 'self')
			{
				$topic = $link['href'];
			}
		}

		if(empty($topic))
		{
			throw new Amun_Exception('Could not find self link');
		}


		// get topic secret
		$con = new PSX_Sql_Condition();
		$con->add('status', '=', AmunService_Pshb_Subscription_Record::SUBSCRIBE);
		$con->add('topic', '=', $topic);

		$subscription = $this->sql->select($this->registry['table.pshb_subscription'], array('userId', 'secret'), $con, PSX_SQL::SELECT_ROW);

		if(empty($subscription))
		{
			throw new Amun_Exception('Invalid topic');
		}


		// check signature
		$foreignSig = PSX_Base::getRequestHeader('x-hub-signature');

		if(!empty($foreignSig))
		{
			list($method, $signature) = explode('=', $foreignSig);

			$method    = trim($method);
			$signature = trim($signature);

			if(in_array($method, hash_algos()))
			{
				$body = PSX_Base::getRawInput();

				if(strcmp($signature, hash_hmac($method, $body, $subscription['secret'])) === 0)
				{
					// if the signature is valid we have an user
					$this->user = new Amun_User($subscription['userId'], $this->registry);
				}
				else
				{
					throw new PSX_Data_Exception('Invalid signature');
				}
			}
			else
			{
				throw new PSX_Data_Exception('Invalid signature method');
			}
		}
		else
		{
			throw new PSX_Data_Exception('Signature not set');
		}


		// insert entries
		$handler = new AmunService_User_Activity_Handler($this->user);

		foreach($atom as $entry)
		{
			try
			{
				$handler->callback($entry);
			}
			catch(Exception $e)
			{
				// if something fails skip entry
			}
		}
	}

	protected function onRss(PSX_Rss $rss)
	{
		throw new Amun_Exception('Rss is not supported');
	}

	protected function onVerify($mode, PSX_Url $topic, $leaseSeconds, $verifyToken)
	{
		$sql = <<<SQL
SELECT

	`subscription`.`id`,
	`subscription`.`status`,
	`subscription`.`hub`,
	`subscription`.`topic`

	FROM {$this->registry['table.pshb_subscription']} `subscription`

		WHERE `subscription`.`status` = ?

		AND `subscription`.`topic` = ?

			AND `subscription`.`verifyToken` = ?
SQL;

		$row = $this->sql->getRow($sql, array(AmunService_Pshb_Subscription_Record::PENDING, (string) $topic, $verifyToken));

		if(!empty($row))
		{
			switch($mode)
			{
				case 'subscribe':
					$status = AmunService_Pshb_Subscription_Record::SUBSCRIBE;
					break;

				case 'unsubscribe':
					$status = AmunService_Pshb_Subscription_Record::UNSUBSCRIBE;
					break;

				default:
					throw new Amun_Exception('Invalid mode');
					break;
			}


			$con = new PSX_Sql_Condition(array('topic', '=', (string) $topic));

			$this->sql->update($this->registry['table.pshb_subscription'], array(

				'status' => $status,

			), $con);


			return true;
		}
		else
		{
			return false;
		}
	}
}

