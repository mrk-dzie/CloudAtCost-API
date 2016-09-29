<?php namespace mrk_dzie;

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
        return $this->httpRequest(self::API_URL . self::API_VERSION . '/listservers.php');
    }

    /**
     * List all templates available
     * @return false|string
     */
    public function getListTemplates()
    {
        return $this->httpRequest(self::API_URL . self::API_VERSION . '/listtemplates.php');
    }

    /**
     * List all tasks in operation
     * @return false|string
     */
    public function getListTasks()
    {
        return $this->httpRequest(self::API_URL . self::API_VERSION . '/listtasks.php');
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
            return $this->httpRequest(self::API_URL . self::API_VERSION . '/powerop.php', 'POST', $data);
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
            return $this->httpRequest(self::API_URL . self::API_VERSION . '/runmode.php', 'POST', $data);
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
        return $this->httpRequest(self::API_URL . self::API_VERSION . '/renameserver.php', 'POST', $data);
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
        return $this->httpRequest(self::API_URL . self::API_VERSION . '/rdns.php', 'POST', $data);
    }

    /**
     * Request URL for console access
     * @param $serverId
     * @return false|string
     */
    public function getConsoleUrl($serverId)
    {
        return $this->httpRequest(self::API_URL . self::API_VERSION . '/console.php', 'POST',
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
        return $this->httpRequest(self::API_URL . self::API_VERSION . '/cloudpro/build.php', 'POST', $data);
    }

    /**
     * CloudPro: Delete / terminate server to add resources.
     * @param $serverId
     * @return false|string
     */
    public function deleteServer($serverId)
    {
        return $this->httpRequest(self::API_URL . self::API_VERSION . '/cloudpro/delete.php', 'POST',
            array('sid' => $serverId));
    }

    /**
     * CloudPro: Display resources available and resources used in cloud-pro
     * @return false|string
     */
    public function getResourcesInfo()
    {
        return $this->httpRequest(self::API_URL . self::API_VERSION . '/cloudpro/resources.php');
    }

    /**
     * Returns response of HTTP request
     * @param $url
     * @param string $method
     * @param array|null $data
     * @return string|false
     * @throws \HttpRequestMethodException
     */
    protected function httpRequest($url, $method = 'GET', array $data = null)
    {
        $data = (is_array($data)) ? array_merge($data, $this->credentials) : $this->credentials;
        if (strcasecmp($method, 'GET') == 0) {
            $opts = array(
                'ssl' => array(
                    'method' => 'GET',
                    'verify_peer' => false,
                    'verify_peer_name' => false
                )
            );
            $url .= '?' . http_build_query($data);
        } elseif (strcasecmp($method, 'POST') == 0) {
            $opts = array(
                'ssl' => array(
                    'method' => 'POST',
                    'header' => 'Content-type: application/x-www-form-urlencoded',
                    'content' => http_build_query($data),
                    'verify_peer' => false,
                    'verify_peer_name' => false
                )
            );
        } else {
            throw new \UnexpectedValueException("Called unsupported HTTP request method.");
        }

        $ret = @file_get_contents($url, false, stream_context_create($opts));
        if (isset($http_response_header) && is_array($http_response_header)) {
            $this->extractHttpCode($http_response_header);
        } else {
            throw new \UnexpectedValueException("Cannot receive a response.");
        }
        return $ret;
    }

    /**
     * Extract HTTP response code and save it as last occurred
     * @param $http_response_header
     * @throws \UnexpectedValueException
     */
    protected function extractHttpCode($http_response_header)
    {
        $this->lastHttpResponseCode = false;
        foreach ($http_response_header as $value) {
            if (preg_match("#HTTP/[0-9\.]+\s+([0-9]+)#", $value, $o)) {
                $this->lastHttpResponseCode = intval($o[1]);
                break;
            }
        }
        switch ($this->lastHttpResponseCode) {
            case 200:
                break;
            case 400:
                throw new \UnexpectedValueException("Invalid api URL");
                break;
            case 403:
                throw new \UnexpectedValueException("Invalid or missing api key");
                break;
            case 412:
                throw new \UnexpectedValueException("Request failed");
                break;
            case 500:
                throw new \UnexpectedValueException("Internal server error");
                break;
            case 503:
                throw new \UnexpectedValueException("Rate limit hit");
                break;
            default:
                throw new \UnexpectedValueException("Unsupported HTTP response code");
                break;
        }
    }
}