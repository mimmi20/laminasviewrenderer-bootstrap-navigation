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

use Laminas\View\Helper\HelperInterface;
use Laminas\View\HelperPluginManager;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

use function assert;
use function is_array;

final class HelperPluginManagerFactory
{
    /**
     * Create an instance of the requested class name.
     *
     * @param string            $requestedName
     * @param array<mixed>|null $options
     *
     * @return HelperPluginManager<(callable(): mixed)|HelperInterface>
     *
     * @throws ContainerExceptionInterface
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        array | null $options = null,
    ): HelperPluginManager {
        $config = $container->get('config');
        assert(is_array($config));

        return new HelperPluginManager($container, $config['view_helpers'] ?? []);
    }
}
