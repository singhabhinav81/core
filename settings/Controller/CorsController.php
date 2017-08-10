<?php
/**
 * @author Noveen Sachdeva "noveen.sachdeva@research.iiit.ac.in"
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
 */

namespace OC\Settings\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\ILogger;
use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\IUserSession;
use OCP\IConfig;

class CorsController extends Controller {

	/** @var ILogger */
	private $logger;

	/** @var IURLGenerator */
	private $urlGenerator;

	/** @var string */
	private $userId;

	/** @var IConfig */
	private $config;

	/**
	 * SettingsController constructor.
	 *
	 * @param string $AppName The app's name.
	 * @param IRequest $request The request.
	 * @param ILogger $logger The logger.
	 * @param IURLGenerator $urlGenerator Use for url generation
	 * @param IConfig $config
	 */
	public function __construct($AppName, IRequest $request,
								$UserId,
								ILogger $logger,
								IURLGenerator $urlGenerator,
								IConfig $config) {
		parent::__construct($AppName, $request);

		$this->config = $config;
		$this->userId = $UserId;
		$this->logger = $logger;
		$this->urlGenerator = $urlGenerator;
	}

	/**
	 * Gets all White-listed domains
	 *
	 * @return JSONResponse All the White-listed domains
	 */
	public function getDomains() {
		$userId = $this->userId;
		$domains = explode(",", $this->config->getUserValue($userId, 'cors', 'domains'));

		return new JSONResponse($domains);
	}

	/**
	 * WhiteLists a domain for CORS
	 *
	 * @return RedirectResponse Redirection to the settings page.
	 */
	public function addDomain($domain) {
		if (!isset($domain)) {
			return new RedirectResponse(
				$this->urlGenerator->linkToRouteAbsolute(
					'settings.SettingsPage.getPersonal',
					['sectionid' => 'security']
				) . '#cors');
		}
		if (!self::isValidUrl($domain)) {
			return new RedirectResponse(
				$this->urlGenerator->linkToRouteAbsolute(
					'settings.SettingsPage.getPersonal',
					['sectionid' => 'security']
				) . '#cors');
		}

		$userId = $this->userId;
		$domains = explode(",", $this->config->getUserValue($userId, 'cors', 'domains'));
		$domains = array_filter($domains);
		array_push($domains, $domain);
		// In case same domain is added
		$domains = array_unique($domains);
		// Store as comma seperated string
		$domainsString = implode(",", $domains);

		$this->config->setUserValue($userId, 'cors', 'domains', $domainsString);
		$this->logger->debug('The domain "' . $domain . '" has been white-listed.', ['app' => 'cors']);

		return new RedirectResponse(
			$this->urlGenerator->linkToRouteAbsolute(
				'settings.SettingsPage.getPersonal',
				['sectionid' => 'security']
			) . '#cors'
		);
	}

	/**
	 * Removes a WhiteListed Domain
	 *
	 * @param string $domain Domain to remove
	 *
	 * @return RedirectResponse Redirection to the settings page.
	 */
	public function removeDomain($id) {
		$userId = $this->userId;
		$domains = explode(",", $this->config->getUserValue($userId, 'cors', 'domains'));
		unset($domains[$id]);

		$this->config->setUserValue($userId, 'cors', 'domains', implode(",", $domains));

		return new RedirectResponse(
			$this->urlGenerator->linkToRouteAbsolute(
				'settings.SettingsPage.getPersonal',
				['sectionid' => 'security']
			) . '#cors');
	}

	private static function isValidUrl($Url) {
		if (strpos($Url, 'http://localhost:*') === 0) {
			$Url = 'http://localhost' . substr($Url, 18);
		}

		return (filter_var($Url, FILTER_VALIDATE_URL) !== false);

	}

}
