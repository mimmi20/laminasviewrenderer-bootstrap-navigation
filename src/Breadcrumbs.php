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

use Laminas\I18n\View\Helper\Translate;
use Laminas\Log\Logger;
use Laminas\Navigation\AbstractContainer;
use Laminas\Navigation\Navigation;
use Laminas\Navigation\Page\AbstractPage;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\Stdlib\Exception\InvalidArgumentException;
use Laminas\View\Exception;
use Laminas\View\Helper\EscapeHtml;
use Laminas\View\Model\ModelInterface;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Renderer\RendererInterface;
use Mimmi20\NavigationHelper\ContainerParser\ContainerParserInterface;
use Mimmi20\NavigationHelper\Htmlify\HtmlifyInterface;

use function array_merge;
use function array_reverse;
use function array_unshift;
use function assert;
use function count;
use function get_class;
use function gettype;
use function implode;
use function is_array;
use function is_int;
use function is_object;
use function is_string;
use function sprintf;

use const PHP_EOL;

/**
 * Helper for printing breadcrumbs.
 */
final class Breadcrumbs extends \Laminas\View\Helper\Navigation\Breadcrumbs
{
    use HelperTrait;

    private RendererInterface $renderer;

    private EscapeHtml $escapeHtml;

    public function __construct(
        ServiceLocatorInterface $serviceLocator,
        Logger $logger,
        HtmlifyInterface $htmlify,
        ContainerParserInterface $containerParser,
        PhpRenderer $renderer,
        EscapeHtml $escapeHtml,
        ?Translate $translator = null
    ) {
        $this->serviceLocator  = $serviceLocator;
        $this->logger          = $logger;
        $this->htmlify         = $htmlify;
        $this->containerParser = $containerParser;
        $this->view            = $renderer;
        $this->escapeHtml      = $escapeHtml;
        $this->translator      = $translator;
    }

    /**
     * Sets navigation container the helper operates on by default
     *
     * Implements {@link ViewHelperInterface::setContainer()}.
     *
     * @param AbstractContainer|string|null $container default is null, meaning container will be reset
     *
     * @throws InvalidArgumentException
     */
    public function setContainer($container = null): self
    {
        $this->container = $this->containerParser->parseContainer($container);

        return $this;
    }

    /**
     * Returns the navigation container helper operates on by default
     *
     * Implements {@link ViewHelperInterface::getContainer()}.
     *
     * If no container is set, a new container will be instantiated and
     * stored in the helper.
     *
     * @return AbstractContainer navigation container
     */
    public function getContainer(): AbstractContainer
    {
        if (null === $this->container) {
            $this->container = new Navigation();
        }

        return $this->container;
    }

    /**
     * Sets which partial view script to use for rendering menu.
     *
     * @param array<int, string>|ModelInterface|string|null $partial partial view script or null. If an array is
     *                                                               given, the first value is used for the partial view script.
     */
    public function setPartial($partial): self
    {
        if (null === $partial || is_string($partial) || is_array($partial) || $partial instanceof ModelInterface) {
            $this->partial = $partial;
        }

        return $this;
    }

    /**
     * Returns partial view script to use for rendering menu.
     *
     * @return array<int, string>|ModelInterface|string|null
     */
    public function getPartial()
    {
        return $this->partial;
    }

    /**
     * Returns minimum depth a page must have to be included when rendering
     */
    public function getMinDepth(): ?int
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
     * @param AbstractContainer|string|null $container [optional] container to render. Default is
     *                                                 to render the container registered in the helper.
     */
    public function renderStraight($container = null): string
    {
        $content = $this->parentRenderStraight($container);

        if ('' === $content) {
            return '';
        }

        $indent = $this->getIndent();

        $html  = $indent . '<nav aria-label="breadcrumb">' . PHP_EOL;
        $html .= $indent . '    <ul class="breadcrumb">' . PHP_EOL;
        $html .= $content;
        $html .= $indent . '    </ul>' . PHP_EOL;
        $html .= $indent . '</nav>' . PHP_EOL;

        return $html;
    }

    /**
     * Render a partial with the given "model".
     *
     * @param array<mixed>                                  $params
     * @param AbstractContainer|string|null                 $container
     * @param array<int, string>|ModelInterface|string|null $partial
     *
     * @throws Exception\RuntimeException         if no partial provided
     * @throws Exception\InvalidArgumentException if partial is invalid array
     */
    protected function renderPartialModel(array $params, $container, $partial): string
    {
        if (null === $partial) {
            $partial = $this->getPartial();
        }

        if (null === $partial || '' === $partial || [] === $partial) {
            throw new Exception\RuntimeException(
                'Unable to render breadcrumbs: No partial view script provided'
            );
        }

        if (is_array($partial)) {
            if (2 !== count($partial)) {
                throw new Exception\InvalidArgumentException(
                    'Unable to render breadcrumbs: A view partial supplied as '
                    . 'an array must contain one value: the partial view script'
                );
            }

            $partial = $partial[0];
        }

        $container = $this->containerParser->parseContainer($container);

        if (null === $container) {
            $container = $this->getContainer();
        }

        $model  = array_merge($params, ['pages' => []], ['separator' => $this->getSeparator()]);
        $active = $this->findActive($container);

        if ([] !== $active) {
            $active = $active['page'];

            assert(
                $active instanceof AbstractPage,
                sprintf(
                    '$active should be an Instance of %s, but was %s',
                    AbstractPage::class,
                    is_object($active) ? get_class($active) : gettype($active)
                )
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

        return $this->view->render($partial, $model);
    }

    /**
     * Renders breadcrumbs by chaining 'a' elements with the separator
     * registered in the helper.
     *
     * @param AbstractContainer|string|null $container [optional] container to render. Default is
     *                                                 to render the container registered in the helper.
     */
    private function parentRenderStraight($container = null): string
    {
        $container = $this->containerParser->parseContainer($container);

        if (null === $container) {
            $container = $this->getContainer();
        }

        $active = $this->findActive($container);

        // find deepest active
        if (!$active) {
            return '';
        }

        $active = $active['page'];

        // put the deepest active page last in breadcrumbs
        if ($this->getLinkLast()) {
            $html[] = $this->renderBreadcrumbItem(
                $this->htmlify->toHtml(self::class, $active),
                $active->getCustomProperties()['liClass'] ?? '',
                $active->isActive()
            );
        } else {
            $label = $active->getLabel();

            if (null !== $this->translator) {
                $label = ($this->translator)($label, $active->getTextDomain());
            }

            $html[] = $this->renderBreadcrumbItem(
                ($this->escapeHtml)($label),
                $active->getCustomProperties()['liClass'] ?? '',
                $active->isActive()
            );
        }

        // walk back to root
        while ($parent = $active->getParent()) {
            if ($parent instanceof AbstractPage) {
                // prepend crumb to html
                $entry = $this->renderBreadcrumbItem(
                    $this->htmlify->toHtml(self::class, $parent),
                    $parent->getCustomProperties()['liClass'] ?? '',
                    $parent->isActive()
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

        $html  = $indent . '        ' . sprintf('<li class="%s"%s>', implode(' ', $classes), $aria) . PHP_EOL;
        $html .= $indent . '            ' . $content . PHP_EOL;
        $html .= $indent . '        </li>' . PHP_EOL;

        return $html;
    }

    private function renderSeparator(): string
    {
        return $this->getIndent() . '        ' . $this->getSeparator() . PHP_EOL;
    }

    /**
     * @param array<string> $html
     */
    private function combineRendered(array $html): string
    {
        return [] !== $html ? implode($this->renderSeparator(), $html) : '';
    }
}