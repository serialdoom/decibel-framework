<?php
namespace tests\app\decibel\event;

use app\decibel\event\DDispatchable;
use app\decibel\event\debug\DDuplicateSubscriptionException;
use app\decibel\event\DEventDispatcher;
use app\decibel\test\DTestCase;

class TestDispatcher implements DDispatchable
{
    use DEventDispatcher;

    const TESTEVENT = 'tests\\app\\decibel\\event\\TestEvent';

    public static function testgetDispatcherHierarchy()
    {
        return static::getDispatcherHierarchy();
    }

    public static function testgetObserverRegistrations($dispatcher, $event = null)
    {
        return static::getObserverRegistrations($dispatcher, $event);
    }

    public static function testisSubscribed($event, $observer, $eventData = null)
    {
        $qualifiedName = get_called_class();
        $subscriptions =& self::getObserverRegistrations($qualifiedName, $event);

        return static::isSubscribed($subscriptions, $observer, $eventData);
    }

    public static function testvalidateEvent(&$event)
    {
        return static::validateEvent($event);
    }

    public static function getDefaultEvent()
    {
        return TestDispatcher::TESTEVENT;
    }

    public static function getEvents()
    {
        return array(TestDispatcher::TESTEVENT);
    }
}

class TestChildDispatcher extends TestDispatcher
{
}

class TestObserver
{
    public static function doSomething()
    {
    }
}

/**
 * Test class for DEventDispatcher.
 * Generated by Decibel on 2011-10-31 at 14:08:29.
 */
class DEventDispatcherTest extends DTestCase
{
    /**
     * @covers app\decibel\event\DEventDispatcher::getObserverRegistrations
     */
    public function testgetObserverRegistrations_newDispatcher()
    {
        $this->assertSame(array(), TestDispatcher::testgetObserverRegistrations('dispatcher1'));
    }

    /**
     * @covers app\decibel\event\DEventDispatcher::getObserverRegistrations
     */
    public function testgetObserverRegistrations_newDispatcherEvent()
    {
        $this->assertSame(array(), TestDispatcher::testgetObserverRegistrations('dispatcher2', 'event'));
    }

    /**
     * @covers app\decibel\event\DEventDispatcher::getObserverRegistrations
     */
    public function testgetObserverRegistrations_existingDispatcher()
    {
        $observer = array('tests\app\decibel\event\TestObserver', 'doSomething');
        TestDispatcher::subscribeObserver($observer, TestDispatcher::TESTEVENT);
        $registrations = TestDispatcher::testgetObserverRegistrations(
            'tests\app\decibel\event\TestDispatcher'
        );
        $this->assertInternalType('array', $registrations);
        $this->assertArrayHasKey('tests\\app\\decibel\\event\\TestEvent', $registrations);
        $this->assertInternalType('array', $registrations['tests\\app\\decibel\\event\\TestEvent']);
        $this->assertArrayHasKey(0, $registrations['tests\\app\\decibel\\event\\TestEvent']);
        $this->assertInstanceOf('app\\decibel\\event\\DEventSubscription',
                                $registrations['tests\\app\\decibel\\event\\TestEvent'][0]);
        $this->assertSame($observer, $registrations['tests\\app\\decibel\\event\\TestEvent'][0]->getObserver());
        // Clean up
        TestDispatcher::unsubscribeObserver($observer);
    }

    /**
     * @covers app\decibel\event\DEventDispatcher::getObserverRegistrations
     */
    public function testgetObserverRegistrations_existingDispatcherEvent()
    {
        $observer = array('tests\app\decibel\event\TestObserver', 'doSomething');
        TestDispatcher::subscribeObserver($observer, TestDispatcher::TESTEVENT);
        $registrations = TestDispatcher::testgetObserverRegistrations(
            'tests\app\decibel\event\TestDispatcher',
            TestDispatcher::TESTEVENT
        );
        $this->assertArrayHasKey(0, $registrations);
        $this->assertInstanceOf('app\\decibel\\event\\DEventSubscription', $registrations[0]);
        $this->assertSame($observer, $registrations[0]->getObserver());
        // Clean up
        TestDispatcher::unsubscribeObserver($observer);
    }

    /**
     * @covers app\decibel\event\DEventDispatcher::isSubscribed
     */
    public function testisSubscribed()
    {
        $observer = array('tests\app\decibel\event\TestObserver', 'doSomething');
        $this->assertNull(TestDispatcher::testisSubscribed(TestDispatcher::TESTEVENT, $observer));
        TestDispatcher::subscribeObserver($observer, TestDispatcher::TESTEVENT);
        $this->assertNotNull(TestDispatcher::testisSubscribed(TestDispatcher::TESTEVENT, $observer));
        // Clean up
        TestDispatcher::unsubscribeObserver($observer);
    }

    /**
     * @covers app\decibel\event\DEventDispatcher::subscribeObserver
     */
    public function testsubscribeObserver()
    {
        $observer = array('tests\app\decibel\event\TestObserver', 'doSomething');
        TestDispatcher::subscribeObserver($observer, TestDispatcher::TESTEVENT);
        $this->assertNotNull(TestDispatcher::testisSubscribed(TestDispatcher::TESTEVENT, $observer));
        // Clean up
        TestDispatcher::unsubscribeObserver($observer);
    }

    /**
     * @covers app\decibel\event\DEventDispatcher::subscribeObserver
     * @expectedException app\decibel\event\debug\DInvalidEventException
     */
    public function testsubscribeObserver_invalid()
    {
        $observer = array('tests\app\decibel\event\TestObserver', 'doSomething');
        TestDispatcher::subscribeObserver($observer, 'invalid');
    }

    /**
     * @covers app\decibel\event\DEventDispatcher::subscribeObserver
     * @expectedException app\decibel\event\debug\DDuplicateSubscriptionException
     */
    public function testsubscribeObserver_duplicate()
    {
        $observer = array('tests\app\decibel\event\TestObserver', 'doSomething');
        TestDispatcher::subscribeObserver($observer, TestDispatcher::TESTEVENT);
        try {
            TestDispatcher::subscribeObserver($observer, TestDispatcher::TESTEVENT);
        } catch (DDuplicateSubscriptionException $exception) {
            // Clean up
            TestDispatcher::unsubscribeObserver($observer);
            throw $exception;
        }
    }

    /**
     * @covers app\decibel\event\DEventDispatcher::unsubscribeObserver
     */
    public function testunsubscribeObserver()
    {
        $observer = array('tests\app\decibel\event\TestObserver', 'doSomething');
        TestDispatcher::subscribeObserver($observer, TestDispatcher::TESTEVENT);
        $this->assertNotNull(TestDispatcher::testisSubscribed(TestDispatcher::TESTEVENT, $observer));
        $this->assertTrue(TestDispatcher::unsubscribeObserver($observer));
        $this->assertNull(TestDispatcher::testisSubscribed(TestDispatcher::TESTEVENT, $observer));
    }

    /**
     * @covers app\decibel\event\DEventDispatcher::unsubscribeObserver
     */
    public function testunsubscribeObserver_notSubscribed()
    {
        $observer = array('tests\app\decibel\event\TestObserver', 'doSomething');
        $this->assertFalse(TestDispatcher::unsubscribeObserver($observer));
    }

    /**
     * @covers app\decibel\event\DEventDispatcher::unsubscribeObserver
     * @expectedException app\decibel\event\debug\DInvalidObserverException
     */
    public function testunsubscribeObserver_notCallable()
    {
        TestDispatcher::unsubscribeObserver(array(), TestDispatcher::TESTEVENT);
    }

    /**
     * @covers app\decibel\event\DEventDispatcher::unsubscribeObserver
     * @expectedException app\decibel\event\debug\DInvalidEventException
     */
    public function testunsubscribeObserver_invalid()
    {
        $observer = array('tests\app\decibel\event\TestObserver', 'doSomething');
        TestDispatcher::unsubscribeObserver($observer, 'invalid');
    }

    /**
     * @covers app\decibel\event\DEventDispatcher::validateEvent
     */
    public function testvalidateEvent()
    {
        $event = TestDispatcher::TESTEVENT;
        TestDispatcher::testvalidateEvent($event);
        $this->assertSame(TestDispatcher::TESTEVENT, $event);
    }

    /**
     * @covers app\decibel\event\DEventDispatcher::validateEvent
     */
    public function testvalidateEvent_default()
    {
        $event = null;
        TestDispatcher::testvalidateEvent($event);
        $this->assertSame(TestDispatcher::TESTEVENT, $event);
    }

    /**
     * @covers app\decibel\event\DEventDispatcher::validateEvent
     * @expectedException app\decibel\event\debug\DInvalidEventException
     */
    public function testvalidateEvent_invalid()
    {
        $event = 'invalid';
        TestDispatcher::testvalidateEvent($event);
    }
}
