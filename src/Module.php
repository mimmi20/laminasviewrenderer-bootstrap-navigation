<?php
/**
 * This file is part of the mimmi20/laminasviewrenderer-bootstrap-navigation package.
 *
 * Copyright (c) 2021-2023, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mimmi20\LaminasView\BootstrapNavigation;

use Laminas\ModuleManager\Feature\ConfigProviderInterface;
use Laminas\ModuleManager\Feature\DependencyIndicatorInterface;

final class Module implements ConfigProviderInterface, DependencyIndicatorInterface
{
    /**
     * Return default configuration for laminas-mvc applications.
     *
     * @return array<string, array<string, array<int|string, string>>>
     * @phpstan-return array{navigation_helpers: array{aliases: array<string, class-string>, factories: array<class-string, class-string>}}
     *
     * @throws void
     */
    public function getConfig(): array
    {
        $provider = new ConfigProvider();

        return [
            'navigation_helpers' => $provider->getNavigationHelperConfig(),
        ];
    }

    /**
     * Expected to return an array of modules on which the current one depends on
     *
     * @return array<int, string>
     *
     * @throws void
     */
    public function getModuleDependencies(): array
    {
        return [
            'Laminas\I18n',
            'Laminas\Router',
            'Laminas\Navigation',
            'Mimmi20\NavigationHelper\Accept',
            'Mimmi20\NavigationHelper\ContainerParser',
            'Mimmi20\NavigationHelper\FindActive',
            'Mimmi20\NavigationHelper\FindRoot',
            'Mimmi20\NavigationHelper\Htmlify',
        ];
    }
}
