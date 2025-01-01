<?php

/**
 * This file is part of the mimmi20/laminasviewrenderer-bootstrap-navigation package.
 *
 * Copyright (c) 2021-2025, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mimmi20Test\LaminasView\BootstrapNavigation;

use Laminas\View\Renderer\PhpRenderer;
use Mimmi20\LaminasView\BootstrapNavigation\Breadcrumbs;
use Mimmi20\LaminasView\BootstrapNavigation\ConfigProvider;
use Mimmi20\LaminasView\BootstrapNavigation\Menu;
use Override;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;

final class ConfigProviderTest extends TestCase
{
    private ConfigProvider $provider;

    /** @throws void */
    #[Override]
    protected function setUp(): void
    {
        $this->provider = new ConfigProvider();
    }

    /** @throws Exception */
    public function testProviderDefinesExpectedFactoryServices(): void
    {
        $navigationHelperConfig = $this->provider->getNavigationHelperConfig();

        self::assertArrayHasKey('factories', $navigationHelperConfig);
        $factories = $navigationHelperConfig['factories'];
        self::assertIsArray($factories);
        self::assertArrayHasKey(Breadcrumbs::class, $factories);
        self::assertArrayHasKey(Menu::class, $factories);

        self::assertArrayHasKey('aliases', $navigationHelperConfig);
        $aliases = $navigationHelperConfig['aliases'];
        self::assertIsArray($aliases);
    }

    /** @throws Exception */
    public function testProviderDefinesExpectedFactoryServices2(): void
    {
        $dependencyConfig = $this->provider->getDependencyConfig();

        self::assertArrayHasKey('factories', $dependencyConfig);
        $factories = $dependencyConfig['factories'];
        self::assertIsArray($factories);
        self::assertArrayHasKey(PhpRenderer::class, $factories);

        self::assertArrayNotHasKey('aliases', $dependencyConfig);
    }

    /** @throws Exception */
    public function testInvocationReturnsArrayWithDependencies(): void
    {
        $config = ($this->provider)();

        self::assertArrayHasKey('navigation_helpers', $config);

        $navigationHelperConfig = $config['navigation_helpers'];
        self::assertIsArray($navigationHelperConfig);

        self::assertArrayHasKey('factories', $navigationHelperConfig);
        $factories = $navigationHelperConfig['factories'];
        self::assertIsArray($factories);
        self::assertArrayHasKey(Breadcrumbs::class, $factories);
        self::assertArrayHasKey(Menu::class, $factories);

        self::assertArrayHasKey('aliases', $navigationHelperConfig);
        $aliases = $navigationHelperConfig['aliases'];
        self::assertIsArray($aliases);

        self::assertArrayHasKey('dependencies', $config);

        $dependencyConfig = $config['dependencies'];
        self::assertIsArray($dependencyConfig);

        self::assertArrayHasKey('factories', $dependencyConfig);
        $factories = $dependencyConfig['factories'];
        self::assertIsArray($factories);
        self::assertArrayHasKey(PhpRenderer::class, $factories);

        self::assertArrayNotHasKey('aliases', $dependencyConfig);
    }
}
