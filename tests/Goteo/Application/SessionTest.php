<?php


namespace Goteo\Application\Tests;

use Goteo\Application\Session;

class SessionTest extends \PHPUnit_Framework_TestCase {

    public function testInstance() {

        $ob = new Session();

        $this->assertInstanceOf('\Goteo\Application\Session', $ob);

        return $ob;
    }

    public function testStore() {
        Session::start('test', 3600);
        $this->assertEquals(Session::store('test-key', 'test-value'), 'test-value');
        $this->assertTrue(Session::exists('test-key'));
    }

    public function testRetrieve() {
        $this->assertEquals(Session::get('test-key'), 'test-value');
    }

    public function testDelete() {
        $this->assertTrue(Session::del('test-key'));
        $this->assertFalse(Session::exists('test-key'));
    }

    public function testDestroy() {
        Session::start('test', 3600);
        $this->assertEquals(Session::store('test-key-2', 'test-value-2'), 'test-value-2');
        Session::destroy();
        $this->assertFalse(Session::exists('test-key-2'));
        $this->assertNull(Session::get('test-key-2'));
    }

    public function testExpire() {
        Session::start('test');
        $this->assertEquals(Session::store('test-key-3', 'test-value-3'), 'test-value-3');
        sleep(2);
        Session::start('test', 1);
        $this->assertFalse(Session::exists('test-key-3'));
        $this->assertNull(Session::get('test-key-3'));
    }
}
