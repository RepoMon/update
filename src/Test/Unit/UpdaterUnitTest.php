<?php

use Ace\Update\Domain\ComposerUpdater;

/**
 * @group unit
 * @author timrodger
 * Date: 26/07/15
 */
class UpdaterUnitTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var
     */
    private $mock_logger;

    private $mock_client;

    private $mock_search_api;

    private $updater;

    private $token = 'abcd1234';

    public function setUp()
    {
        parent::setUp();

        $this->givenAMockClient();
        $this->givenAMockSearchApi();
        $this->givenAMockLogger();
    }

    /**
     */
    public function testRunAuthenticates()
    {
        $this->givenACommand();
        $this->mock_client->expects($this->once())
            ->method('authenticate')
            ->with($this->token, Github\Client::AUTH_HTTP_TOKEN);

        $this->mock_search_api->expects($this->any())
            ->method('code')
            ->will($this->returnValue([]));

        $this->updater->run();
    }

    /**
     */
    public function testRunCatchesSearchExceptions()
    {
        $this->givenACommand();
        $this->mock_client->expects($this->once())
            ->method('authenticate')
            ->with($this->token, Github\Client::AUTH_HTTP_TOKEN);

        $this->mock_search_api->expects($this->any())
            ->method('code')
            ->will($this->throwException(new Github\Exception\ValidationFailedException));

        $this->updater->run();
    }

    private function givenACommand()
    {
        $this->updater = new ComposerUpdater(
            $this->mock_client,
            '/tmp',
            'owner/repo',
            $this->token,
            'master',
            $this->mock_logger
        );
    }

    private function givenAMockClient()
    {
        $this->mock_client = $this->getMockBuilder('Github\Client')
            ->getMock();
    }

    private function givenAMockSearchApi()
    {
        $this->mock_search_api = $this->getMockBuilder('Github\Api\Search')
            ->disableOriginalConstructor()
            ->getMock();

        $this->mock_client->expects($this->any())
            ->method('api')
            ->with('search')
            ->will($this->returnValue($this->mock_search_api));
    }


    private function givenAMockLogger()
    {
        $this->mock_logger = $this->getMockBuilder('Monolog\Logger')
            ->disableOriginalConstructor()
            ->getMock();
    }
}

