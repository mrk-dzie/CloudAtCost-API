<?php

use mrk_dzie\CloudAtCostAPI;

class CloudAtCostApiTest extends PHPUnit_Framework_TestCase
{
    private $CloudAtCostAPI;

    public function setUp()
    {
        try {
            $this->CloudAtCostAPI = new CloudAtCostAPI();
        } catch (Exception $e) {
            $this->CloudAtCostAPI = false;
        }
    }

    /** @test */
    public function check_getters_for_credentials()
    {
        $api = new CloudAtCostAPI('example@example.com', 'SomeRandomApiKey');
        $this->assertEquals('example@example.com', $api->getLogin());
        $this->assertEquals('SomeRandomApiKey', $api->getKey());
    }

    /** @test
     * @expectedException UnexpectedValueException
     */
    public function throws_exception_when_execute_action_with_wrong_credentials()
    {
        $api = new CloudAtCostAPI('wrongLogin', 'wrongKey');
        try {
            $api->getListServers();
        } catch (Exception $e) {
            $this->assertEquals(412, $api->getLastHttpCode());
            throw $e;
        }
    }

    /** @test */
    public function cloudpro_resources_returns_json()
    {
        if ($this->CloudAtCostAPI == false) {
            $this->markTestSkipped('Set the "CAC_LOGIN" and "CAC_KEY" environment variables');
        }
        json_decode($this->CloudAtCostAPI->getResourcesInfo());
        $this->assertEquals(JSON_ERROR_NONE, json_last_error());
    }

    /** @test */
    public function getting_server_list_and_try_to_get_consoleUrl_of_first()
    {
        if ($this->CloudAtCostAPI == false) {
            $this->markTestSkipped('Set the "CAC_LOGIN" and "CAC_KEY" environment variables');
        }

        $serverList = json_decode($this->CloudAtCostAPI->getListServers(), true);
        $this->assertEquals(JSON_ERROR_NONE, json_last_error());

        if (isset($serverList['data'][0]['sid'])) {
            json_decode($this->CloudAtCostAPI->getConsoleUrl($serverList['data'][0]['sid']));
            $this->assertEquals(JSON_ERROR_NONE, json_last_error());
        } else {
            $this->markTestSkipped('You must build at least one server');
        }
    }
}