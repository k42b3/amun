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

namespace AmunService\My\Application\Settings;

use AmunService\My\SettingsAbstract;
use PSX\Sql;
use PSX\Url;
use PSX\Html\Paging;

/**
 * Application
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class Application extends SettingsAbstract
{
	public function onLoad()
	{
		parent::onLoad();

		// add path
		$this->path->add('Settings', $this->page->getUrl() . '/settings');
		$this->path->add('Application', $this->page->getUrl() . '/settings/application');

		// load allowed applications
		$applications = $this->getApplications();

		$this->template->assign('applications', $applications);

		// form url
		$url = $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/oauth/access';

		$this->template->assign('accessUrl', $url);

		// template
		$this->htmlCss->add('my');
		$this->htmlJs->add('amun');
		$this->htmlJs->add('my');
	}

	public function getApplications()
	{
		$con = $this->getRequestCondition();
		$con->add('authorId', '=', $this->user->getId());
		$con->add('allowed', '=', 1);

		$url   = new Url($this->base->getSelf());
		$count = $url->getParam('count') > 0 ? $url->getParam('count') : 8;
		$count = $count > 16 ? 16 : $count;

		$result = $this->getHandler('AmunService\Oauth\Access')->getResultSet(array(),
			$url->getParam('startIndex'), 
			$count, 
			$url->getParam('sortBy'), 
			$url->getParam('sortOrder'), 
			$con, 
			SQL::FETCH_OBJECT);


		$paging = new Paging($url, $result);

		$this->template->assign('pagingApplications', $paging, 0);


		return $result;
	}
}

