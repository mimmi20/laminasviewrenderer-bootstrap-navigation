<?php
/**
 * This file is part of the mimmi20/laminasviewrenderer-bootstrap-navigation package.
 *
 * Copyright (c) 2021, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mimmi20Test\LaminasView\BootstrapNavigation;

use AssertionError;
use Interop\Container\ContainerInterface;
use Laminas\Log\Logger;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\Helper\EscapeHtml;
use Laminas\View\HelperPluginManager as ViewHelperPluginManager;
use Laminas\View\Renderer\PhpRenderer;
use Mimmi20\LaminasView\BootstrapNavigation\Breadcrumbs;
use Mimmi20\LaminasView\BootstrapNavigation\BreadcrumbsFactory;
use Mimmi20\NavigationHelper\ContainerParser\ContainerParserInterface;
use Mimmi20\NavigationHelper\Htmlify\HtmlifyInterface;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

final class BreadcrumbsFactoryTest extends TestCase
{
    private BreadcrumbsFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new BreadcrumbsFactory();
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testInvocation(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emerg');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('crit');
        $logger->expects(self::never())
            ->method('err');
        $logger->expects(self::never())
            ->method('warn');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $htmlify         = $this->createMock(HtmlifyInterface::class);
        $containerParser = $this->createMock(ContainerParserInterface::class);
        $renderer        = $this->createMock(PhpRenderer::class);
        $escapeHtml      = $this->createMock(EscapeHtml::class);

        $viewHelperPluginManager = $this->getMockBuilder(ViewHelperPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $viewHelperPluginManager->expects(self::never())
            ->method('has');
        $viewHelperPluginManager->expects(self::once())
            ->method('get')
            ->with(EscapeHtml::class)
            ->willReturn($escapeHtml);

        $container = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::exactly(5))
            ->method('get')
            ->withConsecutive([ViewHelperPluginManager::class], [Logger::class], [HtmlifyInterface::class], [ContainerParserInterface::class], [PhpRenderer::class])
            ->willReturnOnConsecutiveCalls($viewHelperPluginManager, $logger, $htmlify, $containerParser, $renderer);
        $container->expects(self::never())
            ->method('has');

        $helper = ($this->factory)($container);

        self::assertInstanceOf(Breadcrumbs::class, $helper);
    }

    /**
     * @throws Exception
     */
    public function testInvocationWithAssertionError(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('get');

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage('assert($container instanceof ServiceLocatorInterface)');

        ($this->factory)($container);
    }
}
