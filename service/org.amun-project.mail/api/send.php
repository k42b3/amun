<?php
/*
 *  $Id: serve.php 880 2012-10-27 13:14:26Z k42b3.x@googlemail.com $
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

namespace mail\api;

use AmunService_Mail_Receiver_Record;
use AmunService_Mail_Sender;
use Amun_Module_ApiAbstract;
use Exception;
use PSX_Data_Message;
use PSX_Data_Record;

/**
 * send
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   module
 * @package    api
 * @subpackage content_media
 * @version    $Revision: 880 $
 */
class send extends Amun_Module_ApiAbstract
{
	/**
	 * Sends an mail template to specific email addresses. The API accepts 
	 * only XML format. Here an example request:
	 *
	 * <mail>
	 * 	<receivers>
	 * 		<template>MY_RECOVER</template>
	 * 		<email>foo@bar.com</email>
	 * 		<values>
	 * 			<param name="account.name" value="foo" />
	 * 			<param name="recover.ip" value="127.0.0.1" />
	 * 		</values>
	 * 	</receivers>
	 * </mail>
	 *
	 * @httpMethod POST
	 * @path /
	 * @nickname doSend
	 * @responseClass PSX_Data_Message
	 */
	public function doSend()
	{
		if($this->getProvider()->hasViewRight())
		{
			try
			{
				$sender = new AmunService_Mail_Sender($this->registry);

				$record = new AmunService_Mail_Receiver_Record();
				$record->import($this->getRequest());

				$receivers = $record->getReceivers();
				$report    = array();

				foreach($receivers as $receiver)
				{
					try
					{
						// try send mail
						$sender->send($receiver['template'], $receiver['email'], $receiver['values']);

						$report[] = array(
							'template' => $receiver['template'],
							'email'    => $receiver['email'],
							'success'  => true,
						);
					}
					catch(Exception $e)
					{
						$report[] = array(
							'template' => $receiver['template'],
							'email'    => $receiver['email'],
							'success'  => false,
							'text'     => $e->getMessage(),
						);
					}
				}

				$record = new PSX_Data_Record('reports', array(
					'report' => $report
				));

				$this->setResponse($record);
			}
			catch(Exception $e)
			{
				$msg = new PSX_Data_Message($e->getMessage(), false);

				$this->setResponse($msg, null, 404);
			}
		}
		else
		{
			$msg = new PSX_Data_Message('Access not allowed', false);

			$this->setResponse($msg, null, $this->user->isAnonymous() ? 401 : 403);
		}
	}
}

