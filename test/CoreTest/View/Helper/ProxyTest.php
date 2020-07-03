<?php
/**
 * YAWIK
 *
 * @filesource
 * @license MIT
 * @copyright https://yawik.org/COPYRIGHT.php
 */

/** */
namespace CoreTest\View\Helper;

use PHPUnit\Framework\TestCase;

use Core\View\Helper\Proxy;
use Core\View\Helper\Proxy\NoopHelper;
use Core\View\Helper\Proxy\NoopIterator;
use CoreTestUtils\TestCase\ServiceManagerMockTrait;
use CoreTestUtils\TestCase\TestInheritanceTrait;
use Laminas\View\Helper\AbstractHelper;
use Laminas\View\Renderer\ConsoleRenderer;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Renderer\RendererInterface;

/**
 * Tests for \Core\View\Helper\Proxy
 *
 * @covers \Core\View\Helper\Proxy
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * @group Core
 * @group Core.View
 * @group Core.View.Helper
 */
class ProxyTest extends TestCase
{
    use TestInheritanceTrait, ServiceManagerMockTrait;

    /**
     *
     *
     * @var array|\PHPUnit_Framework_MockObject_MockObject|Proxy
     */
    private $target = [
        Proxy::class,
        '@testInvokationInvokesInvokableHelpers' => '#mockPlugin',
        '@testInvokationReturnsHelperProxyIfOnlyOneArgument' => '#mockPlugin',
        '@testInvokationCallsHelperProxyMethod' => '#mockPlugin',
        '@testExistsProxiesToPlugin' => ['mock' => ['plugin' => ['with' => ['helper', true]]]],
        '#mockPlugin' => [
            'mock' => ['plugin'],
        ],
    ];

    private $inheritance = [ AbstractHelper::class ];

    /**
     *
     *
     * @var \CoreTestUtils\Mock\ServiceManager\PluginManagerMock
     */
    private $helperManager;

    private function injectHelperManager($services = [])
    {
        $sm = $this->createServiceManagerMock();
        $manager = $this->createPluginManagerMock($services, $sm);

        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getHelperPluginManager'])
            ->getMock()
        ;

        $renderer
            ->expects($this->any())
            ->method('getHelperPluginManager')
            ->will($this->returnValue($manager))
        ;

        $this->target->setView($renderer);
        $this->helperManager = $manager;
    }

    public function testInvokationReturnsProxyHelperWhenNoArguments()
    {
        $this->assertSame($this->target, $this->target->__invoke());
    }

    public function testInvokationReturnsHelperProxyIfOnlyOneArgument()
    {
        $expect = new Proxy\HelperProxy(false);

        $this->target->expects($this->once())->method('plugin')->willReturn($expect);

        $actual = $this->target->__invoke('helper');

        $this->assertSame($expect, $actual);
    }

    public function testInvokationCallsHelperProxyMethod()
    {
        $helper = $this->getMockBuilder(Proxy\HelperProxy::class)->disableOriginalConstructor()
            ->setMethods(['call'])->getMock();
        $helper->expects($this->once())->method('call')->with('__invoke', ['invokeArg'], Proxy\HelperProxy::EXPECT_SELF)
            ->will($this->returnSelf());

        $this->target->expects($this->once())->method('plugin')->willReturn($helper);

        $this->target->__invoke('helper', ['invokeArg']);
    }

    public function testPluginReturnsFalseIfRendererDoesNotHaveHelperManager()
    {
        $renderer = new ConsoleRenderer();
        $this->target->setView($renderer);

        $plugin = $this->target->plugin('helper');
        $this->assertInstanceOf(Proxy\HelperProxy::class, $this->target->plugin('helper'));
        $this->assertAttributeEquals(false, 'helper', $plugin);
    }

    public function testPluginReturnIfPluginDoesNotExist()
    {
        $this->injectHelperManager();

        $plugin = $this->target->plugin('helper');
        $this->assertInstanceOf(Proxy\HelperProxy::class, $this->target->plugin('helper'));
        $this->assertAttributeEquals(false, 'helper', $plugin);
    }

    public function testPluginReturnsBool()
    {
        $this->injectHelperManager();

        $this->assertFalse($this->target->plugin('helper', true));

        $this->helperManager->setService('helper', new PtHelperDummy());

        $this->assertTrue($this->target->plugin('helper', true));
    }

    public function testPluginFetchesHelperFromManager()
    {
        $helper = new PtHelperDummy();
        $this->injectHelperManager(['helper' => $helper]);

        $actual = $this->target->plugin('helper');
        $this->assertInstanceOf(Proxy\HelperProxy::class, $actual);
        $this->assertSame($helper,$actual->helper());
    }

    public function testPluginPassesOptionsToHelperManager()
    {
        //$helper = new PtHelperDummy();
        $options = ['option' => 'value'];
        $this->injectHelperManager(['helper' => PtHelperDummy::class]);
        $this->helperManager
            ->setExpectedCallCount('get', 'helper', $options, 1)
        ;

        $this->target->plugin('helper', $options);

        $count = $this->helperManager->getCallCount('get','helper',$options);
        $this->assertEquals(1, $count);
        $this->assertTrue($this->target->exists('helper'));
    }

    public function testExistsProxiesToPlugin()
    {
        $this->assertFalse($this->target->exists('helper'));
    }
}

class PtHelperDummy
{
}
