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

namespace Mimmi20\LaminasView\BootstrapNavigation;

use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\Helper\EscapeHtml;
use Laminas\View\HelperPluginManager as ViewPluginManager;
use Laminas\View\Renderer\PhpRenderer;
use Mimmi20\NavigationHelper\ContainerParser\ContainerParserInterface;
use Mimmi20\NavigationHelper\Htmlify\HtmlifyInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

use function assert;
use function get_debug_type;
use function sprintf;

final class BreadcrumbsFactory
{
    /**
     * Create and return a navigation view helper instance.
     *
     * @throws ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): Breadcrumbs
    {
        assert(
            $container instanceof ServiceLocatorInterface,
            sprintf(
                '$container should be an Instance of %s, but was %s',
                ServiceLocatorInterface::class,
                get_debug_type($container),
            ),
        );

        $plugin = $container->get(ViewPluginManager::class);
        assert(
            $plugin instanceof ViewPluginManager,
            sprintf(
                '$plugin should be an Instance of %s, but was %s',
                ViewPluginManager::class,
                get_debug_type($plugin),
            ),
        );

        $htmlify         = $container->get(HtmlifyInterface::class);
        $containerParser = $container->get(ContainerParserInterface::class);
        $renderer        = $container->get(PhpRenderer::class);
        $escapeHtml      = $plugin->get(EscapeHtml::class);

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        assert($renderer instanceof PhpRenderer);
        assert($escapeHtml instanceof EscapeHtml);

        return new Breadcrumbs($container, $containerParser, $htmlify, $renderer, $escapeHtml);
    }
}
