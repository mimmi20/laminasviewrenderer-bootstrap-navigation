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

use Laminas\I18n\Exception\RuntimeException;
use Laminas\Navigation\AbstractContainer;
use Laminas\Navigation\Page\AbstractPage;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\Stdlib\Exception\InvalidArgumentException;
use Laminas\View\Exception;
use Laminas\View\Exception\DomainException;
use Laminas\View\Helper\EscapeHtml;
use Laminas\View\Model\ModelInterface;
use Laminas\View\Renderer\PhpRenderer;
use Mimmi20\NavigationHelper\ContainerParser\ContainerParserInterface;
use Mimmi20\NavigationHelper\Htmlify\HtmlifyInterface;
use Psr\Container\ContainerExceptionInterface;

use function array_key_exists;
use function array_merge;
use function array_reverse;
use function array_unshift;
use function assert;
use function count;
use function get_debug_type;
use function implode;
use function is_array;
use function is_int;
use function is_string;
use function sprintf;

use const PHP_EOL;

/**
 * Helper for printing breadcrumbs.
 */
final class Breadcrumbs extends \Laminas\View\Helper\Navigation\Breadcrumbs
{
    use HelperTrait;

    /** @throws void */
    public function __construct(
        ServiceLocatorInterface $serviceBuilder,
        ContainerParserInterface $containerParser,
        private readonly HtmlifyInterface $htmlify,
        private readonly PhpRenderer $renderer,
        private readonly EscapeHtml $escapeHtml,
    ) {
        $this->serviceBuilder  = $serviceBuilder;
        $this->containerParser = $containerParser;
    }

    /**
     * Returns minimum depth a page must have to be included when rendering
     *
     * @throws void
     */
    public function getMinDepth(): int | null
    {
        if (!is_int($this->minDepth) || 0 > $this->minDepth) {
            return 1;
        }

        return $this->minDepth;
    }

    /**
     * Renders breadcrumbs by chaining 'a' elements with the separator
     * registered in the helper.
     *
     * @param AbstractContainer<AbstractPage>|string|null $container [optional] container to render. Default is
     *                                                 to render the container registered in the helper.
     *
     * @throws InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws Exception\InvalidArgumentException
     * @throws ContainerExceptionInterface
     * @throws RuntimeException
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function renderStraight($container = null): string
    {
        $content = $this->parentRenderStraight($container);

        if ($content === '') {
            return '';
        }

        $indent = $this->getIndent();

        $html  = $indent . '<nav aria-label="breadcrumb">' . PHP_EOL;
        $html .= $indent . '    <ul class="breadcrumb">' . PHP_EOL;
        $html .= $content;
        $html .= $indent . '    </ul>' . PHP_EOL;

        return $html . ($indent . '</nav>' . PHP_EOL);
    }

    /**
     * Returns an HTML string containing an 'a' element for the given page
     *
     * @param AbstractPage $page page to generate HTML for
     *
     * @return string HTML string (<a href="…">Label</a>)
     *
     * @throws Exception\InvalidArgumentException
     * @throws RuntimeException
     */
    public function htmlify(AbstractPage $page): string
    {
        return $this->htmlify->toHtml(self::class, $page);
    }

    /**
     * Render a partial with the given "model".
     *
     * @param array<string, mixed>                          $params
     * @param AbstractContainer<AbstractPage>|string|null   $container
     * @param array<int, string>|ModelInterface|string|null $partial
     *
     * @throws Exception\RuntimeException                             if no partial provided
     * @throws Exception\InvalidArgumentException                     if partial is invalid array
     * @throws InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws ContainerExceptionInterface
     * @throws DomainException
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    protected function renderPartialModel(array $params, $container, $partial): string
    {
        if ($partial === null) {
            $partial = $this->getPartial();
        }

        if ($partial === null || $partial === '' || $partial === []) {
            throw new Exception\RuntimeException(
                'Unable to render breadcrumbs: No partial view script provided',
            );
        }

        if (is_array($partial)) {
            if (count($partial) !== 2) {
                throw new Exception\InvalidArgumentException(
                    'Unable to render breadcrumbs: A view partial supplied as '
                    . 'an array must contain one value: the partial view script',
                );
            }

            $partial = $partial[0];
        }

        $container = $this->containerParser->parseContainer($container);

        if ($container === null) {
            $container = $this->getContainer();
        }

        /** @var array<string, array<mixed>> $model */
        $model  = array_merge($params, ['pages' => [], 'separator' => $this->getSeparator()]);
        $active = $this->findActive($container);

        if ($active !== []) {
            $active = $active['page'];

            assert(
                $active instanceof AbstractPage,
                sprintf(
                    '$active should be an Instance of %s, but was %s',
                    AbstractPage::class,
                    get_debug_type($active),
                ),
            );

            $model['pages'][] = $active;

            while ($parent = $active->getParent()) {
                if (!$parent instanceof AbstractPage) {
                    break;
                }

                $model['pages'][] = $parent;

                if ($parent === $container) {
                    // break if at the root of the given container
                    break;
                }

                $active = $parent;
            }

            $model['pages'] = array_reverse($model['pages']);
        }

        return $this->renderer->render($partial, $model);
    }

    /**
     * Renders breadcrumbs by chaining 'a' elements with the separator
     * registered in the helper.
     *
     * @param AbstractContainer<AbstractPage>|string|null $container [optional] container to render. Default is
     *                                                 to render the container registered in the helper.
     *
     * @throws InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws Exception\InvalidArgumentException
     * @throws ContainerExceptionInterface
     * @throws RuntimeException
     */
    private function parentRenderStraight(AbstractContainer | string | null $container = null): string
    {
        $container = $this->containerParser->parseContainer($container);

        if ($container === null) {
            $container = $this->getContainer();
        }

        $active = $this->findActive($container);

        // find deepest active
        if (!array_key_exists('page', $active) || !$active['page'] instanceof AbstractPage) {
            return '';
        }

        $active = $active['page'];
        $html   = [];

        // put the deepest active page last in breadcrumbs
        if ($this->getLinkLast()) {
            $html[] = $this->renderBreadcrumbItem(
                $this->htmlify->toHtml(self::class, $active),
                $active->getCustomProperties()['liClass'] ?? '',
                $active->isActive(),
            );
        } else {
            $label      = (string) $active->getLabel();
            $translator = $this->getTranslator();

            if ($label !== '' && $translator !== null) {
                $textDomain = $active->getTextDomain() ?? 'default';
                assert(is_string($textDomain));

                $label = $translator->translate($label, $textDomain);
            }

            $label = ($this->escapeHtml)($label);
            assert(is_string($label));

            $html[] = $this->renderBreadcrumbItem(
                $label,
                $active->getCustomProperties()['liClass'] ?? '',
                $active->isActive(),
            );
        }

        // walk back to root
        while ($parent = $active->getParent()) {
            if ($parent instanceof AbstractPage) {
                // prepend crumb to html
                $entry = $this->renderBreadcrumbItem(
                    $this->htmlify->toHtml(self::class, $parent),
                    $parent->getCustomProperties()['liClass'] ?? '',
                    $parent->isActive(),
                );
                array_unshift($html, $entry);
            }

            if ($parent === $container) {
                // at the root of the given container
                break;
            }

            $active = $parent;
        }

        return $this->combineRendered($html);
    }

    /** @throws void */
    private function renderBreadcrumbItem(string $content, string $liClass, bool $active): string
    {
        $classes = ['breadcrumb-item'];
        $aria    = '';

        if ($liClass) {
            $classes[] = $liClass;
        }

        if ($active) {
            $classes[] = 'active';
            $aria      = ' aria-current="page"';
        }

        $indent = $this->getIndent();

        $html  = $indent . '        ' . sprintf(
            '<li class="%s"%s>',
            implode(' ', $classes),
            $aria,
        ) . PHP_EOL;
        $html .= $indent . '            ' . $content . PHP_EOL;

        return $html . ($indent . '        </li>' . PHP_EOL);
    }

    /** @throws void */
    private function renderSeparator(): string
    {
        return $this->getIndent() . '        ' . $this->getSeparator() . PHP_EOL;
    }

    /**
     * @param array<string> $html
     *
     * @throws void
     */
    private function combineRendered(array $html): string
    {
        return $html !== [] ? implode($this->renderSeparator(), $html) : '';
    }
}
