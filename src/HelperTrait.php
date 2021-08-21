<?php
/**
 * This file is part of the mimmi20/mezzio-navigation-laminasviewrenderer package.
 *
 * Copyright (c) 2020-2021, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mimmi20\LaminasView\BootstrapNavigation;

use Interop\Container\ContainerInterface;
use Laminas\Log\Logger;
use Laminas\Navigation\AbstractContainer;
use Laminas\Navigation\Navigation;
use Laminas\Navigation\Page\AbstractPage;
use Laminas\Permissions\Acl\Acl;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\Stdlib\Exception\DomainException;
use Laminas\Stdlib\Exception\InvalidArgumentException;
use Laminas\View\Exception;
use Mimmi20\NavigationHelper\Accept\AcceptHelperInterface;
use Mimmi20\NavigationHelper\ContainerParser\ContainerParserInterface;
use Mimmi20\NavigationHelper\FindActive\FindActiveInterface;
use Mimmi20\NavigationHelper\Htmlify\HtmlifyInterface;
use Psr\Container\ContainerExceptionInterface;

use function assert;
use function call_user_func_array;
use function get_class;
use function gettype;
use function is_int;
use function is_object;
use function sprintf;
use function str_repeat;

/**
 * Base class for navigational helpers.
 *
 * Duck-types against Laminas\I18n\Translator\TranslatorAwareInterface.
 */
trait HelperTrait
{
    private ?string $navigation = null;

    private Logger $logger;

    private HtmlifyInterface $htmlify;

    private ContainerParserInterface $containerParser;

    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * Whether container should be injected when proxying
     */
    private bool $injectContainer = true;

    /**
     * Helper entry point
     *
     * @param AbstractContainer|string|null $container container to operate on
     *
     * @throws InvalidArgumentException
     */
    public function __invoke($container = null): self
    {
        if (null !== $container) {
            $this->setContainer($container);
        }

        return $this;
    }

    /**
     * Magic overload: Proxy calls to the navigation container
     *
     * @param string       $method    method name in container
     * @param array<mixed> $arguments rguments to pass
     *
     * @return mixed
     */
    public function __call($method, array $arguments = [])
    {
        return call_user_func_array(
            [$this->getContainer(), $method],
            $arguments
        );
    }

    /**
     * Magic overload: Proxy to {@link render()}.
     *
     * This method will trigger an E_USER_ERROR if rendering the helper causes
     * an exception to be thrown.
     *
     * Implements {@link ViewHelperInterface::__toString()}.
     */
    public function __toString(): string
    {
        try {
            return $this->render();
        } catch (Exception\ExceptionInterface | InvalidArgumentException | DomainException $e) {
            $this->logger->err($e);

            return '';
        }
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
     * Finds the deepest active page in the given container
     *
     * @param AbstractContainer|string|null $container to search
     * @param int|null                                  $minDepth  [optional] minimum depth
     *                                                             required for page to be
     *                                                             valid. Default is to use
     *                                                             {@link getMinDepth()}. A
     *                                                             null value means no minimum
     *                                                             depth required.
     * @param int|null                                  $maxDepth  [optional] maximum depth
     *                                                             a page can have to be
     *                                                             valid. Default is to use
     *                                                             {@link getMaxDepth()}. A
     *                                                             null value means no maximum
     *                                                             depth required.
     *
     * @return array<string, int|\Laminas\Navigation\Page\AbstractPage|null> an associative array with the values 'depth' and 'page', or an empty array if not found
     * @phpstan-return array{page?: \Laminas\Navigation\Page\AbstractPage|null, depth?: int|null}
     *
     * @throws InvalidArgumentException
     */
    public function findActive($container, $minDepth = null, $maxDepth = -1)
    {
        $container = $this->containerParser->parseContainer($container);

        if (null === $container) {
            $container = $this->getContainer();
        }

        if (null === $minDepth) {
            $minDepth = $this->getMinDepth();
        }

        if ((!is_int($maxDepth) || 0 > $maxDepth) && null !== $maxDepth) {
            $maxDepth = $this->getMaxDepth();
        }

        try {
            $findActiveHelper = $this->serviceLocator->build(
                FindActiveInterface::class,
                [
                    'authorization' => $this->getUseAcl() ? $this->getAcl() : null,
                    'renderInvisible' => $this->getRenderInvisible(),
                    'role' => $this->getRole(),
                ]
            );
        } catch (ContainerExceptionInterface $e) {
            $this->logger->err($e);

            return [];
        }

        return $findActiveHelper->find($container, $minDepth, $maxDepth);
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
     * @param \Laminas\Navigation\Page\AbstractPage $page      page to check
     * @param bool          $recursive [optional] if true, page will not be
     *                                 accepted if it is the descendant of
     *                                 a page that is not accepted. Default
     *                                 is true
     *
     * @return bool Whether page should be accepted
     */
    public function accept(AbstractPage $page, $recursive = true): bool
    {
        try {
            $acceptHelper = $this->serviceLocator->build(
                AcceptHelperInterface::class,
                [
                    'authorization' => $this->getUseAuthorization() ? $this->getAuthorization() : null,
                    'renderInvisible' => $this->getRenderInvisible(),
                    'role' => $this->getRole(),
                ]
            );
        } catch (ContainerExceptionInterface $e) {
            $this->logger->err($e);

            return false;
        }

        assert(
            $acceptHelper instanceof AcceptHelperInterface,
            sprintf(
                '$acceptHelper should be an Instance of %s, but was %s',
                AcceptHelperInterface::class,
                is_object($acceptHelper) ? get_class($acceptHelper) : gettype($acceptHelper)
            )
        );

        return $acceptHelper->accept($page, $recursive);
    }

    /**
     * Returns an HTML string containing an 'a' element for the given page
     *
     * @param \Laminas\Navigation\Page\AbstractPage $page page to generate HTML for
     *
     * @return string HTML string (<a href="…">Label</a>)
     */
    public function htmlify(\Laminas\Navigation\Page\AbstractPage $page): string
    {
        return $this->htmlify->toHtml(static::class, $page);
    }

    /**
     * @return \Laminas\ServiceManager\ServiceLocatorInterface
     */
    public function getServiceLocator(): ServiceLocatorInterface
    {
        return $this->serviceLocator;
    }
}
