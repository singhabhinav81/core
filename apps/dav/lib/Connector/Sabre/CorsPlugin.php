<?php
/**
 * @author Piotr Mrowczynski <piotr@owncloud.com>
 *
 * @copyright Copyright (c) 2017, ownCloud GmbH
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OCA\DAV\Connector\Sabre;

use Sabre\DAV\Exception\ServiceUnavailable;
use Sabre\DAV\ServerPlugin;

/**
 * Class CorsPlugin is a plugin which adds CORS headers to the responses
 */
class CorsPlugin extends ServerPlugin {

	/**
	 * Reference to main server object
	 *
	 * @var \Sabre\DAV\Server
	 */
	private $server;

	/**
	 * This plugin ensures that all request have CORS enabled
	 */
	public function __construct() {
	}

	/**
	 * This initializes the plugin.
	 *
	 * This function is called by \Sabre\DAV\Server, after
	 * addPlugin is called.
	 *
	 * This method should set up the required event subscriptions.
	 *
	 * @param \Sabre\DAV\Server $server
	 * @return void
	 */
	public function initialize(\Sabre\DAV\Server $server) {
		$this->server = $server;
		$this->server->on('beforeMethod', [$this, 'setCorsHeaders'], 100);
	}

	/**
	 * This method is called before any HTTP method and returns http status code 503
	 * in case the request is incorrect
	 */
	public function setCorsHeaders() {
		$requesterDomain = \OC::$server->getRequest()->server['HTTP_ORIGIN'];
 		$userId = \OC::$server->getRequest()->server['PHP_AUTH_USER'];
 		\OC_Response::setCorsHeaders($userId, $requesterDomain);
	}
}
