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

/** @see       https://github.com/laminas/laminas-view for the canonical source repository */

namespace Mimmi20Test\LaminasView\BootstrapNavigation\Compare\TestAsset;

use Laminas\I18n\Translator;
use Laminas\I18n\Translator\TextDomain;

/** @phpcs:disable SlevomatCodingStandard.Classes.ForbiddenPublicProperty.ForbiddenPublicProperty */
final class ArrayTranslator implements Translator\Loader\FileLoaderInterface
{
    /** @var array<string, string> */
    public array $translations = [];

    /**
     * Load translations from a file.
     *
     * @param string $locale
     * @param string $filename
     *
     * @return TextDomain<mixed, mixed>
     *
     * @throws void
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    public function load($filename, $locale): TextDomain
    {
        return new TextDomain($this->translations);
    }
}
