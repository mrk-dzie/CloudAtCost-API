<?php

use mrk_dzie\CloudAtCostAPI;

class CloudAtCostApiTest extends PHPUnit_Framework_TestCase
{
    protected $CloudAtCostAPI;

    public function setUp()
    {
        $this->CloudAtCostAPI = new CloudAtCostAPI('your.email.address@eg.com', 'CopyHereCloudAtCostApiKey');
    }

    /** @test
     * @expectedException UnexpectedValueException
     */
    public function throws_exception_when_credentials_are_undefined()
    {
        new CloudAtCostAPI(null, -1);
    }

    /** @test */
    public function uses_env_values_as_credentials()
    {
        putenv('CAC_LOGIN=example@example.com');
        putenv('CAC_KEY=SomeRandomApiKey');
        $api = new CloudAtCostAPI(null, null);
        $this->assertEquals(getenv('CAC_LOGIN'), $api->getLogin());
        $this->assertEquals(getenv('CAC_KEY'), $api->getKey());
    }
}