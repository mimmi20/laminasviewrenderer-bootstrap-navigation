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

namespace Mimmi20Test\LaminasView\BootstrapNavigation;

use Laminas\I18n\Translator\TranslatorInterface as Translator;
use Laminas\Navigation\AbstractContainer;
use Laminas\Navigation\Navigation;
use Laminas\Navigation\Page\AbstractPage;
use Laminas\Navigation\Page\Uri;
use Laminas\Permissions\Acl\Acl;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\Exception\ExceptionInterface;
use Laminas\View\Exception\InvalidArgumentException;
use Laminas\View\Exception\RuntimeException;
use Laminas\View\Helper\EscapeHtml;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Renderer\RendererInterface;
use Mimmi20\LaminasView\BootstrapNavigation\Breadcrumbs;
use Mimmi20\NavigationHelper\Accept\AcceptHelperInterface;
use Mimmi20\NavigationHelper\ContainerParser\ContainerParserInterface;
use Mimmi20\NavigationHelper\FindActive\FindActiveInterface;
use Mimmi20\NavigationHelper\Htmlify\HtmlifyInterface;
use PHPUnit\Framework\Constraint\IsInstanceOf;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;

use function assert;

use const PHP_EOL;

final class BreadcrumbsTest extends TestCase
{
    /** @throws InvalidArgumentException */
    protected function tearDown(): void
    {
        Breadcrumbs::setDefaultAcl(null);
        Breadcrumbs::setDefaultRole(null);
    }

    /** @throws Exception */
    public function testSetView(): void
    {
        $view = $this->createMock(RendererInterface::class);

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::never())
            ->method('__invoke');

        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::never())
            ->method('render');
        $renderer->expects(self::never())
            ->method('plugin');
        $renderer->expects(self::never())
            ->method('getHelperPluginManager');

        $helper = new Breadcrumbs($serviceLocator, $containerParser, $htmlify, $renderer, $escapeHtml);

        $helper->setView($view);

        self::assertSame($view, $helper->getView());
        self::assertSame($serviceLocator, $helper->getServiceLocator());
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     */
    public function testSetContainer(): void
    {
        $container = $this->createMock(AbstractContainer::class);

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $matcher         = self::exactly(2);
        $containerParser->expects($matcher)
            ->method('parseContainer')
            ->willReturnCallback(
                static function (AbstractContainer | null $containerInput) use ($matcher, $container): AbstractContainer | null {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertNull($containerInput),
                        default => self::assertSame($container, $containerInput),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        1 => null,
                        default => $container,
                    };
                },
            );

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::never())
            ->method('__invoke');

        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::never())
            ->method('render');
        $renderer->expects(self::never())
            ->method('plugin');
        $renderer->expects(self::never())
            ->method('getHelperPluginManager');

        $helper = new Breadcrumbs($serviceLocator, $containerParser, $htmlify, $renderer, $escapeHtml);

        $container1 = $helper->getContainer();

        self::assertInstanceOf(Navigation::class, $container1);

        $helper->setContainer();

        $container2 = $helper->getContainer();

        self::assertInstanceOf(Navigation::class, $container2);
        self::assertNotSame($container1, $container2);

        $helper->setContainer($container);

        self::assertSame($container, $helper->getContainer());
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     */
    public function testSetContainerWithStringDefaultAndNavigationNotFound(): void
    {
        $name = 'default';

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willThrowException(new InvalidArgumentException('test'));

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::never())
            ->method('__invoke');

        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::never())
            ->method('render');
        $renderer->expects(self::never())
            ->method('plugin');
        $renderer->expects(self::never())
            ->method('getHelperPluginManager');

        $helper = new Breadcrumbs($serviceLocator, $containerParser, $htmlify, $renderer, $escapeHtml);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('test');
        $this->expectExceptionCode(0);

        $helper->setContainer($name);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     */
    public function testSetContainerWithStringFound(): void
    {
        $container = $this->createMock(AbstractContainer::class);
        $name      = 'Mezzio\Navigation\Top';

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::never())
            ->method('__invoke');

        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::never())
            ->method('render');
        $renderer->expects(self::never())
            ->method('plugin');
        $renderer->expects(self::never())
            ->method('getHelperPluginManager');

        $helper = new Breadcrumbs($serviceLocator, $containerParser, $htmlify, $renderer, $escapeHtml);

        $helper->setContainer($name);

        self::assertSame($container, $helper->getContainer());
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     * @throws ContainerExceptionInterface
     */
    public function testDoNotAccept(): void
    {
        $container = $this->createMock(AbstractContainer::class);
        $name      = 'Mezzio\Navigation\Top';

        $page = $this->getMockBuilder(AbstractPage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::never())
            ->method('isVisible');
        $page->expects(self::never())
            ->method('getResource');
        $page->expects(self::never())
            ->method('getPrivilege');
        $page->expects(self::never())
            ->method('getParent');
        $page->expects(self::never())
            ->method('isActive');
        $page->expects(self::never())
            ->method('getLabel');
        $page->expects(self::never())
            ->method('getTextDomain');
        $page->expects(self::never())
            ->method('getTitle');
        $page->expects(self::never())
            ->method('getId');
        $page->expects(self::never())
            ->method('getClass');
        $page->expects(self::never())
            ->method('getHref');
        $page->expects(self::never())
            ->method('getTarget');
        $page->expects(self::never())
            ->method('hasPage');
        $page->expects(self::never())
            ->method('hasPages');
        $page->expects(self::never())
            ->method('getCustomProperties');
        $page->expects(self::never())
            ->method('get');

        $acceptHelper = $this->getMockBuilder(AcceptHelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $acceptHelper->expects(self::once())
            ->method('accept')
            ->with($page)
            ->willReturn(false);

        $auth = $this->getMockBuilder(Acl::class)
            ->disableOriginalConstructor()
            ->getMock();

        $role = 'testRole';

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::once())
            ->method('build')
            ->with(
                AcceptHelperInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ],
            )
            ->willReturn($acceptHelper);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::never())
            ->method('__invoke');

        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::never())
            ->method('render');
        $renderer->expects(self::never())
            ->method('plugin');
        $renderer->expects(self::never())
            ->method('getHelperPluginManager');

        $helper = new Breadcrumbs($serviceLocator, $containerParser, $htmlify, $renderer, $escapeHtml);

        $helper->setContainer($name);
        $helper->setRole($role);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        self::assertFalse($helper->accept($page));
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     * @throws ContainerExceptionInterface
     */
    public function testDoNotAcceptWithException(): void
    {
        $exception = new ServiceNotFoundException('test');

        $container = $this->createMock(AbstractContainer::class);
        $name      = 'Mezzio\Navigation\Top';

        $page = $this->getMockBuilder(AbstractPage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::never())
            ->method('isVisible');
        $page->expects(self::never())
            ->method('getResource');
        $page->expects(self::never())
            ->method('getPrivilege');
        $page->expects(self::never())
            ->method('getParent');
        $page->expects(self::never())
            ->method('isActive');
        $page->expects(self::never())
            ->method('getLabel');
        $page->expects(self::never())
            ->method('getTextDomain');
        $page->expects(self::never())
            ->method('getTitle');
        $page->expects(self::never())
            ->method('getId');
        $page->expects(self::never())
            ->method('getClass');
        $page->expects(self::never())
            ->method('getHref');
        $page->expects(self::never())
            ->method('getTarget');
        $page->expects(self::never())
            ->method('hasPage');
        $page->expects(self::never())
            ->method('hasPages');
        $page->expects(self::never())
            ->method('getCustomProperties');
        $page->expects(self::never())
            ->method('get');

        $auth = $this->getMockBuilder(Acl::class)
            ->disableOriginalConstructor()
            ->getMock();

        $role = 'testRole';

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::once())
            ->method('build')
            ->with(
                AcceptHelperInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ],
            )
            ->willThrowException($exception);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::never())
            ->method('__invoke');

        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::never())
            ->method('render');
        $renderer->expects(self::never())
            ->method('plugin');
        $renderer->expects(self::never())
            ->method('getHelperPluginManager');

        $helper = new Breadcrumbs($serviceLocator, $containerParser, $htmlify, $renderer, $escapeHtml);

        $helper->setContainer($name);
        $helper->setRole($role);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        try {
            $helper->accept($page);

            self::fail('Exception expected');
        } catch (ServiceNotFoundException $e) {
            self::assertSame($exception, $e);
        }
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     * @throws \Laminas\I18n\Exception\RuntimeException
     */
    public function testHtmlify(): void
    {
        $expected = '<a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped" targetEscaped="_blankEscaped">testLabelTranslatedAndEscaped</a>';

        $container = $this->createMock(AbstractContainer::class);
        $name      = 'Mezzio\Navigation\Top';

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

        $page = $this->getMockBuilder(AbstractPage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::never())
            ->method('isVisible');
        $page->expects(self::never())
            ->method('getResource');
        $page->expects(self::never())
            ->method('getPrivilege');
        $page->expects(self::never())
            ->method('getParent');
        $page->expects(self::never())
            ->method('isActive');
        $page->expects(self::never())
            ->method('getLabel');
        $page->expects(self::never())
            ->method('getTextDomain');
        $page->expects(self::never())
            ->method('getTitle');
        $page->expects(self::never())
            ->method('getId');
        $page->expects(self::never())
            ->method('getClass');
        $page->expects(self::never())
            ->method('getHref');
        $page->expects(self::never())
            ->method('getTarget');
        $page->expects(self::never())
            ->method('hasPage');
        $page->expects(self::never())
            ->method('hasPages');
        $page->expects(self::never())
            ->method('getCustomProperties');
        $page->expects(self::never())
            ->method('get');

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::once())
            ->method('toHtml')
            ->with(Breadcrumbs::class, $page)
            ->willReturn($expected);

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::never())
            ->method('__invoke');

        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::never())
            ->method('render');
        $renderer->expects(self::never())
            ->method('plugin');
        $renderer->expects(self::never())
            ->method('getHelperPluginManager');

        $helper = new Breadcrumbs($serviceLocator, $containerParser, $htmlify, $renderer, $escapeHtml);

        $helper->setContainer($name);

        self::assertSame($expected, $helper->htmlify($page));
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     * @throws ContainerExceptionInterface
     */
    public function testFindActiveNoActivePages(): void
    {
        $name = 'Mezzio\Navigation\Top';

        $page = $this->getMockBuilder(AbstractPage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::never())
            ->method('isVisible');
        $page->expects(self::never())
            ->method('getResource');
        $page->expects(self::never())
            ->method('getPrivilege');
        $page->expects(self::never())
            ->method('getParent');
        $page->expects(self::never())
            ->method('isActive');
        $page->expects(self::never())
            ->method('getLabel');
        $page->expects(self::never())
            ->method('getTextDomain');
        $page->expects(self::never())
            ->method('getTitle');
        $page->expects(self::never())
            ->method('getId');
        $page->expects(self::never())
            ->method('getClass');
        $page->expects(self::never())
            ->method('getHref');
        $page->expects(self::never())
            ->method('getTarget');
        $page->expects(self::never())
            ->method('hasPage');
        $page->expects(self::never())
            ->method('hasPages');
        $page->expects(self::never())
            ->method('getCustomProperties');
        $page->expects(self::never())
            ->method('get');

        $container = new Navigation();
        $container->addPage($page);

        $role     = 'testRole';
        $maxDepth = 42;
        $minDepth = 0;

        $findActiveHelper = $this->getMockBuilder(FindActiveInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, $minDepth, $maxDepth)
            ->willReturn([]);

        $auth = $this->getMockBuilder(Acl::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isAllowed');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::once())
            ->method('build')
            ->with(
                FindActiveInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ],
            )
            ->willReturn($findActiveHelper);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::never())
            ->method('__invoke');

        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::never())
            ->method('render');
        $renderer->expects(self::never())
            ->method('plugin');
        $renderer->expects(self::never())
            ->method('getHelperPluginManager');

        $helper = new Breadcrumbs($serviceLocator, $containerParser, $htmlify, $renderer, $escapeHtml);

        $helper->setRole($role);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        self::assertSame([], $helper->findActive($name, $minDepth, $maxDepth));
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     * @throws ContainerExceptionInterface
     */
    public function testFindActiveOneActivePage(): void
    {
        $name = 'Mezzio\Navigation\Top';

        $page = $this->getMockBuilder(AbstractPage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::never())
            ->method('isVisible');
        $page->expects(self::never())
            ->method('getResource');
        $page->expects(self::never())
            ->method('getPrivilege');
        $page->expects(self::never())
            ->method('getParent');
        $page->expects(self::never())
            ->method('isActive');
        $page->expects(self::never())
            ->method('getLabel');
        $page->expects(self::never())
            ->method('getTextDomain');
        $page->expects(self::never())
            ->method('getTitle');
        $page->expects(self::never())
            ->method('getId');
        $page->expects(self::never())
            ->method('getClass');
        $page->expects(self::never())
            ->method('getHref');
        $page->expects(self::never())
            ->method('getTarget');
        $page->expects(self::never())
            ->method('hasPage');
        $page->expects(self::never())
            ->method('hasPages');
        $page->expects(self::never())
            ->method('getCustomProperties');
        $page->expects(self::never())
            ->method('get');

        $container = new Navigation();
        $container->addPage($page);

        $role     = 'testRole';
        $maxDepth = 42;
        $minDepth = 0;

        $findActiveHelper = $this->getMockBuilder(FindActiveInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, $minDepth, $maxDepth)
            ->willReturn(
                [
                    'page' => $page,
                    'depth' => 0,
                ],
            );

        $auth = $this->getMockBuilder(Acl::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isAllowed');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::once())
            ->method('build')
            ->with(
                FindActiveInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ],
            )
            ->willReturn($findActiveHelper);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::never())
            ->method('__invoke');

        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::never())
            ->method('render');
        $renderer->expects(self::never())
            ->method('plugin');
        $renderer->expects(self::never())
            ->method('getHelperPluginManager');

        $helper = new Breadcrumbs($serviceLocator, $containerParser, $htmlify, $renderer, $escapeHtml);

        $helper->setRole($role);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        $expected = [
            'page' => $page,
            'depth' => 0,
        ];

        self::assertSame($expected, $helper->findActive($name, $minDepth, $maxDepth));
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     * @throws ContainerExceptionInterface
     */
    public function testFindActiveWithoutContainer(): void
    {
        $role     = 'testRole';
        $maxDepth = 42;
        $minDepth = 1;

        $findActiveHelper = $this->getMockBuilder(FindActiveInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with(new IsInstanceOf(Navigation::class), $minDepth, $maxDepth)
            ->willReturn([]);

        $auth = $this->getMockBuilder(Acl::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isAllowed');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::once())
            ->method('build')
            ->with(
                FindActiveInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ],
            )
            ->willReturn($findActiveHelper);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with(null)
            ->willReturn(null);

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::never())
            ->method('__invoke');

        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::never())
            ->method('render');
        $renderer->expects(self::never())
            ->method('plugin');
        $renderer->expects(self::never())
            ->method('getHelperPluginManager');

        $helper = new Breadcrumbs($serviceLocator, $containerParser, $htmlify, $renderer, $escapeHtml);

        $helper->setRole($role);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        $expected = [];

        self::assertSame($expected, $helper->findActive(null, $minDepth, $maxDepth));
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     * @throws ContainerExceptionInterface
     */
    public function testFindActiveNoActivePageWithoutDepth(): void
    {
        $name = 'Mezzio\Navigation\Top';

        $page = $this->getMockBuilder(AbstractPage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::never())
            ->method('isVisible');
        $page->expects(self::never())
            ->method('getResource');
        $page->expects(self::never())
            ->method('getPrivilege');
        $page->expects(self::never())
            ->method('getParent');
        $page->expects(self::never())
            ->method('isActive');
        $page->expects(self::never())
            ->method('getLabel');
        $page->expects(self::never())
            ->method('getTextDomain');
        $page->expects(self::never())
            ->method('getTitle');
        $page->expects(self::never())
            ->method('getId');
        $page->expects(self::never())
            ->method('getClass');
        $page->expects(self::never())
            ->method('getHref');
        $page->expects(self::never())
            ->method('getTarget');
        $page->expects(self::never())
            ->method('hasPage');
        $page->expects(self::never())
            ->method('hasPages');
        $page->expects(self::never())
            ->method('getCustomProperties');
        $page->expects(self::never())
            ->method('get');

        $container = new Navigation();
        $container->addPage($page);

        $role     = 'testRole';
        $maxDepth = 42;
        $minDepth = 1;

        $findActiveHelper = $this->getMockBuilder(FindActiveInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, $minDepth, $maxDepth)
            ->willReturn([]);

        $auth = $this->getMockBuilder(Acl::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isAllowed');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::once())
            ->method('build')
            ->with(
                FindActiveInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ],
            )
            ->willReturn($findActiveHelper);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::never())
            ->method('__invoke');

        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::never())
            ->method('render');
        $renderer->expects(self::never())
            ->method('plugin');
        $renderer->expects(self::never())
            ->method('getHelperPluginManager');

        $helper = new Breadcrumbs($serviceLocator, $containerParser, $htmlify, $renderer, $escapeHtml);

        $helper->setRole($role);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        $expected = [];

        $helper->setMinDepth($minDepth);
        $helper->setMaxDepth($maxDepth);

        self::assertSame($expected, $helper->findActive($name));
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     * @throws ContainerExceptionInterface
     */
    public function testFindActiveOneActivePageOutOfRange(): void
    {
        $name = 'Mezzio\Navigation\Top';

        $page = $this->getMockBuilder(AbstractPage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::never())
            ->method('isVisible');
        $page->expects(self::never())
            ->method('getResource');
        $page->expects(self::never())
            ->method('getPrivilege');
        $page->expects(self::never())
            ->method('getParent');
        $page->expects(self::never())
            ->method('isActive');
        $page->expects(self::never())
            ->method('getLabel');
        $page->expects(self::never())
            ->method('getTextDomain');
        $page->expects(self::never())
            ->method('getTitle');
        $page->expects(self::never())
            ->method('getId');
        $page->expects(self::never())
            ->method('getClass');
        $page->expects(self::never())
            ->method('getHref');
        $page->expects(self::never())
            ->method('getTarget');
        $page->expects(self::never())
            ->method('hasPage');
        $page->expects(self::never())
            ->method('hasPages');
        $page->expects(self::never())
            ->method('getCustomProperties');
        $page->expects(self::never())
            ->method('get');

        $container = new Navigation();
        $container->addPage($page);

        $role     = 'testRole';
        $maxDepth = 42;
        $minDepth = 2;

        $findActiveHelper = $this->getMockBuilder(FindActiveInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, $minDepth, $maxDepth)
            ->willReturn([]);

        $auth = $this->getMockBuilder(Acl::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isAllowed');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::once())
            ->method('build')
            ->with(
                FindActiveInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ],
            )
            ->willReturn($findActiveHelper);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::never())
            ->method('__invoke');

        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::never())
            ->method('render');
        $renderer->expects(self::never())
            ->method('plugin');
        $renderer->expects(self::never())
            ->method('getHelperPluginManager');

        $helper = new Breadcrumbs($serviceLocator, $containerParser, $htmlify, $renderer, $escapeHtml);

        $helper->setRole($role);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        $expected = [];

        self::assertSame($expected, $helper->findActive($name, $minDepth, $maxDepth));
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     * @throws ContainerExceptionInterface
     */
    public function testFindActiveOneActivePageRecursive(): void
    {
        $name = 'Mezzio\Navigation\Top';

        $resource  = 'testResource';
        $privilege = 'testPrivilege';

        $parentPage = new Uri();
        $parentPage->setVisible(true);
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);

        $page = $this->getMockBuilder(AbstractPage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::never())
            ->method('isVisible');
        $page->expects(self::never())
            ->method('getResource');
        $page->expects(self::never())
            ->method('getPrivilege');
        $page->expects(self::never())
            ->method('getParent');
        $page->expects(self::never())
            ->method('isActive');
        $page->expects(self::never())
            ->method('getLabel');
        $page->expects(self::never())
            ->method('getTextDomain');
        $page->expects(self::never())
            ->method('getTitle');
        $page->expects(self::never())
            ->method('getId');
        $page->expects(self::never())
            ->method('getClass');
        $page->expects(self::never())
            ->method('getHref');
        $page->expects(self::never())
            ->method('getTarget');
        $page->expects(self::never())
            ->method('hasPage');
        $page->expects(self::never())
            ->method('hasPages');
        $page->expects(self::never())
            ->method('getCustomProperties');
        $page->expects(self::never())
            ->method('get');

        $parentPage->addPage($page);

        $container = new Navigation();
        $container->addPage($parentPage);

        $role     = 'testRole';
        $maxDepth = 0;
        $minDepth = 0;

        $findActiveHelper = $this->getMockBuilder(FindActiveInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, $minDepth, $maxDepth)
            ->willReturn(
                [
                    'page' => $parentPage,
                    'depth' => 0,
                ],
            );

        $auth = $this->getMockBuilder(Acl::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isAllowed');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::once())
            ->method('build')
            ->with(
                FindActiveInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ],
            )
            ->willReturn($findActiveHelper);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::never())
            ->method('__invoke');

        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::never())
            ->method('render');
        $renderer->expects(self::never())
            ->method('plugin');
        $renderer->expects(self::never())
            ->method('getHelperPluginManager');

        $helper = new Breadcrumbs($serviceLocator, $containerParser, $htmlify, $renderer, $escapeHtml);

        $helper->setRole($role);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        $expected = [
            'page' => $parentPage,
            'depth' => 0,
        ];

        self::assertSame($expected, $helper->findActive($name, $minDepth, $maxDepth));
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     * @throws ContainerExceptionInterface
     */
    public function testFindActiveOneActivePageRecursive2(): void
    {
        $name = 'Mezzio\Navigation\Top';

        $resource  = 'testResource';
        $privilege = 'testPrivilege';

        $parentPage = new Uri();
        $parentPage->setVisible(true);
        $parentPage->setActive(true);
        $parentPage->setUri('parent');
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);

        $page1 = new Uri();
        $page1->setActive(true);
        $page1->setUri('test1');

        $page2 = new Uri();
        $page2->setActive(true);
        $page1->setUri('test2');

        $parentPage->addPage($page1);
        $parentPage->addPage($page2);

        $parentParentPage = new Uri();
        $parentParentPage->setVisible(true);
        $parentParentPage->setActive(true);
        $parentParentPage->setUri('parentParent');

        $parentParentParentPage = new Uri();
        $parentParentParentPage->setVisible(true);
        $parentParentParentPage->setActive(true);
        $parentParentParentPage->setUri('parentParentParent');

        $parentParentPage->addPage($parentPage);
        $parentParentParentPage->addPage($parentParentPage);

        $container = new Navigation();
        $container->addPage($parentParentParentPage);

        $role     = 'testRole';
        $maxDepth = 1;
        $minDepth = 2;

        $findActiveHelper = $this->getMockBuilder(FindActiveInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, $minDepth, $maxDepth)
            ->willReturn([]);

        $auth = $this->getMockBuilder(Acl::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isAllowed');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::once())
            ->method('build')
            ->with(
                FindActiveInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ],
            )
            ->willReturn($findActiveHelper);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::never())
            ->method('__invoke');

        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::never())
            ->method('render');
        $renderer->expects(self::never())
            ->method('plugin');
        $renderer->expects(self::never())
            ->method('getHelperPluginManager');

        $helper = new Breadcrumbs($serviceLocator, $containerParser, $htmlify, $renderer, $escapeHtml);

        $helper->setRole($role);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        $expected = [];

        self::assertSame($expected, $helper->findActive($name, $minDepth, $maxDepth));
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     * @throws ContainerExceptionInterface
     */
    public function testFindActiveOneActivePageRecursive3(): void
    {
        $name = 'Mezzio\Navigation\Top';

        $resource  = 'testResource';
        $privilege = 'testPrivilege';

        $parentPage = new Uri();
        $parentPage->setVisible(true);
        $parentPage->setActive(true);
        $parentPage->setUri('parent');
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);

        $page1 = new Uri();
        $page1->setActive(true);
        $page1->setUri('test1');

        $page2 = new Uri();
        $page2->setActive(true);
        $page1->setUri('test2');

        $parentPage->addPage($page1);
        $parentPage->addPage($page2);

        $parentParentPage = new Uri();
        $parentParentPage->setVisible(true);
        $parentParentPage->setActive(true);
        $parentParentPage->setUri('parentParent');

        $parentParentParentPage = new Uri();
        $parentParentParentPage->setVisible(true);
        $parentParentParentPage->setActive(true);
        $parentParentParentPage->setUri('parentParentParent');

        $parentParentPage->addPage($parentPage);
        $parentParentParentPage->addPage($parentParentPage);

        $container = new Navigation();
        $container->addPage($parentParentParentPage);

        $role     = 'testRole';
        $maxDepth = -1;

        $findActiveHelper = $this->getMockBuilder(FindActiveInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, 1, $maxDepth)
            ->willReturn([]);

        $auth = $this->getMockBuilder(Acl::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isAllowed');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::once())
            ->method('build')
            ->with(
                FindActiveInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ],
            )
            ->willReturn($findActiveHelper);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::never())
            ->method('__invoke');

        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::never())
            ->method('render');
        $renderer->expects(self::never())
            ->method('plugin');
        $renderer->expects(self::never())
            ->method('getHelperPluginManager');

        $helper = new Breadcrumbs($serviceLocator, $containerParser, $htmlify, $renderer, $escapeHtml);

        $helper->setRole($role);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        $helper->setMinDepth(-1);
        $helper->setMaxDepth($maxDepth);

        $expected = [];

        self::assertSame($expected, $helper->findActive($name));
    }

    /** @throws Exception */
    public function testSetPartial(): void
    {
        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::never())
            ->method('__invoke');

        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::never())
            ->method('render');
        $renderer->expects(self::never())
            ->method('plugin');
        $renderer->expects(self::never())
            ->method('getHelperPluginManager');

        $helper = new Breadcrumbs($serviceLocator, $containerParser, $htmlify, $renderer, $escapeHtml);

        self::assertNull($helper->getPartial());

        $helper->setPartial('test');

        self::assertSame('test', $helper->getPartial());

        $helper->setPartial(1);

        self::assertSame('test', $helper->getPartial());
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     */
    public function testRenderPartialWithParamsWithoutPartial(): void
    {
        $name = 'Mezzio\Navigation\Top';

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::never())
            ->method('__invoke');

        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::never())
            ->method('render');
        $renderer->expects(self::never())
            ->method('plugin');
        $renderer->expects(self::never())
            ->method('getHelperPluginManager');

        $helper = new Breadcrumbs($serviceLocator, $containerParser, $htmlify, $renderer, $escapeHtml);

        $role = 'testRole';

        $helper->setRole($role);

        $auth = $this->getMockBuilder(Acl::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isAllowed');

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        $helper->setSeparator('/');
        $helper->setLinkLast(true);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to render breadcrumbs: No partial view script provided');
        $this->expectExceptionCode(0);

        $helper->renderPartialWithParams(['abc' => 'test'], $name);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     */
    public function testRenderPartialWithParamsWithWrongPartial(): void
    {
        $name = 'Mezzio\Navigation\Top';

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::never())
            ->method('__invoke');

        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::never())
            ->method('render');
        $renderer->expects(self::never())
            ->method('plugin');
        $renderer->expects(self::never())
            ->method('getHelperPluginManager');

        $helper = new Breadcrumbs($serviceLocator, $containerParser, $htmlify, $renderer, $escapeHtml);

        $role = 'testRole';

        $helper->setRole($role);

        $auth = $this->getMockBuilder(Acl::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isAllowed');

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        $helper->setSeparator('/');
        $helper->setLinkLast(true);

        $helper->setPartial(['a', 'b', 'c']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Unable to render breadcrumbs: A view partial supplied as an array must contain one value: the partial view script',
        );
        $this->expectExceptionCode(0);

        $helper->renderPartialWithParams(['abc' => 'test'], $name);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     */
    public function testRenderPartialWithParams(): void
    {
        $name = 'Mezzio\Navigation\Top';

        $resource  = 'testResource';
        $privilege = 'testPrivilege';

        $parentPage = new Uri();
        $parentPage->setVisible(true);
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);

        $page = $this->getMockBuilder(AbstractPage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::never())
            ->method('isVisible');
        $page->expects(self::never())
            ->method('getResource');
        $page->expects(self::never())
            ->method('getPrivilege');
        $page->expects(self::once())
            ->method('getParent')
            ->willReturn($parentPage);
        $page->expects(self::never())
            ->method('isActive');
        $page->expects(self::never())
            ->method('getLabel');
        $page->expects(self::never())
            ->method('getTextDomain');
        $page->expects(self::never())
            ->method('getTitle');
        $page->expects(self::never())
            ->method('getId');
        $page->expects(self::never())
            ->method('getClass');
        $page->expects(self::never())
            ->method('getHref');
        $page->expects(self::never())
            ->method('getTarget');
        $page->expects(self::never())
            ->method('hasPage');
        $page->expects(self::never())
            ->method('hasPages');
        $page->expects(self::never())
            ->method('getCustomProperties');
        $page->expects(self::never())
            ->method('get');

        $parentPage->addPage($page);

        $container = new Navigation();
        $container->addPage($parentPage);

        $role = 'testRole';

        $findActiveHelper = $this->getMockBuilder(FindActiveInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, 1, null)
            ->willReturn(
                [
                    'page' => $page,
                    'depth' => 2,
                ],
            );

        $auth = $this->getMockBuilder(Acl::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isAllowed');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::once())
            ->method('build')
            ->with(
                FindActiveInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ],
            )
            ->willReturn($findActiveHelper);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $matcher         = self::exactly(2);
        $containerParser->expects($matcher)
            ->method('parseContainer')
            ->willReturnCallback(
                static function (AbstractContainer | string | null $containerInput) use ($matcher, $container, $name): AbstractContainer {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($name, $containerInput),
                        default => self::assertSame($container, $containerInput),
                    };

                    return $container;
                },
            );

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::never())
            ->method('__invoke');

        $partial   = 'testPartial';
        $expected  = 'renderedPartial';
        $seperator = '/';

        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::once())
            ->method('render')
            ->with(
                $partial,
                ['abc' => 'test', 'pages' => [$parentPage, $page], 'separator' => $seperator],
            )
            ->willReturn($expected);
        $renderer->expects(self::never())
            ->method('plugin');
        $renderer->expects(self::never())
            ->method('getHelperPluginManager');

        $helper = new Breadcrumbs($serviceLocator, $containerParser, $htmlify, $renderer, $escapeHtml);

        $helper->setRole($role);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        $helper->setSeparator($seperator);
        $helper->setLinkLast(true);
        $helper->setPartial($partial);

        self::assertSame($expected, $helper->renderPartialWithParams(['abc' => 'test'], $name));
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     */
    public function testRenderPartialWithParamsAndArrayPartial(): void
    {
        $resource  = 'testResource';
        $privilege = 'testPrivilege';

        $parentPage = new Uri();
        $parentPage->setVisible(true);
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);

        $page = $this->getMockBuilder(AbstractPage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::never())
            ->method('isVisible');
        $page->expects(self::never())
            ->method('getResource');
        $page->expects(self::never())
            ->method('getPrivilege');
        $page->expects(self::once())
            ->method('getParent')
            ->willReturn($parentPage);
        $page->expects(self::never())
            ->method('isActive');
        $page->expects(self::never())
            ->method('getLabel');
        $page->expects(self::never())
            ->method('getTextDomain');
        $page->expects(self::never())
            ->method('getTitle');
        $page->expects(self::never())
            ->method('getId');
        $page->expects(self::never())
            ->method('getClass');
        $page->expects(self::never())
            ->method('getHref');
        $page->expects(self::never())
            ->method('getTarget');
        $page->expects(self::never())
            ->method('hasPage');
        $page->expects(self::never())
            ->method('hasPages');
        $page->expects(self::never())
            ->method('getCustomProperties');
        $page->expects(self::never())
            ->method('get');

        $parentPage->addPage($page);

        $container = new Navigation();
        $container->addPage($parentPage);

        $role = 'testRole';

        $findActiveHelper = $this->getMockBuilder(FindActiveInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, 1, null)
            ->willReturn(
                [
                    'page' => $page,
                    'depth' => 2,
                ],
            );

        $auth = $this->getMockBuilder(Acl::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isAllowed');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::once())
            ->method('build')
            ->with(
                FindActiveInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ],
            )
            ->willReturn($findActiveHelper);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $matcher         = self::exactly(3);
        $containerParser->expects($matcher)
            ->method('parseContainer')
            ->willReturnCallback(
                static function (AbstractContainer | string | null $containerInput) use ($matcher, $container): AbstractContainer | null {
                    match ($matcher->numberOfInvocations()) {
                        2 => self::assertNull($containerInput),
                        default => self::assertSame($container, $containerInput),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        2 => null,
                        default => $container,
                    };
                },
            );

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::never())
            ->method('__invoke');

        $partial   = 'testPartial';
        $expected  = 'renderedPartial';
        $seperator = '/';

        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::once())
            ->method('render')
            ->with(
                $partial,
                ['pages' => [$parentPage, $page], 'separator' => $seperator, 'abc' => 'test'],
            )
            ->willReturn($expected);
        $renderer->expects(self::never())
            ->method('plugin');
        $renderer->expects(self::never())
            ->method('getHelperPluginManager');

        $helper = new Breadcrumbs($serviceLocator, $containerParser, $htmlify, $renderer, $escapeHtml);

        $helper->setRole($role);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        $helper->setSeparator($seperator);
        $helper->setLinkLast(true);
        $helper->setContainer($container);

        self::assertSame(
            $expected,
            $helper->renderPartialWithParams(['abc' => 'test'], null, [$partial, 'test']),
        );
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     */
    public function testRenderPartialWithParamsAndArrayPartialRenderingPage(): void
    {
        $resource  = 'testResource';
        $privilege = 'testPrivilege';

        $parentPage = new Uri();
        $parentPage->setVisible(true);
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);
        $parentPage->setActive(true);

        $page = new Uri();
        $page->setVisible(true);
        $page->setResource($resource);
        $page->setPrivilege($privilege);
        $page->setActive(true);

        $subPage = $this->getMockBuilder(AbstractPage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $subPage->expects(self::never())
            ->method('isVisible');
        $subPage->expects(self::never())
            ->method('getResource');
        $subPage->expects(self::never())
            ->method('getPrivilege');
        $subPage->expects(self::once())
            ->method('getParent')
            ->willReturn($page);
        $subPage->expects(self::never())
            ->method('isActive');
        $subPage->expects(self::never())
            ->method('getLabel');
        $subPage->expects(self::never())
            ->method('getTextDomain');
        $subPage->expects(self::never())
            ->method('getTitle');
        $subPage->expects(self::never())
            ->method('getId');
        $subPage->expects(self::never())
            ->method('getClass');
        $subPage->expects(self::never())
            ->method('getHref');
        $subPage->expects(self::never())
            ->method('getTarget');
        $subPage->expects(self::never())
            ->method('hasPage');
        $subPage->expects(self::never())
            ->method('hasPages');
        $subPage->expects(self::never())
            ->method('getCustomProperties');
        $subPage->expects(self::never())
            ->method('get');

        $page->addPage($subPage);
        $parentPage->addPage($page);

        $role = 'testRole';

        $findActiveHelper = $this->getMockBuilder(FindActiveInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($parentPage, 1, null)
            ->willReturn(
                [
                    'page' => $subPage,
                    'depth' => 2,
                ],
            );

        $auth = $this->getMockBuilder(Acl::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isAllowed');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::once())
            ->method('build')
            ->with(
                FindActiveInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ],
            )
            ->willReturn($findActiveHelper);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $matcher         = self::exactly(3);
        $containerParser->expects($matcher)
            ->method('parseContainer')
            ->willReturnCallback(
                static function (AbstractContainer | string | null $containerInput) use ($matcher, $parentPage): AbstractContainer | null {
                    match ($matcher->numberOfInvocations()) {
                        2 => self::assertNull($containerInput),
                        default => self::assertSame($parentPage, $containerInput),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        2 => null,
                        default => $parentPage,
                    };
                },
            );

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::never())
            ->method('__invoke');

        $partial   = 'testPartial';
        $expected  = 'renderedPartial';
        $seperator = '/';

        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::once())
            ->method('render')
            ->with(
                $partial,
                ['pages' => [$parentPage, $page, $subPage], 'separator' => $seperator, 'abc' => 'test'],
            )
            ->willReturn($expected);
        $renderer->expects(self::never())
            ->method('plugin');
        $renderer->expects(self::never())
            ->method('getHelperPluginManager');

        $helper = new Breadcrumbs($serviceLocator, $containerParser, $htmlify, $renderer, $escapeHtml);

        $helper->setRole($role);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        $helper->setSeparator($seperator);
        $helper->setLinkLast(true);
        $helper->setContainer($parentPage);

        self::assertSame(
            $expected,
            $helper->renderPartialWithParams(['abc' => 'test'], null, [$partial, 'test']),
        );
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     */
    public function testRenderPartialWithParamsNoActivePage(): void
    {
        $name = 'Mezzio\Navigation\Top';

        $page = $this->getMockBuilder(AbstractPage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::never())
            ->method('isVisible');
        $page->expects(self::never())
            ->method('getResource');
        $page->expects(self::never())
            ->method('getPrivilege');
        $page->expects(self::never())
            ->method('getParent');
        $page->expects(self::never())
            ->method('isActive');
        $page->expects(self::never())
            ->method('getLabel');
        $page->expects(self::never())
            ->method('getTextDomain');
        $page->expects(self::never())
            ->method('getTitle');
        $page->expects(self::never())
            ->method('getId');
        $page->expects(self::never())
            ->method('getClass');
        $page->expects(self::never())
            ->method('getHref');
        $page->expects(self::never())
            ->method('getTarget');
        $page->expects(self::never())
            ->method('hasPage');
        $page->expects(self::never())
            ->method('hasPages');
        $page->expects(self::never())
            ->method('getCustomProperties');
        $page->expects(self::never())
            ->method('get');

        $container = new Navigation();
        $container->addPage($page);

        $role = 'testRole';

        $findActiveHelper = $this->getMockBuilder(FindActiveInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, 1, null)
            ->willReturn([]);

        $auth = $this->getMockBuilder(Acl::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isAllowed');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::once())
            ->method('build')
            ->with(
                FindActiveInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ],
            )
            ->willReturn($findActiveHelper);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $matcher         = self::exactly(2);
        $containerParser->expects($matcher)
            ->method('parseContainer')
            ->willReturnCallback(
                static function (AbstractContainer | string | null $containerInput) use ($matcher, $container, $name): AbstractContainer {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($name, $containerInput),
                        default => self::assertSame($container, $containerInput),
                    };

                    return $container;
                },
            );

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::never())
            ->method('__invoke');

        $partial   = 'testPartial';
        $expected  = 'renderedPartial';
        $seperator = '/';

        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::once())
            ->method('render')
            ->with($partial, ['pages' => [], 'separator' => $seperator, 'abc' => 'test'])
            ->willReturn($expected);
        $renderer->expects(self::never())
            ->method('plugin');
        $renderer->expects(self::never())
            ->method('getHelperPluginManager');

        $helper = new Breadcrumbs($serviceLocator, $containerParser, $htmlify, $renderer, $escapeHtml);

        $helper->setRole($role);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        $helper->setSeparator($seperator);
        $helper->setLinkLast(true);
        $helper->setPartial($partial);

        self::assertSame($expected, $helper->renderPartialWithParams(['abc' => 'test'], $name));
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     */
    public function testRenderPartialWithoutPartial(): void
    {
        $name = 'Mezzio\Navigation\Top';

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::never())
            ->method('__invoke');

        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::never())
            ->method('render');
        $renderer->expects(self::never())
            ->method('plugin');
        $renderer->expects(self::never())
            ->method('getHelperPluginManager');

        $helper = new Breadcrumbs($serviceLocator, $containerParser, $htmlify, $renderer, $escapeHtml);

        $role = 'testRole';

        $helper->setRole($role);

        $auth = $this->getMockBuilder(Acl::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isAllowed');

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        $helper->setSeparator('/');
        $helper->setLinkLast(true);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to render breadcrumbs: No partial view script provided');
        $this->expectExceptionCode(0);

        $helper->renderPartial($name);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     */
    public function testRenderPartialWithWrongPartial(): void
    {
        $name = 'Mezzio\Navigation\Top';

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::never())
            ->method('__invoke');

        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::never())
            ->method('render');
        $renderer->expects(self::never())
            ->method('plugin');
        $renderer->expects(self::never())
            ->method('getHelperPluginManager');

        $helper = new Breadcrumbs($serviceLocator, $containerParser, $htmlify, $renderer, $escapeHtml);

        $role = 'testRole';

        $helper->setRole($role);

        $auth = $this->getMockBuilder(Acl::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isAllowed');

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        $helper->setSeparator('/');
        $helper->setLinkLast(true);

        $helper->setPartial(['a', 'b', 'c']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Unable to render breadcrumbs: A view partial supplied as an array must contain one value: the partial view script',
        );
        $this->expectExceptionCode(0);

        $helper->renderPartial($name);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     */
    public function testRenderPartial(): void
    {
        $name = 'Mezzio\Navigation\Top';

        $resource  = 'testResource';
        $privilege = 'testPrivilege';

        $parentPage = new Uri();
        $parentPage->setVisible(true);
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);

        $page = $this->getMockBuilder(AbstractPage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::never())
            ->method('isVisible');
        $page->expects(self::never())
            ->method('getResource');
        $page->expects(self::never())
            ->method('getPrivilege');
        $page->expects(self::once())
            ->method('getParent')
            ->willReturn($parentPage);
        $page->expects(self::never())
            ->method('isActive');
        $page->expects(self::never())
            ->method('getLabel');
        $page->expects(self::never())
            ->method('getTextDomain');
        $page->expects(self::never())
            ->method('getTitle');
        $page->expects(self::never())
            ->method('getId');
        $page->expects(self::never())
            ->method('getClass');
        $page->expects(self::never())
            ->method('getHref');
        $page->expects(self::never())
            ->method('getTarget');
        $page->expects(self::never())
            ->method('hasPage');
        $page->expects(self::never())
            ->method('hasPages');
        $page->expects(self::never())
            ->method('getCustomProperties');
        $page->expects(self::never())
            ->method('get');

        $parentPage->addPage($page);

        $container = new Navigation();
        $container->addPage($parentPage);

        $role = 'testRole';

        $findActiveHelper = $this->getMockBuilder(FindActiveInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, 1, null)
            ->willReturn(
                [
                    'page' => $page,
                    'depth' => 2,
                ],
            );

        $auth = $this->getMockBuilder(Acl::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isAllowed');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::once())
            ->method('build')
            ->with(
                FindActiveInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ],
            )
            ->willReturn($findActiveHelper);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $matcher         = self::exactly(2);
        $containerParser->expects($matcher)
            ->method('parseContainer')
            ->willReturnCallback(
                static function (AbstractContainer | string | null $containerInput) use ($matcher, $container, $name): AbstractContainer {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($name, $containerInput),
                        default => self::assertSame($container, $containerInput),
                    };

                    return $container;
                },
            );

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::never())
            ->method('__invoke');

        $partial   = 'testPartial';
        $expected  = 'renderedPartial';
        $seperator = '/';

        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::once())
            ->method('render')
            ->with($partial, ['pages' => [$parentPage, $page], 'separator' => $seperator])
            ->willReturn($expected);
        $renderer->expects(self::never())
            ->method('plugin');
        $renderer->expects(self::never())
            ->method('getHelperPluginManager');

        $helper = new Breadcrumbs($serviceLocator, $containerParser, $htmlify, $renderer, $escapeHtml);

        $helper->setRole($role);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        $helper->setSeparator($seperator);
        $helper->setLinkLast(true);
        $helper->setPartial($partial);

        self::assertSame($expected, $helper->renderPartial($name));
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     */
    public function testRenderPartialNoActivePage(): void
    {
        $name = 'Mezzio\Navigation\Top';

        $page = $this->getMockBuilder(AbstractPage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::never())
            ->method('isVisible');
        $page->expects(self::never())
            ->method('getResource');
        $page->expects(self::never())
            ->method('getPrivilege');
        $page->expects(self::never())
            ->method('getParent');
        $page->expects(self::never())
            ->method('isActive');
        $page->expects(self::never())
            ->method('getLabel');
        $page->expects(self::never())
            ->method('getTextDomain');
        $page->expects(self::never())
            ->method('getTitle');
        $page->expects(self::never())
            ->method('getId');
        $page->expects(self::never())
            ->method('getClass');
        $page->expects(self::never())
            ->method('getHref');
        $page->expects(self::never())
            ->method('getTarget');
        $page->expects(self::never())
            ->method('hasPage');
        $page->expects(self::never())
            ->method('hasPages');
        $page->expects(self::never())
            ->method('getCustomProperties');
        $page->expects(self::never())
            ->method('get');

        $container = new Navigation();
        $container->addPage($page);

        $role = 'testRole';

        $findActiveHelper = $this->getMockBuilder(FindActiveInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, 1, null)
            ->willReturn([]);

        $auth = $this->getMockBuilder(Acl::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isAllowed');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::once())
            ->method('build')
            ->with(
                FindActiveInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ],
            )
            ->willReturn($findActiveHelper);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $matcher         = self::exactly(2);
        $containerParser->expects($matcher)
            ->method('parseContainer')
            ->willReturnCallback(
                static function (AbstractContainer | string | null $containerInput) use ($matcher, $container, $name): AbstractContainer {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($name, $containerInput),
                        default => self::assertSame($container, $containerInput),
                    };

                    return $container;
                },
            );

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::never())
            ->method('__invoke');

        $partial   = 'testPartial';
        $expected  = 'renderedPartial';
        $seperator = '/';

        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::once())
            ->method('render')
            ->with($partial, ['pages' => [], 'separator' => $seperator])
            ->willReturn($expected);
        $renderer->expects(self::never())
            ->method('plugin');
        $renderer->expects(self::never())
            ->method('getHelperPluginManager');

        $helper = new Breadcrumbs($serviceLocator, $containerParser, $htmlify, $renderer, $escapeHtml);

        $helper->setRole($role);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        $helper->setSeparator($seperator);
        $helper->setLinkLast(true);
        $helper->setPartial($partial);

        self::assertSame($expected, $helper->renderPartial($name));
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     */
    public function testRenderPartialWithArrayPartial(): void
    {
        $resource  = 'testResource';
        $privilege = 'testPrivilege';

        $parentPage = new Uri();
        $parentPage->setVisible(true);
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);

        $page = $this->getMockBuilder(AbstractPage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::never())
            ->method('isVisible');
        $page->expects(self::never())
            ->method('getResource');
        $page->expects(self::never())
            ->method('getPrivilege');
        $page->expects(self::once())
            ->method('getParent')
            ->willReturn($parentPage);
        $page->expects(self::never())
            ->method('isActive');
        $page->expects(self::never())
            ->method('getLabel');
        $page->expects(self::never())
            ->method('getTextDomain');
        $page->expects(self::never())
            ->method('getTitle');
        $page->expects(self::never())
            ->method('getId');
        $page->expects(self::never())
            ->method('getClass');
        $page->expects(self::never())
            ->method('getHref');
        $page->expects(self::never())
            ->method('getTarget');
        $page->expects(self::never())
            ->method('hasPage');
        $page->expects(self::never())
            ->method('hasPages');
        $page->expects(self::never())
            ->method('getCustomProperties');
        $page->expects(self::never())
            ->method('get');

        $parentPage->addPage($page);

        $container = new Navigation();
        $container->addPage($parentPage);

        $role = 'testRole';

        $findActiveHelper = $this->getMockBuilder(FindActiveInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, 1, null)
            ->willReturn(
                [
                    'page' => $page,
                    'depth' => 2,
                ],
            );

        $auth = $this->getMockBuilder(Acl::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isAllowed');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::once())
            ->method('build')
            ->with(
                FindActiveInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ],
            )
            ->willReturn($findActiveHelper);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $matcher         = self::exactly(3);
        $containerParser->expects($matcher)
            ->method('parseContainer')
            ->willReturnCallback(
                static function (AbstractContainer | string | null $containerInput) use ($matcher, $container): AbstractContainer | null {
                    match ($matcher->numberOfInvocations()) {
                        2 => self::assertNull($containerInput),
                        default => self::assertSame($container, $containerInput),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        2 => null,
                        default => $container,
                    };
                },
            );

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::never())
            ->method('__invoke');

        $partial   = 'testPartial';
        $expected  = 'renderedPartial';
        $seperator = '/';

        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::once())
            ->method('render')
            ->with($partial, ['pages' => [$parentPage, $page], 'separator' => $seperator])
            ->willReturn($expected);
        $renderer->expects(self::never())
            ->method('plugin');
        $renderer->expects(self::never())
            ->method('getHelperPluginManager');

        $helper = new Breadcrumbs($serviceLocator, $containerParser, $htmlify, $renderer, $escapeHtml);

        $helper->setRole($role);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        $helper->setSeparator($seperator);
        $helper->setLinkLast(true);
        $helper->setContainer($container);

        self::assertSame($expected, $helper->renderPartial(null, [$partial, 'test']));
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     */
    public function testRenderPartialWithArrayPartialRenderingPage(): void
    {
        $resource  = 'testResource';
        $privilege = 'testPrivilege';

        $parentPage = new Uri();
        $parentPage->setVisible(true);
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);
        $parentPage->setActive(true);

        $page = new Uri();
        $page->setVisible(true);
        $page->setResource($resource);
        $page->setPrivilege($privilege);
        $page->setActive(true);

        $subPage = $this->getMockBuilder(AbstractPage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $subPage->expects(self::never())
            ->method('isVisible');
        $subPage->expects(self::never())
            ->method('getResource');
        $subPage->expects(self::never())
            ->method('getPrivilege');
        $subPage->expects(self::once())
            ->method('getParent')
            ->willReturn($page);
        $subPage->expects(self::never())
            ->method('isActive');
        $subPage->expects(self::never())
            ->method('getLabel');
        $subPage->expects(self::never())
            ->method('getTextDomain');
        $subPage->expects(self::never())
            ->method('getTitle');
        $subPage->expects(self::never())
            ->method('getId');
        $subPage->expects(self::never())
            ->method('getClass');
        $subPage->expects(self::never())
            ->method('getHref');
        $subPage->expects(self::never())
            ->method('getTarget');
        $subPage->expects(self::never())
            ->method('hasPage');
        $subPage->expects(self::never())
            ->method('hasPages');
        $subPage->expects(self::never())
            ->method('getCustomProperties');
        $subPage->expects(self::never())
            ->method('get');

        $page->addPage($subPage);
        $parentPage->addPage($page);

        $role = 'testRole';

        $findActiveHelper = $this->getMockBuilder(FindActiveInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($parentPage, 1, null)
            ->willReturn(
                [
                    'page' => $subPage,
                    'depth' => 2,
                ],
            );

        $auth = $this->getMockBuilder(Acl::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isAllowed');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::once())
            ->method('build')
            ->with(
                FindActiveInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ],
            )
            ->willReturn($findActiveHelper);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $matcher         = self::exactly(3);
        $containerParser->expects($matcher)
            ->method('parseContainer')
            ->willReturnCallback(
                static function (AbstractContainer | string | null $containerInput) use ($matcher, $parentPage): AbstractContainer | null {
                    match ($matcher->numberOfInvocations()) {
                        2 => self::assertNull($containerInput),
                        default => self::assertSame($parentPage, $containerInput),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        2 => null,
                        default => $parentPage,
                    };
                },
            );

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::never())
            ->method('__invoke');

        $expected  = 'renderedPartial';
        $partial   = 'testPartial';
        $seperator = '/';

        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::once())
            ->method('render')
            ->with($partial, ['pages' => [$parentPage, $page, $subPage], 'separator' => $seperator])
            ->willReturn($expected);
        $renderer->expects(self::never())
            ->method('plugin');
        $renderer->expects(self::never())
            ->method('getHelperPluginManager');

        $helper = new Breadcrumbs($serviceLocator, $containerParser, $htmlify, $renderer, $escapeHtml);

        $helper->setRole($role);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        $helper->setSeparator($seperator);
        $helper->setLinkLast(true);
        $helper->setContainer($parentPage);

        self::assertSame($expected, $helper->renderPartial(null, [$partial, 'test']));
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     * @throws ContainerExceptionInterface
     * @throws \Laminas\I18n\Exception\RuntimeException
     */
    public function testRenderStraightNoActivePage(): void
    {
        $name = 'Mezzio\Navigation\Top';

        $page = $this->getMockBuilder(AbstractPage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::never())
            ->method('isVisible');
        $page->expects(self::never())
            ->method('getResource');
        $page->expects(self::never())
            ->method('getPrivilege');
        $page->expects(self::never())
            ->method('getParent');
        $page->expects(self::never())
            ->method('isActive');
        $page->expects(self::never())
            ->method('getLabel');
        $page->expects(self::never())
            ->method('getTextDomain');
        $page->expects(self::never())
            ->method('getTitle');
        $page->expects(self::never())
            ->method('getId');
        $page->expects(self::never())
            ->method('getClass');
        $page->expects(self::never())
            ->method('getHref');
        $page->expects(self::never())
            ->method('getTarget');
        $page->expects(self::never())
            ->method('hasPage');
        $page->expects(self::never())
            ->method('hasPages');
        $page->expects(self::never())
            ->method('getCustomProperties');
        $page->expects(self::never())
            ->method('get');

        $container = new Navigation();
        $container->addPage($page);

        $role = 'testRole';

        $findActiveHelper = $this->getMockBuilder(FindActiveInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, 1, null)
            ->willReturn([]);

        $auth = $this->getMockBuilder(Acl::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isAllowed');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::once())
            ->method('build')
            ->with(
                FindActiveInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ],
            )
            ->willReturn($findActiveHelper);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $matcher         = self::exactly(2);
        $containerParser->expects($matcher)
            ->method('parseContainer')
            ->willReturnCallback(
                static function (AbstractContainer | string | null $containerInput) use ($matcher, $container, $name): AbstractContainer {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($name, $containerInput),
                        default => self::assertSame($container, $containerInput),
                    };

                    return $container;
                },
            );

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::never())
            ->method('__invoke');

        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::never())
            ->method('render');
        $renderer->expects(self::never())
            ->method('plugin');
        $renderer->expects(self::never())
            ->method('getHelperPluginManager');

        $helper = new Breadcrumbs($serviceLocator, $containerParser, $htmlify, $renderer, $escapeHtml);

        $helper->setRole($role);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        $expected  = '';
        $partial   = 'testPartial';
        $seperator = '/';

        $helper->setSeparator($seperator);
        $helper->setLinkLast(true);
        $helper->setPartial($partial);

        self::assertSame($expected, $helper->renderStraight($name));
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     * @throws ContainerExceptionInterface
     * @throws \Laminas\I18n\Exception\RuntimeException
     */
    public function testRenderStraight(): void
    {
        $name = 'Mezzio\Navigation\Top';

        $resource  = 'testResource';
        $privilege = 'testPrivilege';

        $parentPage = new Uri();
        $parentPage->setVisible(true);
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);
        $parentPage->setId('parent-id');
        $parentPage->setClass('parent-class');
        $parentPage->setUri('##');
        $parentPage->setTarget('self');
        $parentPage->setLabel('parent-label');
        $parentPage->setTitle('parent-title');
        $parentPage->setTextDomain('parent-text-domain');

        $page = $this->getMockBuilder(AbstractPage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::never())
            ->method('isVisible');
        $page->expects(self::never())
            ->method('getResource');
        $page->expects(self::never())
            ->method('getPrivilege');
        $page->expects(self::once())
            ->method('getParent')
            ->willReturn($parentPage);
        $page->expects(self::once())
            ->method('isActive')
            ->with(false)
            ->willReturn(true);
        $page->expects(self::never())
            ->method('getLabel');
        $page->expects(self::never())
            ->method('getTextDomain');
        $page->expects(self::never())
            ->method('getTitle');
        $page->expects(self::never())
            ->method('getId');
        $page->expects(self::never())
            ->method('getClass');
        $page->expects(self::never())
            ->method('getHref');
        $page->expects(self::never())
            ->method('getTarget');
        $page->expects(self::never())
            ->method('hasPage');
        $page->expects(self::never())
            ->method('hasPages');
        $page->expects(self::once())
            ->method('getCustomProperties')
            ->willReturn([]);
        $page->expects(self::never())
            ->method('get');

        $parentPage->addPage($page);

        $container = new Navigation();
        $container->addPage($parentPage);

        $role = 'testRole';

        $findActiveHelper = $this->getMockBuilder(FindActiveInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, 1, null)
            ->willReturn(
                [
                    'page' => $page,
                    'depth' => 1,
                ],
            );

        $auth = $this->getMockBuilder(Acl::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isAllowed');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::once())
            ->method('build')
            ->with(
                FindActiveInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ],
            )
            ->willReturn($findActiveHelper);

        $expected1 = '<a parent-id-escaped="parent-id-escaped" parent-title-escaped="parent-title-escaped" parent-class-escaped="parent-class-escaped" parent-href-escaped="##-escaped" parent-target-escaped="self-escaped">parent-label-escaped</a>';
        $expected2 = '<a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>';

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $matcher = self::exactly(2);
        $htmlify->expects($matcher)
            ->method('toHtml')
            ->willReturnCallback(
                static function (string $prefix, AbstractPage $pageInput) use ($matcher, $page, $parentPage, $expected2, $expected1): string {
                    self::assertSame(Breadcrumbs::class, $prefix);

                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($page, $pageInput),
                        default => self::assertSame($parentPage, $pageInput),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        1 => $expected2,
                        default => $expected1,
                    };
                },
            );

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $matcher         = self::exactly(2);
        $containerParser->expects($matcher)
            ->method('parseContainer')
            ->willReturnCallback(
                static function (AbstractContainer | string | null $containerInput) use ($matcher, $container, $name): AbstractContainer {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($name, $containerInput),
                        default => self::assertSame($container, $containerInput),
                    };

                    return $container;
                },
            );

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::never())
            ->method('__invoke');

        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::never())
            ->method('render');
        $renderer->expects(self::never())
            ->method('plugin');
        $renderer->expects(self::never())
            ->method('getHelperPluginManager');

        $helper = new Breadcrumbs($serviceLocator, $containerParser, $htmlify, $renderer, $escapeHtml);

        $helper->setRole($role);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        $expected  = '<nav aria-label="breadcrumb">'
            . PHP_EOL . '    <ul class="breadcrumb">'
            . PHP_EOL . '        <li class="breadcrumb-item">'
            . PHP_EOL . '            <a parent-id-escaped="parent-id-escaped" parent-title-escaped="parent-title-escaped" parent-class-escaped="parent-class-escaped" parent-href-escaped="##-escaped" parent-target-escaped="self-escaped">parent-label-escaped</a>'
            . PHP_EOL . '        </li>'
            . PHP_EOL . '        /'
            . PHP_EOL . '        <li class="breadcrumb-item active" aria-current="page">'
            . PHP_EOL . '            <a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>'
            . PHP_EOL . '        </li>'
            . PHP_EOL . '    </ul>'
            . PHP_EOL . '</nav>'
            . PHP_EOL;
        $seperator = '/';

        $helper->setSeparator($seperator);
        $helper->setLinkLast(true);

        self::assertSame($expected, $helper->renderStraight($name));
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     * @throws ContainerExceptionInterface
     * @throws \Laminas\I18n\Exception\RuntimeException
     */
    public function testRenderStraightWithoutLinkAtEnd(): void
    {
        $resource               = 'testResource';
        $privilege              = 'testPrivilege';
        $label                  = 'testLabel';
        $tranalatedLabel        = 'testLabelTranslated';
        $escapedTranalatedLabel = 'testLabelTranslatedAndEscaped';
        $textDomain             = 'testDomain';

        $parentPage = new Uri();
        $parentPage->setVisible(true);
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);
        $parentPage->setId('parent-id');
        $parentPage->setClass('parent-class');
        $parentPage->setUri('##');
        $parentPage->setTarget('self');
        $parentPage->setLabel('parent-label');
        $parentPage->setTitle('parent-title');
        $parentPage->setTextDomain('parent-text-domain');

        $page = $this->getMockBuilder(AbstractPage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::never())
            ->method('isVisible');
        $page->expects(self::never())
            ->method('getResource');
        $page->expects(self::never())
            ->method('getPrivilege');
        $page->expects(self::once())
            ->method('getParent')
            ->willReturn($parentPage);
        $page->expects(self::once())
            ->method('isActive')
            ->with(false)
            ->willReturn(false);
        $page->expects(self::once())
            ->method('getLabel')
            ->willReturn($label);
        $page->expects(self::once())
            ->method('getTextDomain')
            ->willReturn($textDomain);
        $page->expects(self::never())
            ->method('getTitle');
        $page->expects(self::never())
            ->method('getId');
        $page->expects(self::never())
            ->method('getClass');
        $page->expects(self::never())
            ->method('getHref');
        $page->expects(self::never())
            ->method('getTarget');
        $page->expects(self::never())
            ->method('hasPage');
        $page->expects(self::never())
            ->method('hasPages');
        $page->expects(self::once())
            ->method('getCustomProperties')
            ->willReturn([]);
        $page->expects(self::never())
            ->method('get');

        $parentPage->addPage($page);

        $container = new Navigation();
        $container->addPage($parentPage);

        $role = 'testRole';

        $findActiveHelper = $this->getMockBuilder(FindActiveInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, 1, null)
            ->willReturn(
                [
                    'page' => $page,
                    'depth' => 1,
                ],
            );

        $auth = $this->getMockBuilder(Acl::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isAllowed');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::once())
            ->method('build')
            ->with(
                FindActiveInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ],
            )
            ->willReturn($findActiveHelper);

        $expected1 = '<a parent-id-escaped="parent-id-escaped" parent-title-escaped="parent-title-escaped" parent-class-escaped="parent-class-escaped" parent-href-escaped="##-escaped" parent-target-escaped="self-escaped">parent-label-escaped</a>';

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::once())
            ->method('toHtml')
            ->with(Breadcrumbs::class, $parentPage)
            ->willReturn($expected1);

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $matcher         = self::exactly(3);
        $containerParser->expects($matcher)
            ->method('parseContainer')
            ->willReturnCallback(
                static function (AbstractContainer | string | null $containerInput) use ($matcher, $container): AbstractContainer | null {
                    match ($matcher->numberOfInvocations()) {
                        2 => self::assertNull($containerInput),
                        default => self::assertSame($container, $containerInput),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        2 => null,
                        default => $container,
                    };
                },
            );

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::once())
            ->method('__invoke')
            ->with($tranalatedLabel)
            ->willReturn($escapedTranalatedLabel);

        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::never())
            ->method('render');
        $renderer->expects(self::never())
            ->method('plugin');
        $renderer->expects(self::never())
            ->method('getHelperPluginManager');

        $translator = $this->getMockBuilder(Translator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $translator->expects(self::once())
            ->method('translate')
            ->with($label, $textDomain)
            ->willReturn($tranalatedLabel);

        $helper = new Breadcrumbs($serviceLocator, $containerParser, $htmlify, $renderer, $escapeHtml);

        $helper->setRole($role);
        $helper->setContainer($container);
        $helper->setTranslator($translator);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        $expected  = '<nav aria-label="breadcrumb">'
            . PHP_EOL . '    <ul class="breadcrumb">'
            . PHP_EOL . '        <li class="breadcrumb-item">'
            . PHP_EOL . '            <a parent-id-escaped="parent-id-escaped" parent-title-escaped="parent-title-escaped" parent-class-escaped="parent-class-escaped" parent-href-escaped="##-escaped" parent-target-escaped="self-escaped">parent-label-escaped</a>'
            . PHP_EOL . '        </li>'
            . PHP_EOL . '        /'
            . PHP_EOL . '        <li class="breadcrumb-item">'
            . PHP_EOL . '            testLabelTranslatedAndEscaped'
            . PHP_EOL . '        </li>'
            . PHP_EOL . '    </ul>'
            . PHP_EOL . '</nav>'
            . PHP_EOL;
        $seperator = '/';

        $helper->setSeparator($seperator);
        $helper->setLinkLast(false);

        self::assertSame($expected, $helper->renderStraight());
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     * @throws ContainerExceptionInterface
     * @throws \Laminas\I18n\Exception\RuntimeException
     */
    public function testRenderStraightWithoutLinkAtEndWithLiClass(): void
    {
        $resource               = 'testResource';
        $privilege              = 'testPrivilege';
        $label                  = 'testLabel';
        $tranalatedLabel        = 'testLabelTranslated';
        $escapedTranalatedLabel = 'testLabelTranslatedAndEscaped';
        $textDomain             = 'testDomain';

        $parentPage = new Uri();
        $parentPage->setVisible(true);
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);
        $parentPage->setId('parent-id');
        $parentPage->setClass('parent-class');
        $parentPage->setUri('##');
        $parentPage->setTarget('self');
        $parentPage->setLabel('parent-label');
        $parentPage->setTitle('parent-title');
        $parentPage->setTextDomain('parent-text-domain');

        $page = $this->getMockBuilder(AbstractPage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::never())
            ->method('isVisible');
        $page->expects(self::never())
            ->method('getResource');
        $page->expects(self::never())
            ->method('getPrivilege');
        $page->expects(self::once())
            ->method('getParent')
            ->willReturn($parentPage);
        $page->expects(self::once())
            ->method('isActive')
            ->with(false)
            ->willReturn(false);
        $page->expects(self::once())
            ->method('getLabel')
            ->willReturn($label);
        $page->expects(self::once())
            ->method('getTextDomain')
            ->willReturn($textDomain);
        $page->expects(self::never())
            ->method('getTitle');
        $page->expects(self::never())
            ->method('getId');
        $page->expects(self::never())
            ->method('getClass');
        $page->expects(self::never())
            ->method('getHref');
        $page->expects(self::never())
            ->method('getTarget');
        $page->expects(self::never())
            ->method('hasPage');
        $page->expects(self::never())
            ->method('hasPages');
        $page->expects(self::once())
            ->method('getCustomProperties')
            ->willReturn(
                ['liClass' => 'li-class'],
            );
        $page->expects(self::never())
            ->method('get');

        $parentPage->addPage($page);

        $container = new Navigation();
        $container->addPage($parentPage);

        $role = 'testRole';

        $findActiveHelper = $this->getMockBuilder(FindActiveInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, 1, null)
            ->willReturn(
                [
                    'page' => $page,
                    'depth' => 1,
                ],
            );

        $auth = $this->getMockBuilder(Acl::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isAllowed');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::once())
            ->method('build')
            ->with(
                FindActiveInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ],
            )
            ->willReturn($findActiveHelper);

        $expected1 = '<a parent-id-escaped="parent-id-escaped" parent-title-escaped="parent-title-escaped" parent-class-escaped="parent-class-escaped" parent-href-escaped="##-escaped" parent-target-escaped="self-escaped">parent-label-escaped</a>';

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::once())
            ->method('toHtml')
            ->with(Breadcrumbs::class, $parentPage)
            ->willReturn($expected1);

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $matcher         = self::exactly(3);
        $containerParser->expects($matcher)
            ->method('parseContainer')
            ->willReturnCallback(
                static function (AbstractContainer | string | null $containerInput) use ($matcher, $container): AbstractContainer | null {
                    match ($matcher->numberOfInvocations()) {
                        2 => self::assertNull($containerInput),
                        default => self::assertSame($container, $containerInput),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        2 => null,
                        default => $container,
                    };
                },
            );

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::once())
            ->method('__invoke')
            ->with($tranalatedLabel)
            ->willReturn($escapedTranalatedLabel);

        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::never())
            ->method('render');
        $renderer->expects(self::never())
            ->method('plugin');
        $renderer->expects(self::never())
            ->method('getHelperPluginManager');

        $translator = $this->getMockBuilder(Translator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $translator->expects(self::once())
            ->method('translate')
            ->with($label, $textDomain)
            ->willReturn($tranalatedLabel);

        $helper = new Breadcrumbs($serviceLocator, $containerParser, $htmlify, $renderer, $escapeHtml);

        $helper->setRole($role);
        $helper->setContainer($container);
        $helper->setTranslator($translator);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        $expected  = '<nav aria-label="breadcrumb">'
            . PHP_EOL . '    <ul class="breadcrumb">'
            . PHP_EOL . '        <li class="breadcrumb-item">'
            . PHP_EOL . '            <a parent-id-escaped="parent-id-escaped" parent-title-escaped="parent-title-escaped" parent-class-escaped="parent-class-escaped" parent-href-escaped="##-escaped" parent-target-escaped="self-escaped">parent-label-escaped</a>'
            . PHP_EOL . '        </li>'
            . PHP_EOL . '        /'
            . PHP_EOL . '        <li class="breadcrumb-item li-class">'
            . PHP_EOL . '            testLabelTranslatedAndEscaped'
            . PHP_EOL . '        </li>'
            . PHP_EOL . '    </ul>'
            . PHP_EOL . '</nav>'
            . PHP_EOL;
        $seperator = '/';

        $helper->setSeparator($seperator);
        $helper->setLinkLast(false);

        self::assertSame($expected, $helper->renderStraight());
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     * @throws ContainerExceptionInterface
     * @throws \Laminas\I18n\Exception\RuntimeException
     */
    public function testRenderStraightWithoutLinkAtEndWithLiClass2(): void
    {
        $indent = '    ';

        $resource               = 'testResource';
        $privilege              = 'testPrivilege';
        $label                  = 'testLabel';
        $tranalatedLabel        = 'testLabelTranslated';
        $escapedTranalatedLabel = 'testLabelTranslatedAndEscaped';
        $textDomain             = 'testDomain';

        $parentPage = new Uri();
        $parentPage->setVisible(true);
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);
        $parentPage->setId('parent-id');
        $parentPage->setClass('parent-class');
        $parentPage->setUri('##');
        $parentPage->setTarget('self');
        $parentPage->setLabel('parent-label');
        $parentPage->setTitle('parent-title');
        $parentPage->setTextDomain('parent-text-domain');

        $page = $this->getMockBuilder(AbstractPage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::never())
            ->method('isVisible');
        $page->expects(self::never())
            ->method('getResource');
        $page->expects(self::never())
            ->method('getPrivilege');
        $page->expects(self::once())
            ->method('getParent')
            ->willReturn($parentPage);
        $page->expects(self::once())
            ->method('isActive')
            ->with(false)
            ->willReturn(false);
        $page->expects(self::once())
            ->method('getLabel')
            ->willReturn($label);
        $page->expects(self::once())
            ->method('getTextDomain')
            ->willReturn($textDomain);
        $page->expects(self::never())
            ->method('getTitle');
        $page->expects(self::never())
            ->method('getId');
        $page->expects(self::never())
            ->method('getClass');
        $page->expects(self::never())
            ->method('getHref');
        $page->expects(self::never())
            ->method('getTarget');
        $page->expects(self::never())
            ->method('hasPage');
        $page->expects(self::never())
            ->method('hasPages');
        $page->expects(self::once())
            ->method('getCustomProperties')
            ->willReturn(
                ['liClass' => 'li-class'],
            );
        $page->expects(self::never())
            ->method('get');

        $parentPage->addPage($page);

        $container = new Navigation();
        $container->addPage($parentPage);

        $role = 'testRole';

        $findActiveHelper = $this->getMockBuilder(FindActiveInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, 1, null)
            ->willReturn(
                [
                    'page' => $page,
                    'depth' => 1,
                ],
            );

        $auth = $this->getMockBuilder(Acl::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isAllowed');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::once())
            ->method('build')
            ->with(
                FindActiveInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ],
            )
            ->willReturn($findActiveHelper);

        $expected1 = '<a parent-id-escaped="parent-id-escaped" parent-title-escaped="parent-title-escaped" parent-class-escaped="parent-class-escaped" parent-href-escaped="##-escaped" parent-target-escaped="self-escaped">parent-label-escaped</a>';

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::once())
            ->method('toHtml')
            ->with(Breadcrumbs::class, $parentPage)
            ->willReturn($expected1);

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $matcher         = self::exactly(3);
        $containerParser->expects($matcher)
            ->method('parseContainer')
            ->willReturnCallback(
                static function (AbstractContainer | string | null $containerInput) use ($matcher, $container): AbstractContainer | null {
                    match ($matcher->numberOfInvocations()) {
                        2 => self::assertNull($containerInput),
                        default => self::assertSame($container, $containerInput),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        2 => null,
                        default => $container,
                    };
                },
            );

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::once())
            ->method('__invoke')
            ->with($tranalatedLabel)
            ->willReturn($escapedTranalatedLabel);

        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::never())
            ->method('render');
        $renderer->expects(self::never())
            ->method('plugin');
        $renderer->expects(self::never())
            ->method('getHelperPluginManager');

        $translator = $this->getMockBuilder(Translator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $translator->expects(self::once())
            ->method('translate')
            ->with($label, $textDomain)
            ->willReturn($tranalatedLabel);

        $helper = new Breadcrumbs($serviceLocator, $containerParser, $htmlify, $renderer, $escapeHtml);

        $helper->setRole($role);
        $helper->setContainer($container);
        $helper->setTranslator($translator);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        $seperator = '/';

        $expected = $indent . '<nav aria-label="breadcrumb">' . PHP_EOL
            . $indent . $indent . '<ul class="breadcrumb">' . PHP_EOL
            . $indent . $indent . $indent . '<li class="breadcrumb-item">' . PHP_EOL
            . $indent . $indent . $indent . $indent . '<a parent-id-escaped="parent-id-escaped" parent-title-escaped="parent-title-escaped" parent-class-escaped="parent-class-escaped" parent-href-escaped="##-escaped" parent-target-escaped="self-escaped">parent-label-escaped</a>' . PHP_EOL
            . $indent . $indent . $indent . '</li>' . PHP_EOL
            . $indent . $indent . $indent . $seperator . PHP_EOL
            . $indent . $indent . $indent . '<li class="breadcrumb-item li-class">' . PHP_EOL
            . $indent . $indent . $indent . $indent . 'testLabelTranslatedAndEscaped' . PHP_EOL
            . $indent . $indent . $indent . '</li>' . PHP_EOL
            . $indent . $indent . '</ul>' . PHP_EOL
            . $indent . '</nav>' . PHP_EOL;

        $helper->setSeparator($seperator);
        $helper->setLinkLast(false);
        $helper->setIndent($indent);

        self::assertSame($expected, $helper->renderStraight());
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     */
    public function testRenderWithoutPartial(): void
    {
        $name      = 'Mezzio\Navigation\Top';
        $resource  = 'testResource';
        $privilege = 'testPrivilege';

        $parentPage = new Uri();
        $parentPage->setVisible(true);
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);
        $parentPage->setId('parent-id');
        $parentPage->setClass('parent-class');
        $parentPage->setUri('##');
        $parentPage->setTarget('self');
        $parentPage->setLabel('parent-label');
        $parentPage->setTitle('parent-title');
        $parentPage->setTextDomain('parent-text-domain');

        $page = $this->getMockBuilder(AbstractPage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::never())
            ->method('isVisible');
        $page->expects(self::never())
            ->method('getResource');
        $page->expects(self::never())
            ->method('getPrivilege');
        $page->expects(self::once())
            ->method('getParent')
            ->willReturn($parentPage);
        $page->expects(self::once())
            ->method('isActive')
            ->with(false)
            ->willReturn(false);
        $page->expects(self::never())
            ->method('getLabel');
        $page->expects(self::never())
            ->method('getTextDomain');
        $page->expects(self::never())
            ->method('getTitle');
        $page->expects(self::never())
            ->method('getId');
        $page->expects(self::never())
            ->method('getClass');
        $page->expects(self::never())
            ->method('getHref');
        $page->expects(self::never())
            ->method('getTarget');
        $page->expects(self::never())
            ->method('hasPage');
        $page->expects(self::never())
            ->method('hasPages');
        $page->expects(self::once())
            ->method('getCustomProperties')
            ->willReturn([]);
        $page->expects(self::never())
            ->method('get');

        $parentPage->addPage($page);

        $container = new Navigation();
        $container->addPage($parentPage);

        $role = 'testRole';

        $auth = $this->getMockBuilder(Acl::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isAllowed');

        $findActiveHelper = $this->getMockBuilder(FindActiveInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, 1, null)
            ->willReturn(
                [
                    'page' => $page,
                    'depth' => 1,
                ],
            );

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::once())
            ->method('build')
            ->with(
                FindActiveInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ],
            )
            ->willReturn($findActiveHelper);

        $expected1 = '<a parent-id-escaped="parent-id-escaped" parent-title-escaped="parent-title-escaped" parent-class-escaped="parent-class-escaped" parent-href-escaped="##-escaped" parent-target-escaped="self-escaped">parent-label-escaped</a>';
        $expected2 = '<a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>';

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $matcher = self::exactly(2);
        $htmlify->expects($matcher)
            ->method('toHtml')
            ->willReturnCallback(
                static function (string $prefix, AbstractPage $pageInput) use ($matcher, $page, $parentPage, $expected2, $expected1): string {
                    self::assertSame(Breadcrumbs::class, $prefix);

                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($page, $pageInput),
                        default => self::assertSame($parentPage, $pageInput),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        1 => $expected2,
                        default => $expected1,
                    };
                },
            );

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $matcher         = self::exactly(2);
        $containerParser->expects($matcher)
            ->method('parseContainer')
            ->willReturnCallback(
                static function (AbstractContainer | string | null $containerInput) use ($matcher, $container, $name): AbstractContainer {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($name, $containerInput),
                        default => self::assertSame($container, $containerInput),
                    };

                    return $container;
                },
            );

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::never())
            ->method('__invoke');

        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::never())
            ->method('render');
        $renderer->expects(self::never())
            ->method('plugin');
        $renderer->expects(self::never())
            ->method('getHelperPluginManager');

        $helper = new Breadcrumbs($serviceLocator, $containerParser, $htmlify, $renderer, $escapeHtml);

        $helper->setRole($role);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        $expected  = '<nav aria-label="breadcrumb">'
            . PHP_EOL . '    <ul class="breadcrumb">'
            . PHP_EOL . '        <li class="breadcrumb-item">'
            . PHP_EOL . '            <a parent-id-escaped="parent-id-escaped" parent-title-escaped="parent-title-escaped" parent-class-escaped="parent-class-escaped" parent-href-escaped="##-escaped" parent-target-escaped="self-escaped">parent-label-escaped</a>'
            . PHP_EOL . '        </li>'
            . PHP_EOL . '        /'
            . PHP_EOL . '        <li class="breadcrumb-item">'
            . PHP_EOL . '            <a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>'
            . PHP_EOL . '        </li>'
            . PHP_EOL . '    </ul>'
            . PHP_EOL . '</nav>'
            . PHP_EOL;
        $seperator = '/';

        $helper->setSeparator($seperator);
        $helper->setLinkLast(true);

        self::assertSame($expected, $helper->render($name));
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     */
    public function testRenderWithPartial(): void
    {
        $name = 'Mezzio\Navigation\Top';

        $page = $this->getMockBuilder(AbstractPage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::never())
            ->method('isVisible');
        $page->expects(self::never())
            ->method('getResource');
        $page->expects(self::never())
            ->method('getPrivilege');
        $page->expects(self::never())
            ->method('getParent');
        $page->expects(self::never())
            ->method('isActive');
        $page->expects(self::never())
            ->method('getLabel');
        $page->expects(self::never())
            ->method('getTextDomain');
        $page->expects(self::never())
            ->method('getTitle');
        $page->expects(self::never())
            ->method('getId');
        $page->expects(self::never())
            ->method('getClass');
        $page->expects(self::never())
            ->method('getHref');
        $page->expects(self::never())
            ->method('getTarget');
        $page->expects(self::never())
            ->method('hasPage');
        $page->expects(self::never())
            ->method('hasPages');
        $page->expects(self::never())
            ->method('getCustomProperties');
        $page->expects(self::never())
            ->method('get');

        $container = new Navigation();
        $container->addPage($page);

        $role = 'testRole';

        $auth = $this->getMockBuilder(Acl::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isAllowed');

        $findActiveHelper = $this->getMockBuilder(FindActiveInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, 1, null)
            ->willReturn([]);

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::once())
            ->method('build')
            ->with(
                FindActiveInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ],
            )
            ->willReturn($findActiveHelper);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $matcher         = self::exactly(2);
        $containerParser->expects($matcher)
            ->method('parseContainer')
            ->willReturnCallback(
                static function (AbstractContainer | string | null $containerInput) use ($matcher, $container, $name): AbstractContainer {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($name, $containerInput),
                        default => self::assertSame($container, $containerInput),
                    };

                    return $container;
                },
            );

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::never())
            ->method('__invoke');

        $expected  = 'renderedPartial';
        $partial   = 'testPartial';
        $seperator = '/';

        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::once())
            ->method('render')
            ->with($partial, ['pages' => [], 'separator' => $seperator])
            ->willReturn($expected);
        $renderer->expects(self::never())
            ->method('plugin');
        $renderer->expects(self::never())
            ->method('getHelperPluginManager');

        $helper = new Breadcrumbs($serviceLocator, $containerParser, $htmlify, $renderer, $escapeHtml);

        $helper->setRole($role);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        $helper->setSeparator($seperator);
        $helper->setLinkLast(true);
        $helper->setPartial($partial);

        self::assertSame($expected, $helper->render($name));
    }

    /**
     * @throws Exception
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     */
    public function testToStringWithPartial(): void
    {
        $auth = $this->getMockBuilder(Acl::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isAllowed');
        assert($auth instanceof Acl);

        $name = 'Mezzio\Navigation\Top';

        $page = $this->getMockBuilder(AbstractPage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::never())
            ->method('isVisible');
        $page->expects(self::never())
            ->method('getResource');
        $page->expects(self::never())
            ->method('getPrivilege');
        $page->expects(self::never())
            ->method('getParent');
        $page->expects(self::never())
            ->method('isActive');
        $page->expects(self::never())
            ->method('getLabel');
        $page->expects(self::never())
            ->method('getTextDomain');
        $page->expects(self::never())
            ->method('getTitle');
        $page->expects(self::never())
            ->method('getId');
        $page->expects(self::never())
            ->method('getClass');
        $page->expects(self::never())
            ->method('getHref');
        $page->expects(self::never())
            ->method('getTarget');
        $page->expects(self::never())
            ->method('hasPage');
        $page->expects(self::never())
            ->method('hasPages');
        $page->expects(self::never())
            ->method('getCustomProperties');
        $page->expects(self::never())
            ->method('get');

        $container = new Navigation();
        $container->addPage($page);

        $role = 'testRole';

        $findActiveHelper = $this->getMockBuilder(FindActiveInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, 1, null)
            ->willReturn([]);

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::once())
            ->method('build')
            ->with(
                FindActiveInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ],
            )
            ->willReturn($findActiveHelper);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $matcher         = self::exactly(3);
        $containerParser->expects($matcher)
            ->method('parseContainer')
            ->willReturnCallback(
                static function (AbstractContainer | string | null $containerInput) use ($matcher, $container, $name): AbstractContainer | null {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($name, $containerInput),
                        2 => self::assertNull($containerInput),
                        default => self::assertSame($container, $containerInput),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        2 => null,
                        default => $container,
                    };
                },
            );

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::never())
            ->method('__invoke');

        $expected  = 'renderedPartial';
        $partial   = 'testPartial';
        $seperator = '/';

        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::once())
            ->method('render')
            ->with($partial, ['pages' => [], 'separator' => $seperator])
            ->willReturn($expected);
        $renderer->expects(self::never())
            ->method('plugin');
        $renderer->expects(self::never())
            ->method('getHelperPluginManager');

        $helper = new Breadcrumbs($serviceLocator, $containerParser, $htmlify, $renderer, $escapeHtml);

        $helper->setRole($role);
        $helper->setAcl($auth);
        $helper->setSeparator($seperator);
        $helper->setLinkLast(true);
        $helper->setPartial($partial);

        self::assertSame($expected, (string) $helper($name));
    }

    /**
     * @throws Exception
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     */
    public function testInvoke(): void
    {
        $container = $this->createMock(AbstractContainer::class);

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($container)
            ->willReturn($container);

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::never())
            ->method('__invoke');

        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::never())
            ->method('render');

        $helper = new Breadcrumbs($serviceLocator, $containerParser, $htmlify, $renderer, $escapeHtml);

        $container1 = $helper->getContainer();

        self::assertInstanceOf(Navigation::class, $container1);

        $helper($container);

        self::assertSame($container, $helper->getContainer());
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     */
    public function testDoNotRenderIfNoPageIsActive(): void
    {
        $page = $this->getMockBuilder(AbstractPage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::never())
            ->method('isVisible');
        $page->expects(self::never())
            ->method('getResource');
        $page->expects(self::never())
            ->method('getPrivilege');
        $page->expects(self::never())
            ->method('getParent');
        $page->expects(self::never())
            ->method('isActive');
        $page->expects(self::never())
            ->method('getLabel');
        $page->expects(self::never())
            ->method('getTextDomain');
        $page->expects(self::never())
            ->method('getTitle');
        $page->expects(self::never())
            ->method('getId');
        $page->expects(self::never())
            ->method('getClass');
        $page->expects(self::never())
            ->method('getHref');
        $page->expects(self::never())
            ->method('getTarget');
        $page->expects(self::never())
            ->method('hasPage');
        $page->expects(self::never())
            ->method('hasPages');
        $page->expects(self::never())
            ->method('getCustomProperties');
        $page->expects(self::never())
            ->method('get');

        $container = new Navigation();
        $container->addPage($page);

        $findActiveHelper = $this->getMockBuilder(FindActiveInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, 1, null)
            ->willReturn([]);

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::once())
            ->method('build')
            ->with(
                FindActiveInterface::class,
                [
                    'authorization' => null,
                    'renderInvisible' => false,
                    'role' => null,
                ],
            )
            ->willReturn($findActiveHelper);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $matcher         = self::exactly(3);
        $containerParser->expects($matcher)
            ->method('parseContainer')
            ->willReturnCallback(
                static function (AbstractContainer | string | null $containerInput) use ($matcher, $container): AbstractContainer | null {
                    match ($matcher->numberOfInvocations()) {
                        2 => self::assertNull($containerInput),
                        default => self::assertSame($container, $containerInput),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        2 => null,
                        default => $container,
                    };
                },
            );

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::never())
            ->method('__invoke');

        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::never())
            ->method('render');

        $helper = new Breadcrumbs($serviceLocator, $containerParser, $htmlify, $renderer, $escapeHtml);

        $helper->setContainer($container);

        self::assertSame('', $helper->render());
    }
}
