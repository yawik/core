<?php
/**
 * YAWIK
 *
 * @filesource
 * @license MIT
 * @copyright  2013 - 2016 Cross Solution <http://cross-solution.de>
 */
  
/** */
namespace CoreTest\Controller\Plugin\PaginationBuilder;

use CoreTestUtils\TestCase\TestInheritanceTrait;

/**
 * Tests for \Core\Controller\Plugin\PaginationBuilder
 * 
 * @covers \Core\Controller\Plugin\PaginationBuilder
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * @group Core
 * @group Core.Controller
 * @group Core.Controller.Plugin
 * @group Core.Controller.Plugin.PaginationBuilder
 */
class BaseTest extends \PHPUnit_Framework_TestCase
{
    use TestInheritanceTrait;

    /**
     *
     *
     * @var \Core\Controller\Plugin\PaginationBuilder
     */
    protected $target = [
        'class' => '\Core\Controller\Plugin\PaginationBuilder',
        '@testInvokationCallsGetResult' => [
            'mock' => ['getResult' => ['count' => 2, 'return' => '__self__']],
        ],
    ];

    protected $inheritance = [ '\Zend\Mvc\Controller\Plugin\AbstractPlugin' ];

    public function testInvokationWithoutArgumentsReturnsSelf()
    {
        $this->assertSame($this->target, $this->target->__invoke());
    }

    public function testInvokationWithBooleanTrueResetsStack()
    {
        $this->target->__invoke(['test' => 'Dummydata'], false);
        $this->assertSame($this->target, $this->target->__invoke(true), 'resetting stack does not return self!');
        $this->assertAttributeEmpty('stack', $this->target);
    }

    public function invalidArgumentProvider()
    {
        return [
            'string' => ['string is invalid'],
            'bool' => [false],
            'int' => [1234],
            'float' => [12.34],
        ];
    }

    /**
     * @dataProvider invalidArgumentProvider
     *
     * @param mixed $argument
     */
    public function testInvokationWithInvalidArgumentThrowsException($argument)
    {
        $this->setExpectedException('\InvalidArgumentException', 'Expected argument to be of type array');
        $this->target->__invoke($argument);
    }

    public function testInvokationCallsGetResult()
    {
        $this->assertSame($this->target, $this->target->__invoke(['Test' => 'Dummy data']));
        $this->target->__invoke(['Test2'], true);
    }

    public function argumentsStackProvider()
    {
        return [
            [ 'paginator', ['paginator'], ['as' => 'paginator', 'paginator', [], false] ],
            [ 'paginator', ['name', 'alias'], ['as' => 'alias', 'name', [], false] ],
            [ 'paginator', ['name', ['param' => 'value'], 'alias'], ['as' => 'alias', 'name', ['param' => 'value'], false] ],
            [ 'paginator', ['name', [], true], ['as' => 'paginator', 'name', [], true]],
            [ 'form', ['elements'], ['as' => 'searchform', 'elements', null]],
            [ 'form', ['elements', 'buttons'], ['as' => 'searchform', 'elements', 'buttons']],
            [ 'form', ['elements', null, 'alias'], ['as' => 'alias', 'elements', null]],
            [ 'form', ['elements', '@alias'], ['as' => 'alias', 'elements', null]],
            [ 'params', ['namespace'], ['namespace', ['page' => 1]]],
            [ 'params', ['namespace', ['param' => 'value']], ['namespace', ['param' => 'value']]],
        ];
    }

    /**
     * @dataProvider argumentsStackProvider
     *
     * @param $method
     * @param $args
     * @param $expect
     */
    public function testSetPluginArgumentsStack($method, $args, $expect)
    {
        $this->assertSame($this->target, call_user_func_array([$this->target, $method], $args), 'Fluent interface broken!');
        $this->assertAttributeEquals([$method => $expect], 'stack', $this->target);
    }
}