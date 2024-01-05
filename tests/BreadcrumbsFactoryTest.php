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
use Laminas\View\HelperPluginManager as ViewHelperPluginManager;
use Laminas\View\Renderer\PhpRenderer;
use Mimmi20\LaminasView\BootstrapNavigation\Breadcrumbs;
use Mimmi20\LaminasView\BootstrapNavigation\BreadcrumbsFactory;
use Mimmi20\NavigationHelper\ContainerParser\ContainerParserInterface;
use Mimmi20\NavigationHelper\Htmlify\HtmlifyInterface;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

use function get_debug_type;
use function sprintf;

final class BreadcrumbsFactoryTest extends TestCase
{
    private BreadcrumbsFactory $factory;

    /** @throws void */
    protected function setUp(): void
    {
        $this->factory = new BreadcrumbsFactory();
    }

    /**
     * @throws Exception
     * @throws ContainerExceptionInterface
     */
    public function testInvocation(): void
    {
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
        $matcher   = self::exactly(4);
        $container->expects($matcher)
            ->method('get')
            ->willReturnCallback(
                static function ($name, array | null $options = null) use ($matcher, $viewHelperPluginManager, $htmlify, $containerParser, $renderer): mixed {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame(ViewHelperPluginManager::class, $name),
                        2 => self::assertSame(HtmlifyInterface::class, $name),
                        3 => self::assertSame(ContainerParserInterface::class, $name),
                        default => self::assertSame(PhpRenderer::class, $name),
                    };

                    self::assertNull($options);

                    return match ($matcher->numberOfInvocations()) {
                        1 => $viewHelperPluginManager,
                        2 => $htmlify,
                        3 => $containerParser,
                        default => $renderer,
                    };
                },
            );
        $container->expects(self::never())
            ->method('has');

        $helper = ($this->factory)($container);

        self::assertInstanceOf(Breadcrumbs::class, $helper);
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
