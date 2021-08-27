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

use Mimmi20\LaminasView\BootstrapNavigation\Breadcrumbs;
use Mimmi20\LaminasView\BootstrapNavigation\Menu;
use Mimmi20\LaminasView\BootstrapNavigation\Module;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

final class ModuleTest extends TestCase
{
    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testGetConfig(): void
    {
        $module = new Module();

        $config = $module->getConfig();

        self::assertIsArray($config);
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
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testGetModuleDependencies(): void
    {
        $module = new Module();

        $config = $module->getModuleDependencies();

        self::assertIsArray($config);
        self::assertCount(8, $config);
        self::assertArrayHasKey(0, $config);
        self::assertContains('Laminas\I18n', $config);
        self::assertContains('Laminas\Router', $config);
        self::assertContains('Laminas\Navigation', $config);
        self::assertContains('Mimmi20\NavigationHelper\Accept', $config);
        self::assertContains('Mimmi20\NavigationHelper\ContainerParser', $config);
        self::assertContains('Mimmi20\NavigationHelper\FindActive', $config);
        self::assertContains('Mimmi20\NavigationHelper\FindRoot', $config);
        self::assertContains('Mimmi20\NavigationHelper\Htmlify', $config);
    }
}
