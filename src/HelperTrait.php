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
use Laminas\Navigation\Navigation;
use Laminas\Navigation\Page\AbstractPage;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\Stdlib\Exception\InvalidArgumentException;
use Laminas\View\Exception\ExceptionInterface;
use Laminas\View\Model\ModelInterface;
use Mimmi20\NavigationHelper\Accept\AcceptHelperInterface;
use Mimmi20\NavigationHelper\ContainerParser\ContainerParserInterface;
use Mimmi20\NavigationHelper\FindActive\FindActiveInterface;
use Psr\Container\ContainerExceptionInterface;

use function array_key_exists;
use function assert;
use function get_debug_type;
use function is_array;
use function is_int;
use function is_string;
use function sprintf;

/**
 * Base class for navigational helpers.
 *
 * Duck-types against Laminas\I18n\Translator\TranslatorAwareInterface.
 */
trait HelperTrait
{
    protected ServiceLocatorInterface $serviceBuilder;

    /**
     * AbstractContainer to operate on by default
     *
     * @var AbstractContainer<AbstractPage>|null
     */
    protected AbstractContainer | null $pageContainer = null;

    /**
     * Partial view script to use for rendering menu.
     *
     * @var array<int, string>|ModelInterface|string|null
     */
    protected array | ModelInterface | string | null $partialTemplate = null;
    private string | null $navigation                                 = null;
    private ContainerParserInterface $containerParser;

    /**
     * Whether container should be injected when proxying
     */
    private bool $injectContainer = true;

    /**
     * Helper entry point
     *
     * @param AbstractContainer<AbstractPage>|string|null $container container to operate on
     *
     * @throws InvalidArgumentException
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function __invoke($container = null): self
    {
        if ($container !== null) {
            $this->setContainer($container);
        }

        return $this;
    }

    /**
     * Magic overload: Proxy to {@link render()}.
     *
     * This method will trigger an E_USER_ERROR if rendering the helper causes
     * an exception to be thrown.
     *
     * Implements {@link ViewHelperInterface::__toString()}.
     *
     * @throws ExceptionInterface
     */
    public function __toString(): string
    {
        return $this->render();
    }

    /**
     * Sets navigation container the helper operates on by default
     *
     * Implements {@link ViewHelperInterface::setContainer()}.
     *
     * @param AbstractContainer<AbstractPage>|string|null $container default is null, meaning container will be reset
     *
     * @throws InvalidArgumentException
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function setContainer($container = null): self
    {
        $container = $this->containerParser->parseContainer($container);

        $this->pageContainer = $container;

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
     * @return AbstractContainer<AbstractPage> navigation container
     *
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     */
    public function getContainer(): AbstractContainer
    {
        if ($this->pageContainer === null) {
            $this->pageContainer = new Navigation();
        }

        return $this->pageContainer;
    }

    /**
     * Sets which partial view script to use for rendering menu.
     *
     * @param array<int, string>|int|ModelInterface|string|null $partial partial view script or null. If an array is
     *                                                                   given, the first value is used for the partial view script.
     *
     * @throws void
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function setPartial($partial): self
    {
        if (
            $partial === null
            || is_string($partial)
            || is_array($partial)
            || $partial instanceof ModelInterface
        ) {
            $this->partialTemplate = $partial;
        }

        return $this;
    }

    /**
     * Returns partial view script to use for rendering menu.
     *
     * @return array<int, string>|ModelInterface|string|null
     *
     * @throws void
     */
    public function getPartial(): array | ModelInterface | string | null
    {
        return $this->partialTemplate;
    }

    /**
     * Finds the deepest active page in the given container
     *
     * @param AbstractContainer<AbstractPage>|string|null $container to search
     * @param int|null                                    $minDepth  [optional] minimum depth
     *                                                               required for page to be
     *                                                               valid. Default is to use
     *                                                               {@link getMinDepth()}. A
     *                                                               null value means no minimum
     *                                                               depth required.
     * @param int|null                                    $maxDepth  [optional] maximum depth
     *                                                               a page can have to be
     *                                                               valid. Default is to use
     *                                                               {@link getMaxDepth()}. A
     *                                                               null value means no maximum
     *                                                               depth required.
     *
     * @return array<string, (AbstractPage|int|null)> an associative array with the values 'depth' and 'page', or an empty array if not found
     * @phpstan-return array{page?: (AbstractPage|null), depth?: (int|null)}
     *
     * @throws InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws ContainerExceptionInterface
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function findActive($container, $minDepth = null, $maxDepth = -1): array
    {
        $container = $this->containerParser->parseContainer($container);

        if ($container === null) {
            $container = $this->getContainer();
        }

        if ($minDepth === null) {
            $minDepth = $this->getMinDepth();
        }

        if ((!is_int($maxDepth) || 0 > $maxDepth) && $maxDepth !== null) {
            $maxDepth = $this->getMaxDepth();
        }

        $findActiveHelper = $this->serviceBuilder->build(
            FindActiveInterface::class,
            [
                'authorization' => $this->getUseAcl() ? $this->getAcl() : null,
                'renderInvisible' => $this->getRenderInvisible(),
                'role' => $this->getRole(),
            ],
        );
        assert(
            $findActiveHelper instanceof FindActiveInterface,
            sprintf(
                '$findActiveHelper should be an Instance of %s, but was %s',
                FindActiveInterface::class,
                get_debug_type($findActiveHelper),
            ),
        );

        $active = $findActiveHelper->find($container, $minDepth, $maxDepth);

        if (array_key_exists('page', $active)) {
            assert($active['page'] instanceof AbstractPage || $active['page'] === null);
        }

        return $active;
    }

    // Iterator filter methods:

    /**
     * Determines whether a page should be accepted when iterating
     *
     * Rules:
     * - If a page is not visible it is not accepted, unless RenderInvisible has
     *   been set to true
     * - If $useAuthorization is true (default is true):
     *      - Page is accepted if Authorization returns true, otherwise false
     * - If page is accepted and $recursive is true, the page
     *   will not be accepted if it is the descendant of a non-accepted page
     *
     * @param AbstractPage $page      page to check
     * @param bool         $recursive [optional] if true, page will not be
     *                                accepted if it is the descendant of
     *                                a page that is not accepted. Default
     *                                is true
     *
     * @return bool Whether page should be accepted
     *
     * @throws ContainerExceptionInterface
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function accept(AbstractPage $page, $recursive = true): bool
    {
        $acceptHelper = $this->serviceBuilder->build(
            AcceptHelperInterface::class,
            [
                'authorization' => $this->getUseAcl() ? $this->getAcl() : null,
                'renderInvisible' => $this->getRenderInvisible(),
                'role' => $this->getRole(),
            ],
        );

        return $acceptHelper->accept($page, $recursive);
    }

    /** @throws void */
    public function getServiceLocator(): ServiceLocatorInterface
    {
        return $this->serviceBuilder;
    }
}
