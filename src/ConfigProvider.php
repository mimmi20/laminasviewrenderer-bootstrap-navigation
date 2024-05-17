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

use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\View\Renderer\PhpRenderer;

final class ConfigProvider
{
    /**
     * Return general-purpose laminas-navigation configuration.
     *
     * @return array<string, array<string, array<string, string>>>
     * @phpstan-return array{navigation_helpers: array{aliases: array<string, class-string>, factories: array<class-string, class-string>}, dependencies: array{factories: array<class-string, class-string>}}
     *
     * @throws void
     */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencyConfig(),
            'navigation_helpers' => $this->getNavigationHelperConfig(),
        ];
    }

    /**
     * Return application-level dependency configuration.
     *
     * @return array<string, array<string, string>>
     * @phpstan-return array{factories: array<class-string, class-string>}
     *
     * @throws void
     *
     * @api
     */
    public function getDependencyConfig(): array
    {
        return [
            'factories' => [
                PhpRenderer::class => InvokableFactory::class,
            ],
        ];
    }

    /**
     * @return array<string, array<string, string>>
     * @phpstan-return array{aliases: array<string, class-string>, factories: array<class-string, class-string>}
     *
     * @throws void
     *
     * @api
     */
    public function getNavigationHelperConfig(): array
    {
        return [
            'aliases' => [
                'breadcrumbs' => Breadcrumbs::class,
                'menu' => Menu::class,
            ],
            'factories' => [
                Breadcrumbs::class => BreadcrumbsFactory::class,
                Menu::class => MenuFactory::class,
            ],
        ];
    }
}
