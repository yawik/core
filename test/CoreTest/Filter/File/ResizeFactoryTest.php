<?php

/**
 * YAWIK
 *
 * @filesource
 * @license MIT
 * @copyright https://yawik.org/COPYRIGHT.php
 */

namespace CoreTest\Filter\File;

use PHPUnit\Framework\TestCase;

use Core\Filter\File\ResizeFactory;
use Core\Filter\File\Resize;
use Imagine\Image\ImagineInterface;
use Interop\Container\ContainerInterface;

/**
 * Class ResizeFactoryTest
 *
 * @package CoreTest\Filter\File
 * @author Anthonius Munthi <me@itstoni.com>
 */
class ResizeFactoryTest extends TestCase
{
    public function testInvokation()
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->getMock()
        ;
        $imagine = $this->getMockBuilder(ImagineInterface::class)
            ->getMock()
        ;

        $container->expects($this->once())
            ->method('get')
            ->with('Imagine')
            ->willReturn($imagine)
        ;

        $factory = new ResizeFactory();
        $service = $factory($container, 'some_name');

        $this->assertInstanceOf(Resize::class, $service);
        $this->assertSame($imagine, $service->getImagine());
    }
}
