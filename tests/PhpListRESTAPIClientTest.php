<?php

namespace UtagawaVtt\Tests;

use UtagawaVtt\PhpListRESTAPIClient;

use GuzzleHttp\Client as httpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

class PhpListRESTAPIClientTest extends \PHPUnit_Framework_TestCase
{
    protected $clientMock;

    protected $dataStub;

    protected function setUp()
    {
        // error response
        $responseErrorStub = new \stdClass();
        $responseErrorStub->status = 'error';

        // subscriber data response
        $this->dataStub = new \stdClass();
        $this->dataStub->id = 33;
        $this->dataStub->total = 4;

        $responseSuccessStub = new \stdClass();
        $responseSuccessStub->status = 'success';
        $responseSuccessStub->data = $this->dataStub;

        $handlerMock = new MockHandler(array(
            new Response(200, array(), json_encode($responseSuccessStub)),
            new Response(200, array(), json_encode($responseErrorStub)),
        ));
        $handler = HandlerStack::create($handlerMock);

        $this->clientMock = new httpClient(array('handler' => $handler));

        // lists array response
        $list1 = new \stdClass();
        $list1->id = 20;
        $list1->name = 'list1';
        $list2 = new \stdClass();
        $list2->id = 20;
        $list2->name = 'list1';

        $this->dataListsStub = array(0 => $list1, 1 => $list2);

        $responseListsSuccessStub = new \stdClass();
        $responseListsSuccessStub->status = 'success';
        $responseListsSuccessStub->data = $this->dataListsStub;

        $handlerListsMock = new MockHandler(array(
            new Response(200, array(), json_encode($responseListsSuccessStub)),
            new Response(200, array(), json_encode($responseErrorStub)),
        ));
        $handlerLists = HandlerStack::create($handlerListsMock);

        $this->clientListsMock = new httpClient(array('handler' => $handlerLists));
    }

    /**
     */
    public function testLogin()
    {
        $phpList = new PhpListRESTAPIClient('', '', '', '', $this->clientMock);

        $this->assertEquals(true, $phpList->login(), 'First attempt should succeed');
        $this->assertEquals(false, $phpList->login(), 'Second attempt should fail');
    }

    /**
     */
    public function testSubscribe()
    {
        $phpList = new PhpListRESTAPIClient('', '', '', '', $this->clientMock);

        $this->assertEquals(true, $phpList->subscribe('', ''), 'First attempt should succeed');
        $this->assertEquals(false, $phpList->subscribe('', ''), 'Second attempt should fail');
    }

    /**
     */
    public function testSubscriberAdd()
    {
        $phpList = new PhpListRESTAPIClient('', '', '', '', $this->clientMock);

        $this->assertEquals(true, $phpList->subscriberAdd(''), 'First attempt should succeed');
        $this->assertEquals(false, $phpList->subscriberAdd(''), 'Second attempt should fail');
    }

    /**
     */
    public function testSubscriberUpdate()
    {
        $phpList = new PhpListRESTAPIClient('', '', '', '', $this->clientMock);

        $this->assertEquals(true, $phpList->subscriberUpdate('', ''), 'First attempt should succeed');
        $this->assertEquals(false, $phpList->subscriberUpdate('', ''), 'Second attempt should fail');
    }

    /**
     */
    public function testSubscriberDelete()
    {
        $phpList = new PhpListRESTAPIClient('', '', '', '', $this->clientMock);

        $this->assertEquals(true, $phpList->subscriberDelete(''), 'First attempt should succeed');
        $this->assertEquals(false, $phpList->subscriberDelete(''), 'Second attempt should fail');
    }

    /**
     */
    public function testSubscriberGet()
    {
        $phpList = new PhpListRESTAPIClient('', '', '', '', $this->clientMock);

        $this->assertEquals($this->dataStub, $phpList->subscriberGet($this->dataStub->id), 'First attempt should return subscriber data');
        $this->assertEquals(false, $phpList->subscriberGet($this->dataStub->id), 'Second attempt should fail');
    }

    /**
     */
    public function testSubscriberFindByEmail()
    {
        $phpList = new PhpListRESTAPIClient('', '', '', '', $this->clientMock);

        $this->assertEquals($this->dataStub->id, $phpList->subscriberFindByEmail(''), 'First attempt should return subscriber id');
        $this->assertEquals(false, $phpList->subscriberFindByEmail(''), 'Second attempt should fail');
    }

    /**
     */
    public function testSubscriberGetByForeignkey()
    {
        $phpList = new PhpListRESTAPIClient('', '', '', '', $this->clientMock);

        $this->assertEquals($this->dataStub, $phpList->subscriberGetByForeignkey(''), 'First attempt should return subscriber data');
        $this->assertEquals(false, $phpList->subscriberGetByForeignkey(''), 'Second attempt should fail');
    }

    /**
     */
    public function testSubscriberCount()
    {
        $phpList = new PhpListRESTAPIClient('', '', '', '', $this->clientMock);

        $this->assertEquals($this->dataStub->total, $phpList->subscriberCount(), 'First attempt should return subscriber count');
        $this->assertEquals(false, $phpList->subscriberCount(), 'Second attempt should fail');
    }

    /**
     */
    public function testListsGet()
    {
        $phpList = new PhpListRESTAPIClient('', '', '', '', $this->clientListsMock);

        $this->assertEquals($this->dataListsStub, $phpList->listsGet(), 'First attempt should succeed');
        $this->assertEquals(false, $phpList->listsGet(), 'Second attempt should fail');
    }

    /**
     */
    public function testListAdd()
    {
        $phpList = new PhpListRESTAPIClient('', '', '', '', $this->clientMock);

        $this->assertEquals(true, $phpList->listAdd('', ''), 'First attempt should succeed');
        $this->assertEquals(false, $phpList->listAdd('', ''), 'Second attempt should fail');
    }

    /**
     */
    public function testListsSubscriber()
    {
        $phpList = new PhpListRESTAPIClient('', '', '', '', $this->clientMock);

        $this->assertEquals($this->dataStub, $phpList->listsSubscriber(''), 'First attempt should return list data');
        $this->assertEquals(false, $phpList->listsSubscriber(''), 'Second attempt should fail');
    }

    /**
     */
    public function testListSubscriberAdd()
    {
        $phpList = new PhpListRESTAPIClient('', '', '', '', $this->clientMock);

        $this->assertEquals($this->dataStub, $phpList->listSubscriberAdd('', ''), 'First attempt should return list data');
        $this->assertEquals(false, $phpList->listSubscriberAdd('', ''), 'Second attempt should fail');
    }

    /**
     */
    public function testListSubscriberDelete()
    {
        $phpList = new PhpListRESTAPIClient('', '', '', '', $this->clientMock);

        $this->assertEquals($this->dataStub, $phpList->listSubscriberDelete('', ''), 'First attempt should return list data');
        $this->assertEquals(false, $phpList->listSubscriberDelete('', ''), 'Second attempt should fail');
    }
}
