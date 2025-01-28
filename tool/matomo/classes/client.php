<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/**
 * Webanalytics class to wrap HTTP requests.
 *
 * @package    watool_matomo
 * @copyright  2023 onwards Catalyst IT EU {@link https://catalyst-eu.net}
 * @author     Simon Adams simon.adams@catalyst-eu.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace watool_matomo;

use core_date;
use curl;
use Exception;
use stdClass;
use Throwable;

/**
 * Wrap Http client.
 */
class client extends curl {
    /**
     * Global config settings to allow the API to function.
     *
     * @var stdClass
     */
    protected $config;

    /**
     * Optional settings for the curl client to use.
     *
     * @var array
     */
    protected $settings;

    /**
     * Simple wrapper HTTP client used for API communication.
     *
     * @param array $settings optional settings for the curl client to use.
     */
    public function __construct(array $settings = []) {
        $this->config = get_config('watool_matomo');
        $this->settings = $settings;
        parent::__construct($settings);
    }

    /**
     * Gets the api url from config.
     *
     * @return string
     * @throws Exception
     */
    private function get_api_url(): string {
        if (empty($this->config->siteurl)) {
            throw new \InvalidArgumentException('No siteurl set in config');
        }

        $url = parse_url($this->config->siteurl);
        if (!empty($url['scheme'])) {
            return $this->config->siteurl;
        }
        return "https://{$this->config->siteurl}";
    }

    /**
     * Build boilerplate request options.
     *
     * @return array
     */
    private function build_request(): array {
        return [
            'module' => 'API',
            'method' => '',
            'format' => 'JSON',
            'token_auth' => $this->config->apitoken,
        ];
    }

    /**
     * Get the site id by url of any site registered on Matomo.
     * If there are multiple, the API will only return the first instance.
     *
     * @param string $url
     * @return int 0 Means it doesn't exist.
     */
    public function get_siteid_from_url(string $url = ''): int {
        global $CFG;

        $request = $this->build_request();
        $request['method'] = 'SitesManager.getSitesIdFromSiteUrl';
        $request['url'] = !empty($url) ? $url : $CFG->wwwroot;
        $rawresponsebody = $this->post($this->get_api_url(), $request);
        $response = $this->validate_response_body($rawresponsebody, $request);
        $response = is_array($response) ? reset($response) : new stdClass();

        return !empty($response->idsite) ? $response->idsite : 0;
    }

    /**
     * Get all registered urls for this siteid.
     *
     * @param int $siteid
     * @return array
     */
    public function get_urls_from_siteid(int $siteid): array {
        $request = $this->build_request();
        $request['method'] = 'SitesManager.getSiteUrlsFromId';
        $request['idSite'] = $siteid;
        $rawresponsebody = $this->post($this->get_api_url(), $request);
        $response = $this->validate_response_body($rawresponsebody, $request);
        return is_array($response) ? $response : [];
    }

    /**
     * Create a site at Matomo instance.
     *
     * @param string $sitename
     * @param string[] $urls
     * @param string $timezone
     * @return int Site id on success, or 0 on failure.
     * @throws Exception
     */
    public function add_site(string $sitename = '', array $urls = [], string $timezone = ''): int {
        global $SITE;

        $request = $this->build_request();
        $request['method'] = 'SitesManager.addSite';
        $request['siteName'] = !empty($sitename) ? $sitename : $SITE->fullname;
        $request['timezone'] = !empty($timezone) ? $timezone : core_date::get_server_timezone();
        $request['excludeUnknownUrls'] = $this->config->matomostricttracking;
        $request = array_merge($request, $this->build_urls_for_request($urls));
        $rawresponse = $this->post($this->get_api_url(), $request);
        $responsebody = $this->validate_response_body($rawresponse, $request);

        return !empty($responsebody->value) ? $responsebody->value : 0;
    }

    /**
     * Update site at Matomo instance.
     *
     * @param int $siteid
     * @param string $sitename
     * @param array $urls A list of all urls for the site. This should include the current urls.
     * @param string $timezone
     * @return bool true for success, false for error
     * @throws Exception
     */
    public function update_site(int $siteid, string $sitename = '', array $urls = [], string $timezone = ''): bool {
        $request = $this->build_request();
        $request['method'] = 'SitesManager.updateSite';
        $request['idSite'] = $siteid;
        $request['siteName'] = $sitename;
        $request['timezone'] = $timezone;
        $request = array_filter($request);
        $request = array_merge($request, $this->build_urls_for_request($urls));
        $rawresponse = $this->post($this->get_api_url(), $request);
        $responsebody = $this->validate_response_body($rawresponse, $request);

        return !empty($responsebody->result) && $responsebody->result === 'success';
    }

    /**
     * Log any errors and return the json decoded result.
     *
     * @param string $responsebody
     * @param array $request
     * @return mixed
     */
    protected function validate_response_body(string $responsebody, array $request) {
        try {
            $responsebody = json_decode($responsebody, false, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable $e) {
            $responsebody = new stdClass();
            $responsebody->result = 'error';
            $responsebody->message = $e->getMessage();
        }

        if (!empty($responsebody->result) && $responsebody->result === 'error') {
            debugging('tool_webanalytics: Matomo API error: ' . $responsebody->message);
        }

        return $responsebody;
    }

    /**
     * Parse an array into the correct associative type for the request.
     *
     * @param array $urls
     * @return array
     */
    private function build_urls_for_request(array $urls): array {
        global $CFG;

        $urls = !empty($urls) ? array_unique($urls) : [$CFG->wwwroot];
        $associative = [];
        $count = 0;
        foreach ($urls as $url) {
            $associative['urls[' . $count . ']'] = $url;
            $count++;
        }

        return $associative;
    }
}
