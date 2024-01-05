<?php
/**
 * This file is part of the mimmi20/laminasviewrenderer-bootstrap-navigation package.
 *
 * Copyright (c) 2021-2024, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mimmi20Test\LaminasView\BootstrapNavigation;

use AssertionError;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\Helper\EscapeHtml;
use Laminas\View\Helper\EscapeHtmlAttr;
use Laminas\View\Helper\HelperInterface;
use Laminas\View\HelperPluginManager as ViewHelperPluginManager;
use Laminas\View\Renderer\PhpRenderer;
use Mimmi20\LaminasView\BootstrapNavigation\Menu;
use Mimmi20\LaminasView\BootstrapNavigation\MenuFactory;
use Mimmi20\LaminasView\Helper\HtmlElement\Helper\HtmlElementInterface;
use Mimmi20\NavigationHelper\ContainerParser\ContainerParserInterface;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

use function get_debug_type;
use function sprintf;

final class MenuFactoryTest extends TestCase
{
    private MenuFactory $factory;

    /** @throws void */
    protected function setUp(): void
    {
        $this->factory = new MenuFactory();
    }

    /**
     * @throws Exception
     * @throws ContainerExceptionInterface
     */
    public function testInvocation(): void
    {
        $containerParser = $this->createMock(ContainerParserInterface::class);
        $htmlElement     = $this->createMock(HtmlElementInterface::class);
        $escapeHtmlAttr  = $this->createMock(EscapeHtmlAttr::class);
        $escapeHtml      = $this->createMock(EscapeHtml::class);
        $renderer        = $this->createMock(PhpRenderer::class);

        $viewHelperPluginManager = $this->getMockBuilder(ViewHelperPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $matcher                 = self::exactly(2);
        $viewHelperPluginManager->expects($matcher)
            ->method('get')
            ->willReturnCallback(
                static function ($name, array | null $options = null) use ($matcher, $escapeHtmlAttr, $escapeHtml): HelperInterface {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame(EscapeHtmlAttr::class, $name),
                        default => self::assertSame(EscapeHtml::class, $name),
                    };

                    self::assertNull($options);

                    return match ($matcher->numberOfInvocations()) {
                        1 => $escapeHtmlAttr,
                        default => $escapeHtml,
                    };
                },
            );
        $viewHelperPluginManager->expects(self::never())
            ->method('has');

        $container = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $matcher   = self::exactly(4);
        $container->expects($matcher)
            ->method('get')
            ->willReturnCallback(
                static function ($name, array | null $options = null) use ($matcher, $viewHelperPluginManager, $htmlElement, $containerParser, $renderer): mixed {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame(ViewHelperPluginManager::class, $name),
                        2 => self::assertSame(ContainerParserInterface::class, $name),
                        4 => self::assertSame(HtmlElementInterface::class, $name),
                        default => self::assertSame(PhpRenderer::class, $name),
                    };

                    self::assertNull($options);

                    return match ($matcher->numberOfInvocations()) {
                        1 => $viewHelperPluginManager,
                        2 => $containerParser,
                        4 => $htmlElement,
                        default => $renderer,
                    };
                },
            );
        $container->expects(self::never())
            ->method('has');

        $helper = ($this->factory)($container);

        self::assertInstanceOf(Menu::class, $helper);
    }

    /**
     * @throws Exception
     * @throws ContainerExceptionInterface
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
        $this->expectExceptionMessage(
            sprintf(
                '$container should be an Instance of %s, but was %s',
                ServiceLocatorInterface::class,
                get_debug_type($container),
            ),
        );

        ($this->factory)($container);
    }
}
