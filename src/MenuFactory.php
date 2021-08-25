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

        $plugin     = $container->get(ViewPluginManager::class);
        $translator = null;

        return new Menu(
            $container,
            $container->get(Logger::class),
            $container->get(ContainerParserInterface::class),
            $plugin->get(EscapeHtmlAttr::class),
            $container->get(PhpRenderer::class),
            $plugin->get(EscapeHtml::class),
            $container->get(HtmlElementInterface::class),
            $translator
        );
    }
}
