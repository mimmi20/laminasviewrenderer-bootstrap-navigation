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

/** @see       https://github.com/laminas/laminas-view for the canonical source repository */

namespace Mimmi20Test\LaminasView\BootstrapNavigation\Compare\TestAsset;

use Laminas\I18n\Translator;
use Laminas\I18n\Translator\TextDomain;
use Override;

/** @phpcs:disable SlevomatCodingStandard.Classes.ForbiddenPublicProperty.ForbiddenPublicProperty */
final readonly class ArrayTranslator implements Translator\Loader\FileLoaderInterface
{
    /**
     * @param array<string, string> $translations
     *
     * @throws void
     */
    public function __construct(private array $translations)
    {
        // nothing to do
    }

    /**
     * Load translations from a file.
     *
     * @param string $locale
     * @param string $filename
     *
     * @return TextDomain<(int|string), mixed>
     *
     * @throws void
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @api
     */
    #[Override]
    public function load($filename, $locale): TextDomain
    {
        return new TextDomain($this->translations);
    }
}
