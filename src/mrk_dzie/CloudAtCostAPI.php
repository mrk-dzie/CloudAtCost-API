<?php namespace mrk_dzie;

use GuzzleHttp\Client;

class CloudAtCostAPI
{
    /**
     * The base URL of CloudAtCost API
     */
    const API_URL = 'https://panel.cloudatcost.com/api/';

    /**
     * Currently used version of CloudAtCost API
     */
    const API_VERSION = 'v1';

    /**
     * The credentials data used to access CloudAtCost API
     */
    protected $credentials = array();

    /**
     * @var false|int $lastHttpResponseCode
     */
    protected $lastHttpResponseCode;

    protected $client;

    /**
     * Set the "CAC_LOGIN" and "CAC_KEY" environment variables or define
     * credentials in the arguments of method.
     * @param string $login
     * @param string $key
     * @throws \UnexpectedValueException
     */
    public function __construct($login = null, $key = null)
    {
        if (!is_string($login) || !is_string($key)) {
            if (is_string(getenv('CAC_LOGIN')) && is_string(getenv('CAC_KEY'))) {
                $login = getenv('CAC_LOGIN');
                $key = getenv('CAC_KEY');
            } else {
                throw new \UnexpectedValueException("Undefined login or key.");
            }
        }
        $this->credentials['login'] = $login;
        $this->credentials['key'] = $key;
        $this->client = new Client(['base_uri' => self::API_URL . self::API_VERSION, 'verify' => false]);
    }

    /**
     * Get the login value
     * @return string
     */
    public function getLogin()
    {
        return $this->credentials['login'];
    }

    /**
     * Get the key value
     * @return string
     */
    public function getKey()
    {
        return $this->credentials['key'];
    }

    /**
     * Get last response HTTP code.
     * @return int
     */
    public function getLastHttpCode()
    {
        return $this->lastHttpResponseCode;
    }

    /**
     * List all servers on the account
     * @return false|string
     */
    public function getListServers()
    {
        return $this->httpRequest('/listservers.php');
    }

    /**
     * List all templates available
     * @return false|string
     */
    public function getListTemplates()
    {
        return $this->httpRequest('/listtemplates.php');
    }

    /**
     * List all tasks in operation
     * @return false|string
     */
    public function getListTasks()
    {
        return $this->httpRequest('/listtasks.php');
    }

    /**
     * Activate server power operations.
     * @param $serverId
     * @param string $action poweron|poweroff|reset
     * @return false|string
     */
    public function powerControl($serverId, $action)
    {
        if ($action == 'poweron' || $action == 'poweroff' || $action == 'reset') {
            $data = array('sid' => $serverId, 'action' => $action);
            return $this->httpRequest('/powerop.php', 'POST', $data);
        }
        throw new \UnexpectedValueException("Unsupported power operation!");
    }

    /**
     * Set the run mode of the server to either 'normal' or 'safe'. Safe automatically turns off the server after 7
     * days of idle usage. Normal keeps it on indefinitely.
     * @param $serverId
     * @param string $mode normal|safe
     * @return false|string
     */
    public function changeRunMode($serverId, $mode)
    {
        if ($mode == 'normal' || $mode == 'safe') {
            $data = array('sid' => $serverId, 'mode' => $mode);
            return $this->httpRequest('/runmode.php', 'POST', $data);
        }
        throw new \UnexpectedValueException("Unsupported run mode!");
    }

    /**
     * Rename the server label
     * @param $serverId
     * @param string $newName
     * @return false|string
     */
    public function renameServer($serverId, $newName)
    {
        $data = array('sid' => $serverId, 'name' => $newName);
        return $this->httpRequest('/renameserver.php', 'POST', $data);
    }

    /**
     * Modify the reverse DNS & hostname of the VPS
     * @param $serverId
     * @param string $hostname
     * @return false|string
     */
    public function changeHostname($serverId, $hostname)
    {
        $data = array('sid' => $serverId, 'hostname' => $hostname);
        return $this->httpRequest('/rdns.php', 'POST', $data);
    }

    /**
     * Request URL for console access
     * @param $serverId
     * @return false|string
     */
    public function getConsoleUrl($serverId)
    {
        return $this->httpRequest('/console.php', 'POST',
            array('sid' => $serverId));
    }

    /**
     * CloudPro: Build a server from available resources
     * @param $cpu
     * @param $ram
     * @param $storage
     * @param $templateID
     * @return false|string
     */
    public function buildServer($cpu, $ram, $storage, $templateID)
    {
        $data = array('cpu' => $cpu, 'ram' => $ram, 'storage' => $storage, 'os' => $templateID);
        return $this->httpRequest('/cloudpro/build.php', 'POST', $data);
    }

    /**
     * CloudPro: Delete / terminate server to add resources.
     * @param $serverId
     * @return false|string
     */
    public function deleteServer($serverId)
    {
        return $this->httpRequest('/cloudpro/delete.php', 'POST',
            array('sid' => $serverId));
    }

    /**
     * CloudPro: Display resources available and resources used in cloud-pro
     * @return false|string
     */
    public function getResourcesInfo()
    {
        return $this->httpRequest('/cloudpro/resources.php');
    }

    /**
     * Returns response of HTTP request
     * @param $url
     * @param string $method
     * @param array|null $data
     * @return string|false
     */
    protected function httpRequest($url, $method = 'GET', array $data = null)
    {
        $data = (is_array($data)) ? array_merge($data, $this->credentials) : $this->credentials;

        if (strcasecmp($method, 'POST') == 0) {
            $response = $this->client->request('POST', $url, ['form_params' => $data]);
        } else { // (strcasecmp($method, 'GET') == 0)
            $response = $this->client->request('GET', $url, ['query' => $data]);
        }
        $this->lastHttpResponseCode = $response->getStatusCode();
        return $response->getBody();
    }
}