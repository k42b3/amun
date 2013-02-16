<?php
/*
 *  $Id: Handler.php 880 2012-10-27 13:14:26Z k42b3.x@googlemail.com $
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
 * Amun_Service_Forum_Handler
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Service_Forum
 * @version    $Revision: 880 $
 */
class AmunService_Forum_Handler extends Amun_Data_HandlerAbstract
{
	public function create(PSX_Data_RecordInterface $record)
	{
		if($record->hasFields('pageId', 'urlTitle', 'title', 'text'))
		{
			$record->globalId = $this->base->getUUID('service:forum:' . $record->pageId . ':' . uniqid());
			$record->userId   = $this->user->id;

			$date = new DateTime('NOW', $this->registry['core.default_timezone']);

			$record->date = $date->format(PSX_DateTime::SQL);


			if(isset($record->sticky) && !$this->user->hasRight('forum_sticky'))
			{
				unset($record->sticky);
			}

			if(isset($record->closed) && !$this->user->hasRight('forum_close'))
			{
				unset($record->closed);
			}


			if(!$this->hasApproval($record))
			{
				$this->table->insert($record->getData());


				$record->id = $this->sql->getLastInsertId();

				$this->notify(Amun_Data_RecordAbstract::INSERT, $record);
			}
			else
			{
				$this->approveRecord(AmunService_Core_Approval_Record::INSERT, $record);
			}

			return $record;
		}
		else
		{
			throw new PSX_Data_Exception('Missing field in record');
		}
	}

	public function update(PSX_Data_RecordInterface $record)
	{
		if($record->hasFields('id'))
		{
			if(!$this->hasApproval($record))
			{
				if(isset($record->sticky) && !$this->user->hasRight('forum_sticky'))
				{
					unset($record->sticky);
				}

				if(isset($record->closed) && !$this->user->hasRight('forum_close'))
				{
					unset($record->closed);
				}


				$con = new PSX_Sql_Condition(array('id', '=', $record->id));

				$this->table->update($record->getData(), $con);


				$this->notify(Amun_Data_RecordAbstract::UPDATE, $record);
			}
			else
			{
				$this->approveRecord(AmunService_Core_Approval_Record::UPDATE, $record);
			}

			return $record;
		}
		else
		{
			throw new PSX_Data_Exception('Missing field in record');
		}
	}

	public function delete(PSX_Data_RecordInterface $record)
	{
		if($record->hasFields('id'))
		{
			if(!$this->hasApproval($record))
			{
				$con = new PSX_Sql_Condition(array('id', '=', $record->id));

				$this->table->delete($con);


				$this->notify(Amun_Data_RecordAbstract::DELETE, $record);
			}
			else
			{
				$this->approveRecord(AmunService_Core_Approval_Record::DELETE, $record);
			}

			return $record;
		}
		else
		{
			throw new PSX_Data_Exception('Missing field in record');
		}
	}

	protected function getDefaultSelect()
	{
		return $this->table
			->select(array('id', 'globalId', 'pageId', 'userId', 'sticky', 'closed', 'urlTitle', 'title', 'text', 'date'))
			->join(PSX_Sql_Join::INNER, Amun_Sql_Table_Registry::get('User_Account')
				->select(array('name', 'profileUrl'), 'author')
			)
			->join(PSX_Sql_Join::INNER, Amun_Sql_Table_Registry::get('Content_Page')
				->select(array('path'), 'page')
			)
			->orderBy('sticky', PSX_Sql::SORT_DESC);
	}
}


