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

namespace Mimmi20\LaminasView\BootstrapNavigation;

use Interop\Container\ContainerInterface;
use Laminas\Log\Logger;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\Helper\EscapeHtml;
use Laminas\View\Helper\EscapeHtmlAttr;
use Laminas\View\HelperPluginManager as ViewPluginManager;
use Laminas\View\Renderer\PhpRenderer;
use Mimmi20\LaminasView\Helper\HtmlElement\Helper\HtmlElementInterface;
use Mimmi20\NavigationHelper\ContainerParser\ContainerParserInterface;
use Psr\Container\ContainerExceptionInterface;

use function assert;

final class MenuFactory
{
    /**
     * Create and return a navigation view helper instance.
     *
     * @throws ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): Menu
    {
        assert($container instanceof ServiceLocatorInterface);

        $plugin = $container->get(ViewPluginManager::class);
        assert($plugin instanceof ViewPluginManager);

        $logger          = $container->get(Logger::class);
        $containerParser = $container->get(ContainerParserInterface::class);
        $escapeHtmlAttr  = $plugin->get(EscapeHtmlAttr::class);
        $renderer        = $container->get(PhpRenderer::class);
        $escapeHtml      = $plugin->get(EscapeHtml::class);
        $htmlElement     = $container->get(HtmlElementInterface::class);

        assert($logger instanceof Logger);
        assert($containerParser instanceof ContainerParserInterface);
        assert($renderer instanceof PhpRenderer);
        assert($escapeHtml instanceof EscapeHtml);
        assert($escapeHtmlAttr instanceof EscapeHtmlAttr);
        assert($htmlElement instanceof HtmlElementInterface);

        return new Menu(
            $container,
            $logger,
            $containerParser,
            $escapeHtmlAttr,
            $renderer,
            $escapeHtml,
            $htmlElement
        );
    }
}
