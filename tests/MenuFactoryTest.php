<?php
/**
 * This file is part of the mimmi20/mezzio-navigation-laminasviewrenderer-bootstrap package.
 *
 * Copyright (c) 2021, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mimmi20Test\LaminasView\BootstrapNavigation;

use Interop\Container\ContainerInterface;
use Laminas\I18n\View\Helper\Translate;
use Laminas\Log\Logger;
use Laminas\ServiceManager\PluginManagerInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\Helper\EscapeHtml;
use Laminas\View\Helper\EscapeHtmlAttr;
use Laminas\View\HelperPluginManager as ViewHelperPluginManager;
use Laminas\View\Renderer\PhpRenderer;
use Mimmi20\LaminasView\BootstrapNavigation\Menu;
use Mimmi20\LaminasView\BootstrapNavigation\MenuFactory;
use Mimmi20\LaminasView\Helper\HtmlElement\Helper\HtmlElementInterface;
use Mimmi20\NavigationHelper\ContainerParser\ContainerParserInterface;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

use function assert;

final class MenuFactoryTest extends TestCase
{
    private MenuFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new MenuFactory();
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

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $htmlElement     = $this->createMock(HtmlElementInterface::class);
        $escapeHtmlAttr  = $this->createMock(EscapeHtmlAttr::class);
        $escapeHtml      = $this->createMock(EscapeHtml::class);
        $renderer        = $this->createMock(PhpRenderer::class);

        $viewHelperPluginManager = $this->getMockBuilder(ViewHelperPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $viewHelperPluginManager->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive([EscapeHtmlAttr::class], [EscapeHtml::class])
            ->willReturnOnConsecutiveCalls($escapeHtmlAttr, $escapeHtml);
        $viewHelperPluginManager->expects(self::once())
            ->method('has')
            ->with(Translate::class)
            ->willReturn(false);

        $container = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::exactly(5))
            ->method('get')
            ->withConsecutive([ViewHelperPluginManager::class], [Logger::class], [ContainerParserInterface::class], [PhpRenderer::class], [HtmlElementInterface::class])
            ->willReturnOnConsecutiveCalls($viewHelperPluginManager, $logger, $containerParser, $renderer, $htmlElement);

        $helper = ($this->factory)($container);

        self::assertInstanceOf(Menu::class, $helper);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testInvocationWithTranslator(): void
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

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $htmlElement     = $this->createMock(HtmlElementInterface::class);
        $escapeHtmlAttr  = $this->createMock(EscapeHtmlAttr::class);
        $escapeHtml      = $this->createMock(EscapeHtml::class);
        $renderer        = $this->createMock(PhpRenderer::class);
        $translator      = $this->createMock(Translate::class);

        $viewHelperPluginManager = $this->getMockBuilder(ViewHelperPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $viewHelperPluginManager->expects(self::exactly(3))
            ->method('get')
            ->withConsecutive([Translate::class], [EscapeHtmlAttr::class], [EscapeHtml::class])
            ->willReturnOnConsecutiveCalls($translator, $escapeHtmlAttr, $escapeHtml);
        $viewHelperPluginManager->expects(self::once())
            ->method('has')
            ->with(Translate::class)
            ->willReturn(true);

        $container = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::exactly(5))
            ->method('get')
            ->withConsecutive([ViewHelperPluginManager::class], [Logger::class], [ContainerParserInterface::class], [PhpRenderer::class], [HtmlElementInterface::class])
            ->willReturnOnConsecutiveCalls($viewHelperPluginManager, $logger, $containerParser, $renderer, $htmlElement);

        $helper = ($this->factory)($container);

        self::assertInstanceOf(Menu::class, $helper);
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

        $this->expectException(\AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage('assert($container instanceof ServiceLocatorInterface)');

        ($this->factory)($container);
    }
}
