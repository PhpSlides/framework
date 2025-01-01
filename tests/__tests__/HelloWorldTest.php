<?php
use PHPUnit\Framework\TestCase;

class HelloWorldTest extends TestCase
{
    public function testHelloWorld ()
    {
        $this->assertEquals('Hello, World!', 'Hello, World!');
    }

    public function testAddition ()
    {
        $this->assertEquals(2, 1 + 1);
    }
}