<?php namespace Magician\Tests;

use Magician\Magician;

class FindersTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->magician = new Magician();
        $this->magician->set('Mocked\Eloquent\Model');
    }

    public function testFinders()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
}
