<?php

namespace greevex\react\tests\easyServer;

/**
 * Class TestCase
 *
 * thanks, copied from https://github.com/reactphp/socket/blob/master/tests/TestCase.php
 *
 * @package greevex\react\tests\easyServer
 */
class testCase extends \PHPUnit_Framework_TestCase
{
    protected function expectCallableExactly($amount)
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects(static::exactly($amount))
            ->method('__invoke');
        return $mock;
    }
    protected function expectCallableOnce()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects(static::once())
            ->method('__invoke');
        return $mock;
    }
    protected function expectCallableNever()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects(static::never())
            ->method('__invoke');
        return $mock;
    }
    protected function createCallableMock()
    {
        return $this->getMock('greevex\react\tests\easyServer\callableStub');
    }
}