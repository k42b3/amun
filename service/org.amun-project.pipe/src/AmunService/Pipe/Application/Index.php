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

namespace AmunService\Pipe\Application;

use DateTime;
use Amun\Module\ApplicationAbstract;
use Amun\Exception;
use Amun\Option;
use AmunService\Pipe\Record;
use PSX\Sql;

/**
 * Index
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class Index extends ApplicationAbstract
{
	/**
	 * @httpMethod GET
	 * @path /
	 */
	public function doIndex()
	{
		if($this->user->hasRight('pipe_view'))
		{
			// load pipe
			$recordPipe = $this->getHandler()->getOneByPageId($this->page->getId(), array(), Sql::FETCH_OBJECT);

			$this->template->assign('recordPipe', $recordPipe);


			// check whether user has the media right
			if($recordPipe instanceof Record && (!empty($recordPipe->mediaRightId) && !$this->user->hasRightId($recordPipe->mediaRightId)))
			{
				throw new Exception('Access not allowed');
			}


			// get content
			$embedded = $this->get->embedded('boolean');
			$content  = '';

			if($recordPipe instanceof Record)
			{
				// set header
				header_remove('Expires');
				header_remove('Cache-Control');
				header_remove('Pragma');

				header('Last-Modified: ' . $recordPipe->getLastModified()->format(DateTime::RFC1123));

				// get content
				$content = $recordPipe->getContent();
			}

			if(!$embedded)
			{
				$this->template->assign('data', $content);
			}
			else
			{
				echo $content;
				exit;
			}


			// options
			if($recordPipe instanceof Record)
			{
				$url = $this->service->getApiEndpoint() . '/form?format=json&method=update&id=' . $recordPipe->id;
			}
			else
			{
				$url = $this->service->getApiEndpoint() . '/form?format=json&method=create&pageId=' . $this->page->getId();
			}

			$options = new Option(__CLASS__, $this->registry, $this->user, $this->page);
			$options->add('pipe_edit', 'Edit', 'javascript:amun.services.pipe.showForm(\'' . $url . '\')');
			$options->load(array($this->page));

			$this->template->assign('options', $options);


			// template
			$this->htmlCss->add('pipe');
			$this->htmlJs->add('pipe');
			$this->htmlJs->add('ace');
			$this->htmlJs->add('bootstrap');
			$this->htmlJs->add('prettify');
		}
		else
		{
			throw new Exception('Access not allowed');
		}
	}
}

