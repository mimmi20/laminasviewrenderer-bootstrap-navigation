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

use Laminas\Navigation\AbstractContainer;
use Laminas\Navigation\Page\AbstractPage;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\Stdlib\Exception\InvalidArgumentException;
use Laminas\View\Exception;
use Laminas\View\Exception\DomainException;
use Laminas\View\Helper\EscapeHtml;
use Laminas\View\Helper\EscapeHtmlAttr;
use Laminas\View\Model\ModelInterface;
use Laminas\View\Renderer\PhpRenderer;
use Mimmi20\LaminasView\Helper\HtmlElement\Helper\HtmlElementInterface;
use Mimmi20\NavigationHelper\ContainerParser\ContainerParserInterface;
use Psr\Container\ContainerExceptionInterface;
use RecursiveIteratorIterator;

use function array_diff_key;
use function array_filter;
use function array_flip;
use function array_key_exists;
use function array_merge;
use function array_unique;
use function assert;
use function count;
use function get_debug_type;
use function implode;
use function in_array;
use function is_array;
use function is_int;
use function is_string;
use function rtrim;
use function sprintf;
use function str_repeat;

use const PHP_EOL;

/**
 * Helper for rendering menus from navigation containers.
 *
 * @phpstan-type Direction Menu::DROP_ORIENTATION_DOWN|Menu::DROP_ORIENTATION_UP|Menu::DROP_ORIENTATION_START|Menu::DROP_ORIENTATION_END
 * @phpstan-type Sublink Menu::STYLE_SUBLINK_LINK|Menu::STYLE_SUBLINK_SPAN|Menu::STYLE_SUBLINK_BUTTON|Menu::STYLE_SUBLINK_DETAILS
 * @phpstan-type Style Menu::STYLE_UL|Menu::STYLE_OL
 */
final class Menu extends \Laminas\View\Helper\Navigation\Menu
{
    use HelperTrait;

    public const STYLE_UL = 'ul';

    public const STYLE_OL = 'ol';

    public const STYLE_SUBLINK_LINK = 'link';

    public const STYLE_SUBLINK_SPAN = 'span';

    public const STYLE_SUBLINK_BUTTON = 'button';

    public const STYLE_SUBLINK_DETAILS = 'details';

    public const DROP_ORIENTATION_DOWN = 'down';

    public const DROP_ORIENTATION_UP = 'up';

    public const DROP_ORIENTATION_START = 'start';

    public const DROP_ORIENTATION_END = 'end';

    /**
     * Allowed sizes
     *
     * @var array<string>
     */
    private static array $sizes = [
        'sm',
        'md',
        'lg',
        'xl',
        // added in Bootstrap 5
        'xxl',
    ];

    /** @throws void */
    public function __construct(
        ServiceLocatorInterface $serviceBuilder,
        ContainerParserInterface $containerParser,
        private readonly EscapeHtmlAttr $escapeHtmlAttr,
        private readonly PhpRenderer $renderer,
        private readonly EscapeHtml $escapeHtml,
        private readonly HtmlElementInterface $htmlElement,
    ) {
        $this->serviceBuilder  = $serviceBuilder;
        $this->containerParser = $containerParser;
    }

    /**
     * Renders helper.
     *
     * Renders a HTML 'ul' for the given $container. If $container is not given,
     * the container registered in the helper will be used.
     *
     * Available $options:
     *
     * @param AbstractContainer<AbstractPage>|string|null $container [optional] container to create menu from.
     *                                                 Default is to use the container retrieved from {@link getContainer()}.
     * @param array<string, bool|int|string|null>         $options   [optional] options for controlling rendering
     * @phpstan-param array{in-navbar?: bool, ulClass?: string|null, tabs?: bool, pills?: bool, fill?: bool, justified?: bool, centered?: bool, right-aligned?: bool, vertical?: string, direction?: Direction, style?: self::STYLE_UL|self::STYLE_OL, substyle?: string, sublink?: Sublink, onlyActiveBranch?: bool, renderParents?: bool, indent?: int|string|null} $options
     *
     * @throws InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws ContainerExceptionInterface
     * @throws \Laminas\View\Exception\InvalidArgumentException
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function renderMenu($container = null, array $options = []): string
    {
        $container = $this->containerParser->parseContainer($container);

        if ($container === null) {
            $container = $this->getContainer();
        }

        $options = $this->normalizeOptions($options);

        if ($options['onlyActiveBranch'] && !$options['renderParents']) {
            return $this->renderDeepestMenu(
                $container,
                $options['ulClass'],
                $options['indent'],
                $options['minDepth'],
                $options['maxDepth'],
                $options['escapeLabels'],
                $options['addClassToListItem'],
                $options['liActiveClass'],
                $options['liClass'],
                $options['direction'],
                $options['sublink'],
                $options['ulRole'],
                $options['liRole'],
                $options['role'] ?? '',
            );
        }

        return $this->renderNormalMenu(
            $container,
            $options['ulClass'],
            $options['indent'],
            $options['minDepth'],
            $options['maxDepth'],
            $options['onlyActiveBranch'],
            $options['escapeLabels'],
            $options['addClassToListItem'],
            $options['liActiveClass'],
            $options['liClass'],
            $options['direction'],
            $options['style'],
            $options['sublink'],
            $options['ulRole'],
            $options['liRole'],
            $options['role'] ?? '',
            $options['dark'],
        );
    }

    /**
     * Renders the inner-most sub menu for the active page in the $container.
     *
     * This is a convenience method which is equivalent to the following call:
     * <code>
     * renderMenu($container, array(
     *     'indent'           => $indent,
     *     'ulClass'          => $ulClass,
     *     'liClass'          => $liClass,
     *     'minDepth'         => null,
     *     'maxDepth'         => null,
     *     'onlyActiveBranch' => true,
     *     'renderParents'    => false,
     *     'liActiveClass'    => $liActiveClass
     * ));
     * </code>
     *
     * @param AbstractContainer<AbstractPage>|null $container     [optional] container to create menu from.
     *                                              Default is to use the container retrieved from {@link getContainer()}.
     * @param string|null                          $ulClass       [optional] CSS class to use for UL element.
     *                                                            Default is to use the value from {@link getUlClass()}.
     * @param string|null                          $liClass       [optional] CSS class to use for LI elements
     * @param int|string|null                      $indent        [optional] indentation as a string or number
     *                                                            of spaces. Default is to use the value retrieved from
     *                                                            {@link getIndent()}.
     * @param string|null                          $liActiveClass [optional] CSS class to use for UL
     *                                                            element. Default is to use the value from {@link getUlClass()}.
     *
     * @throws InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws ContainerExceptionInterface
     * @throws \Laminas\View\Exception\InvalidArgumentException
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function renderSubMenu(
        AbstractContainer | null $container = null,
        $ulClass = null,
        $indent = null,
        $liActiveClass = null,
        string | null $liClass = null,
    ): string {
        return $this->renderMenu(
            $container,
            [
                'indent' => $indent,
                'ulClass' => $ulClass,
                'liClass' => $liClass,
                'minDepth' => null,
                'maxDepth' => null,
                'onlyActiveBranch' => true,
                'renderParents' => false,
                'escapeLabels' => true,
                'addClassToListItem' => false,
                'liActiveClass' => $liActiveClass,
            ],
        );
    }

    /**
     * Returns an HTML string containing an 'a' element for the given page if
     * the page's href is not empty, and a 'span' element if it is empty.
     *
     * Overrides {@link AbstractHelper::htmlify()}.
     *
     * @param AbstractPage $page               page to generate HTML for
     * @param bool         $escapeLabel        Whether to escape the label
     * @param bool         $addClassToListItem Whether to add the page class to the list item
     *
     * @throws \Laminas\View\Exception\InvalidArgumentException
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function htmlify(AbstractPage $page, $escapeLabel = true, $addClassToListItem = false): string
    {
        return $this->toHtml($page, ['escapeLabels' => $escapeLabel, 'sublink' => null], [], true);
    }

    /**
     * Renders the deepest active menu within [minDepth, maxDepth], (called from {@link renderMenu()}).
     *
     * @param AbstractContainer<AbstractPage> $container          container to render
     * @param string                          $ulClass            CSS class for first UL
     * @param string                          $indent             initial indentation
     * @param int|null                        $minDepth           minimum depth
     * @param int|null                        $maxDepth           maximum depth
     * @param bool                            $escapeLabels       Whether or not to escape the labels
     * @param bool                            $addClassToListItem Whether or not page class applied to <li> element
     * @param string                          $liActiveClass      CSS class for active LI
     * @phpstan-param Direction $direction
     * @phpstan-param Sublink $subLink
     *
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws ContainerExceptionInterface
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    protected function renderDeepestMenu(
        AbstractContainer $container,
        $ulClass,
        $indent,
        $minDepth,
        $maxDepth,
        $escapeLabels,
        $addClassToListItem,
        $liActiveClass,
        string $liClass = '',
        string $direction = self::DROP_ORIENTATION_DOWN,
        string $subLink = self::STYLE_SUBLINK_LINK,
        string | null $ulRole = '',
        string | null $liRole = '',
        string $role = '',
    ): string {
        $active = $this->findActive($container, $minDepth - 1, $maxDepth);

        if (!array_key_exists('page', $active) || !($active['page'] instanceof AbstractPage)) {
            return '';
        }

        $activePage = $active['page'];

        // special case if active page is one below minDepth
        if (!array_key_exists('depth', $active) || $active['depth'] < $minDepth) {
            if (!$activePage->hasPages(!$this->renderInvisible)) {
                return '';
            }
        } elseif (!$active['page']->hasPages(!$this->renderInvisible)) {
            // found pages has no children; render siblings
            $activePage = $active['page']->getParent();
        } elseif (is_int($maxDepth) && $active['depth'] + 1 > $maxDepth) {
            // children are below max depth; render siblings
            $activePage = $active['page']->getParent();
        }

        assert(
            $activePage instanceof AbstractContainer,
            sprintf(
                '$activePage should be an Instance of %s, but was %s',
                AbstractContainer::class,
                get_debug_type($activePage),
            ),
        );

        $subHtml = '';

        foreach ($activePage as $subPage) {
            if (!$this->accept($subPage)) {
                continue;
            }

            $isActive = $subPage->isActive(true);

            // render li tag and page
            $liClasses      = [];
            $pageAttributes = [];

            $this->setAttributes(
                $subPage,
                [
                    'role' => $role,
                    'direction' => $direction,
                    'sublink' => $subLink,
                    'liActiveClass' => $liActiveClass,
                    'liClass' => $liClass,
                    'addClassToListItem' => $addClassToListItem,
                ],
                0,
                false,
                $isActive,
                $liClasses,
                $pageAttributes,
            );

            $subHtml .= $indent . '    <li';

            if ($liClasses !== []) {
                $subHtml .= ' class="' . ($this->escapeHtmlAttr)($this->combineClasses(
                    $liClasses,
                )) . '"';
            }

            if (!empty($liRole)) {
                $subHtml .= ' role="' . ($this->escapeHtmlAttr)($liRole) . '"';
            }

            $subHtml .= '>' . PHP_EOL;
            $subHtml .= $indent . '        ';
            $subHtml .= $this->toHtml(
                $subPage,
                [
                    'sublink' => $subLink,
                    'escapeLabels' => $escapeLabels,
                ],
                $pageAttributes,
                false,
            );
            $subHtml .= PHP_EOL;
            $subHtml .= $indent . '    </li>' . PHP_EOL;
        }

        if ($subHtml === '') {
            return '';
        }

        $html = $indent . '<ul';

        if ($ulClass) {
            $html .= ' class="' . ($this->escapeHtmlAttr)($ulClass) . '"';
        }

        if (!empty($ulRole)) {
            $html .= ' role="' . ($this->escapeHtmlAttr)($ulRole) . '"';
        }

        $html .= '>' . PHP_EOL;

        return $html . ($subHtml . $indent . '</ul>');
    }

    /**
     * Renders a normal menu (called from {@link renderMenu()}).
     *
     * @param AbstractContainer<AbstractPage> $container          container to render
     * @param string                          $ulClass            CSS class for first UL
     * @param string                          $indent             initial indentation
     * @param int|null                        $minDepth           minimum depth
     * @param int|null                        $maxDepth           maximum depth
     * @param bool                            $onlyActive         render only active branch?
     * @param bool                            $escapeLabels       Whether or not to escape the labels
     * @param bool                            $addClassToListItem Whether or not page class applied to <li> element
     * @param string                          $liActiveClass      CSS class for active LI
     * @param string                          $liClass            CSS class for every LI
     * @phpstan-param Direction $direction
     * @phpstan-param Style $style
     * @phpstan-param Sublink $subLink
     *
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws ContainerExceptionInterface
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    protected function renderNormalMenu(
        AbstractContainer $container,
        $ulClass,
        $indent,
        $minDepth,
        $maxDepth,
        $onlyActive,
        $escapeLabels,
        $addClassToListItem,
        $liActiveClass,
        string $liClass = '',
        string $direction = self::DROP_ORIENTATION_DOWN,
        string $style = self::STYLE_UL,
        string $subLink = self::STYLE_SUBLINK_LINK,
        string | null $ulRole = '',
        string | null $liRole = '',
        string $role = '',
        bool $dark = false,
    ): string {
        $html = '';

        // find deepest active
        $found = $this->findActive($container, $minDepth, $maxDepth);

        // create iterator
        $iterator = new RecursiveIteratorIterator($container, RecursiveIteratorIterator::SELF_FIRST);

        if (is_int($maxDepth)) {
            $iterator->setMaxDepth($maxDepth);
        }

        // iterate container
        $prevDepth = -1;
        $prevPage  = null;

        $element = match ($style) {
            self::STYLE_OL => 'ol',
            default => 'ul',
        };

        foreach ($iterator as $page) {
            assert(
                $page instanceof AbstractPage,
                sprintf(
                    '$page should be an Instance of %s, but was %s',
                    AbstractPage::class,
                    get_debug_type($page),
                ),
            );

            $depth = $iterator->getDepth();

            [$accept, $isActive] = $this->isPageAccepted(
                $page,
                [
                    'minDepth' => $minDepth,
                    'maxDepth' => $maxDepth,
                    'onlyActiveBranch' => $onlyActive,
                ],
                $depth,
                $found,
            );

            if (!$accept) {
                continue;
            }

            // make sure indentation is correct
            $iteratorDepth = $depth;

            assert(is_int($minDepth));

            $depth   -= $minDepth;
            $myIndent = $indent . str_repeat('        ', $depth);

            if ($depth > $prevDepth) {
                // start new ul tag
                if ($depth === 0) {
                    $ulClass = ' class="' . ($this->escapeHtmlAttr)($ulClass) . '"';

                    if (!empty($ulRole)) {
                        $ulClass .= ' role="' . ($this->escapeHtmlAttr)($ulRole) . '"';
                    }
                } else {
                    $ulClasses = [];

                    $ulClasses[] = $subLink === self::STYLE_SUBLINK_DETAILS
                        ? 'dropdown-details-menu'
                        : 'dropdown-menu';

                    if ($dark) {
                        $ulClasses[] = 'dropdown-menu-dark';
                    }

                    $ulClass = ' class="' . ($this->escapeHtmlAttr)($this->combineClasses(
                        $ulClasses,
                    )) . '"';

                    if ($prevPage?->getId() !== null) {
                        $ulClass .= ' aria-labelledby="' . ($this->escapeHtmlAttr)($prevPage->getId()) . '"';
                    }
                }

                $html .= $myIndent . '<' . $element . $ulClass . '>' . PHP_EOL;
            } elseif ($prevDepth > $depth) {
                // close li/ul tags until we're at current depth
                for ($i = $prevDepth; $i > $depth; --$i) {
                    $ind   = $indent . str_repeat('        ', $i);
                    $html .= $ind . '    </li>' . PHP_EOL;
                    $html .= $ind . '</' . $element . '>' . PHP_EOL;

                    if ($subLink !== self::STYLE_SUBLINK_DETAILS) {
                        continue;
                    }

                    $html .= $ind . '</details>' . PHP_EOL;
                }

                // close previous li tag
                $html .= $myIndent . '    </li>' . PHP_EOL;
            } else {
                // close previous li tag
                $html .= $myIndent . '    </li>' . PHP_EOL;
            }

            $anySubpageAccepted = $this->hasAcceptedSubpages(
                $page,
                ['maxDepth' => $maxDepth],
                $iteratorDepth,
            );

            // render li tag and page
            $liClasses      = [];
            $pageAttributes = [];

            $this->setAttributes(
                $page,
                [
                    'role' => $role,
                    'direction' => $direction,
                    'sublink' => $subLink,
                    'liActiveClass' => $liActiveClass,
                    'liClass' => $liClass,
                    'addClassToListItem' => $addClassToListItem,
                ],
                $depth,
                $anySubpageAccepted,
                $isActive,
                $liClasses,
                $pageAttributes,
            );

            if ($liClasses === []) {
                $allLiClasses = '';
            } else {
                $combinedLiClasses = $this->combineClasses($liClasses);

                $allLiClasses = $combinedLiClasses === ''
                    ? ''
                    : ' class="' . ($this->escapeHtmlAttr)($combinedLiClasses) . '"';
            }

            if ($depth === 0 && !empty($liRole)) {
                $allLiClasses .= ' role="' . ($this->escapeHtmlAttr)($liRole) . '"';
            }

            $html .= $myIndent . '    <li' . $allLiClasses . '>' . PHP_EOL;

            if ($anySubpageAccepted && $subLink === self::STYLE_SUBLINK_DETAILS) {
                $html .= $myIndent . '        <details>' . PHP_EOL;
            }

            $html .= $myIndent . '        ';
            $html .= $this->toHtml(
                $page,
                [
                    'sublink' => $subLink,
                    'escapeLabels' => $escapeLabels,
                ],
                $pageAttributes,
                $anySubpageAccepted,
            );
            $html .= PHP_EOL;

            // store as previous depth for next iteration
            $prevDepth = $depth;
            $prevPage  = $page;
        }

        if ($html) {
            // done iterating container; close open ul/li tags
            for ($i = $prevDepth + 1; 0 < $i; --$i) {
                $myIndent = $indent . str_repeat('        ', $i - 1);
                $html    .= $myIndent . '    </li>' . PHP_EOL;
                $html    .= $myIndent . '</' . $element . '>' . PHP_EOL;

                if (1 >= $i || $subLink !== self::STYLE_SUBLINK_DETAILS) {
                    continue;
                }

                $html .= $myIndent . '</details>' . PHP_EOL;
            }

            $html = rtrim($html, PHP_EOL);
        }

        return $html;
    }

    /**
     * Render a partial with the given "model".
     *
     * @param array<mixed>                                  $params
     * @param AbstractContainer<AbstractPage>|string|null   $container
     * @param array<int, string>|ModelInterface|string|null $partial
     *
     * @throws Exception\RuntimeException                             if no partial provided
     * @throws Exception\InvalidArgumentException                     if partial is invalid array
     * @throws InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
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
                'Unable to render menu: No partial view script provided',
            );
        }

        if (is_array($partial)) {
            if (count($partial) !== 2) {
                throw new Exception\InvalidArgumentException(
                    'Unable to render menu: A view partial supplied as '
                    . 'an array must contain one value: the partial view script',
                );
            }

            $partial = $partial[0];
        }

        $container = $this->containerParser->parseContainer($container);

        if ($container === null) {
            $container = $this->getContainer();
        }

        return $this->renderer->render(
            $partial,
            array_merge($params, ['container' => $container]),
        );
    }

    /**
     * Normalizes given render options.
     *
     * @param array<string, bool|int|string|null> $options [optional] options to normalize
     * @phpstan-param array{ulClass?: string|null, liClass?: string|null, indent?: int|string|null, minDepth?: int|null, maxDepth?: int|null, onlyActiveBranch?: bool, escapeLabels?: bool, renderParents?: bool, addClassToListItem?: bool, liActiveClass?: string|null, tabs?: bool, pills?: bool, fill?: bool, justified?: bool, centered?: bool, right-aligned?: bool, vertical?: string, direction?: Direction, style?: Style, substyle?: string, sublink?: Sublink, in-navbar?: bool, dark?: bool} $options
     *
     * @return array<string, bool|int|string|null>
     * @phpstan-return array{ulClass: string, liClass: string, indent: string, minDepth: int, maxDepth: int|null, onlyActiveBranch: bool, escapeLabels: bool, renderParents: bool, addClassToListItem: bool, liActiveClass: string, role: string|null, style: Style, substyle: string, sublink: Sublink, class: string, ulRole: string|null, liRole: string|null, direction: Direction, dark: bool}
     *
     * @throws InvalidArgumentException
     */
    protected function normalizeOptions(array $options = []): array
    {
        if (isset($options['indent'])) {
            assert(is_int($options['indent']) || is_string($options['indent']));
            $options['indent'] = $this->getWhitespace($options['indent']);
        } else {
            $options['indent'] = $this->getIndent();
        }

        $options['liClass'] ??= '';

        if (!array_key_exists('minDepth', $options)) {
            $options['minDepth'] = $this->getMinDepth();
        }

        if (0 > $options['minDepth'] || $options['minDepth'] === null) {
            $options['minDepth'] = 0;
        }

        if (!array_key_exists('maxDepth', $options)) {
            $options['maxDepth'] = $this->getMaxDepth();
        }

        if (!array_key_exists('onlyActiveBranch', $options)) {
            $options['onlyActiveBranch'] = $this->getOnlyActiveBranch();
        }

        if (!array_key_exists('renderParents', $options)) {
            $options['renderParents'] = $this->getRenderParents();
        }

        if (!array_key_exists('addClassToListItem', $options)) {
            $options['addClassToListItem'] = $this->getAddClassToListItem();
        }

        $options['liActiveClass'] = array_key_exists(
            'liActiveClass',
            $options,
        ) && $options['liActiveClass'] !== null
            ? $options['liActiveClass']
            : $this->getLiActiveClass();

        if (
            array_key_exists('vertical', $options)
            && is_string($options['vertical'])
            && !array_key_exists('direction', $options)
        ) {
            $options['direction'] = self::DROP_ORIENTATION_END;
        } elseif (!array_key_exists('direction', $options)) {
            $options['direction'] = self::DROP_ORIENTATION_DOWN;
        }

        $options['ulClass'] = $this->normalizeUlClass($options);
        $options['class']   = $this->normalizeItemClass($options);
        $options['ulRole']  = null;
        $options['liRole']  = null;
        $options['role']    = null;

        if (array_key_exists('tabs', $options) || array_key_exists('pills', $options)) {
            $options['ulRole'] = 'tablist';
            $options['liRole'] = 'presentation';
            $options['role']   = 'tab';
        }

        if (!array_key_exists('style', $options)) {
            $options['style'] = self::STYLE_UL;
        }

        if (!array_key_exists('substyle', $options)) {
            $options['substyle'] = self::STYLE_UL;
        }

        if (!array_key_exists('sublink', $options)) {
            $options['sublink'] = self::STYLE_SUBLINK_LINK;
        }

        if (!array_key_exists('dark', $options)) {
            $options['dark'] = false;
        }

        if (!array_key_exists('escapeLabels', $options)) {
            $options['escapeLabels'] = true;
        }

        return $options;
    }

    /** @throws InvalidArgumentException */
    private function getSizeClass(string $size, string $prefix): string
    {
        if (!in_array($size, self::$sizes, true)) {
            throw new InvalidArgumentException('Size "' . $size . '" does not exist');
        }

        return sprintf($prefix, $size);
    }

    /**
     * @param AbstractPage                        $page    current page to check
     * @param array<string, bool|int|string|null> $options options for controlling rendering
     * @param int                                 $level   current level of rendering
     *
     * @throws ContainerExceptionInterface
     */
    private function hasAcceptedSubpages(AbstractPage $page, array $options, int $level): bool
    {
        $hasVisiblePages    = $page->hasPages(true);
        $anySubpageAccepted = false;

        assert(is_int($options['maxDepth']) || $options['maxDepth'] === null);

        if ($hasVisiblePages && ($options['maxDepth'] === null || $level + 1 <= $options['maxDepth'])) {
            foreach ($page->getPages() as $subpage) {
                if (!$this->accept($subpage, false)) {
                    continue;
                }

                $anySubpageAccepted = true;
            }
        }

        return $anySubpageAccepted;
    }

    /**
     * @param AbstractPage                         $page    current page to check
     * @param array<string, bool|int|string|null>  $options options for controlling rendering
     * @param int                                  $level   current level of rendering
     * @param array<string, AbstractPage|int|null> $found
     * @phpstan-param array{page?: AbstractPage|null, depth?: int|null} $found
     *
     * @return array<bool>
     *
     * @throws ContainerExceptionInterface
     */
    private function isPageAccepted(AbstractPage $page, array $options, int $level, array $found): array
    {
        if ($level < $options['minDepth'] || !$this->accept($page)) {
            // page is below minDepth or not accepted by acl/visibility
            return [false, false];
        }

        $isActive = $page->isActive(true);
        $accept   = true;

        assert(is_int($options['maxDepth']) || $options['maxDepth'] === null);

        if ($options['onlyActiveBranch'] && !$isActive) {
            // page is not active itself, but might be in the active branch
            $accept = $this->isActiveBranch($found, $page, $options['maxDepth']);
        }

        return [$accept, $isActive];
    }

    /**
     * @param AbstractPage                        $page           current page to check
     * @param array<string, bool|int|string|null> $options        options for controlling rendering
     * @param int                                 $level          current level of rendering
     * @param array<int, string>                  $liClasses
     * @param array<string, string>               $pageAttributes
     * @phpstan-param array{role?: string, direction?: Direction, sublink?: Sublink, liActiveClass?: string, liClass?: string, addClassToListItem?: bool} $options
     *
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     */
    private function setAttributes(
        AbstractPage $page,
        array $options,
        int $level,
        bool $anySubpageAccepted,
        bool $isActive,
        array &$liClasses,
        array &$pageAttributes,
    ): void {
        $pageClasses = [];

        if ($level === 0) {
            $liClasses[]   = 'nav-item';
            $pageClasses[] = 'nav-link';

            if (!empty($options['role']) && !$anySubpageAccepted) {
                $pageAttributes['role'] = $options['role'];
            }
        } else {
            $pageClasses[] = 'dropdown-item';
        }

        if ($anySubpageAccepted && array_key_exists('direction', $options)) {
            $liClasses[] = match ($options['direction']) {
                self::DROP_ORIENTATION_UP => 'dropup',
                self::DROP_ORIENTATION_END => 'dropend',
                self::DROP_ORIENTATION_START => 'dropstart',
                default => 'dropdown',
            };

            if (array_key_exists('sublink', $options)) {
                if (
                    $options['sublink'] === self::STYLE_SUBLINK_BUTTON
                    || $options['sublink'] === self::STYLE_SUBLINK_DETAILS
                ) {
                    $pageClasses[] = 'btn';
                }

                if ($options['sublink'] !== self::STYLE_SUBLINK_DETAILS) {
                    $pageClasses[]                    = 'dropdown-toggle';
                    $pageAttributes['data-bs-toggle'] = 'dropdown';
                }
            }

            $pageAttributes['aria-expanded'] = 'false';
            $pageAttributes['role']          = 'button';
        }

        // Is page active?
        if ($isActive) {
            if (array_key_exists('liActiveClass', $options)) {
                $liClasses[] = $options['liActiveClass'];
            }

            if ($level === 0) {
                $pageAttributes['aria-current'] = 'page';
            }

            $liActiveClass = $page->get('li-active-class');

            if ($liActiveClass) {
                $liClasses[] = $liActiveClass;
            }
        }

        if (array_key_exists('liClass', $options)) {
            $liClasses[] = $options['liClass'];
        }

        $liClass = $page->get('li-class');

        if ($liClass) {
            $liClasses[] = $liClass;
        }

        // Add CSS class from page to <li>
        if (
            array_key_exists('addClassToListItem', $options)
            && $options['addClassToListItem']
            && $page->getClass()
        ) {
            $liClasses[] = $page->getClass();
        } elseif ($page->getClass()) {
            $pageClasses[] = $page->getClass();
        }

        $pageAttributes['class'] = $this->combineClasses($pageClasses);
    }

    /**
     * Returns an HTML string for the given page
     *
     * @param AbstractPage                        $page       page to generate HTML for
     * @param array<string, bool|int|string|null> $options    options for controlling rendering
     * @param array<string, string>               $attributes
     *
     * @return string HTML string
     *
     * @throws \Laminas\View\Exception\InvalidArgumentException
     */
    private function toHtml(AbstractPage $page, array $options, array $attributes, bool $anySubpageAccepted): string
    {
        $label      = (string) $page->getLabel();
        $title      = $page->getTitle();
        $translator = $this->getTranslator();

        if ($translator !== null) {
            $textDomain = $page->getTextDomain() ?? 'default';
            assert(is_string($textDomain));

            if ($label !== '') {
                $label = $translator->translate($label, $textDomain);
            }

            if ($title !== null) {
                $title = $translator->translate($title, $textDomain);
            }
        }

        // get attribs for element

        $attributes['id']    = $page->getId();
        $attributes['title'] = $title;

        if ($anySubpageAccepted && $options['sublink'] === self::STYLE_SUBLINK_DETAILS) {
            $element = 'summary';
        } elseif ($anySubpageAccepted && $options['sublink'] === self::STYLE_SUBLINK_BUTTON) {
            $element            = 'button';
            $attributes['type'] = 'button';
        } elseif (
            (
                $anySubpageAccepted
                && $options['sublink'] === self::STYLE_SUBLINK_SPAN
            )
            || !$page->getHref()
        ) {
            $element = 'span';
        } else {
            $element              = 'a';
            $attributes['href']   = $page->getHref();
            $attributes['target'] = $page->getTarget();
        }

        // remove sitemap specific attributes
        $attributes = array_diff_key(
            array_merge($attributes, $page->getCustomProperties()),
            array_flip(['lastmod', 'changefreq', 'priority']),
        );

        if ($label !== '' && $options['escapeLabels']) {
            $label = ($this->escapeHtml)($label);
            assert(is_string($label));
        }

        return $this->htmlElement->toHtml($element, $attributes, $label);
    }

    /**
     * @param array<string, bool|int|string|null> $options [optional] options to normalize
     *
     * @throws InvalidArgumentException
     */
    private function normalizeUlClass(array $options): string
    {
        $ulClasses = array_key_exists('in-navbar', $options) ? ['navbar-nav'] : ['nav'];

        $ulClasses[] = isset($options['ulClass']) ? (string) $options['ulClass'] : $this->getUlClass();

        foreach (
            [
                'tabs' => 'nav-tabs',
                'pills' => 'nav-pills',
                'fill' => 'nav-fill',
                'justified' => 'nav-justified',
                'centered' => 'justify-content-center',
                'right-aligned' => 'justify-content-end',
            ] as $optionname => $optionvalue
        ) {
            if (!array_key_exists($optionname, $options)) {
                continue;
            }

            $ulClasses[] = $optionvalue;
        }

        if (array_key_exists('vertical', $options) && is_string($options['vertical'])) {
            $ulClasses[] = 'flex-column';
            $ulClasses[] = $this->getSizeClass($options['vertical'], 'flex-%s-row');
        }

        return $this->combineClasses($ulClasses);
    }

    /**
     * @param array<string, bool|int|string|null> $options [optional] options to normalize
     *
     * @throws InvalidArgumentException
     */
    private function normalizeItemClass(array $options): string
    {
        $itemClasses = [];

        if (array_key_exists('vertical', $options) && is_string($options['vertical'])) {
            $itemClasses[] = $this->getSizeClass($options['vertical'], 'flex-%s-fill');
            $itemClasses[] = $this->getSizeClass($options['vertical'], 'text-%s-center');
        }

        return $this->combineClasses($itemClasses);
    }

    /**
     * @param array<string, AbstractPage|int|null> $found
     * @phpstan-param array{page?: AbstractPage|null, depth?: int|null} $found
     *
     * @throws void
     */
    private function isActiveBranch(array $found, AbstractPage $page, int | null $maxDepth): bool
    {
        if (!array_key_exists('page', $found) || !($found['page'] instanceof AbstractPage)) {
            return false;
        }

        $foundPage  = $found['page'];
        $foundDepth = $found['depth'] ?? 0;

        $accept = false;

        if ($foundPage->hasPage($page)) {
            // accept if page is a direct child of the active page
            $accept = true;
        } elseif (
            $foundPage->getParent() instanceof AbstractContainer
            && $foundPage->getParent()->hasPage($page)
        ) {
            // page is a sibling of the active page...
            if (
                !$foundPage->hasPages(!$this->renderInvisible)
                || is_int($maxDepth) && $foundDepth + 1 > $maxDepth
            ) {
                // accept if active page has no children, or the
                // children are too deep to be rendered
                $accept = true;
            }
        }

        return $accept;
    }

    /**
     * @param array<int|string, string|null> $classes
     *
     * @throws void
     */
    private function combineClasses(array $classes): string
    {
        return implode(' ', array_unique(array_filter($classes)));
    }
}
