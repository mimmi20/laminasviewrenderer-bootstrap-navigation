<?php
/**
 * This file is part of the mimmi20/mezzio-navigation-laminasviewrenderer-bootstrap package.
 *
 * Copyright (c) 2021, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mimmi20Test\LaminasView\BootstrapNavigation;

use Laminas\I18n\View\Helper\Translate;
use Laminas\Log\Logger;
use Laminas\Navigation\AbstractContainer;
use Laminas\Navigation\Navigation;
use Laminas\Navigation\Page\AbstractPage;
use Laminas\Navigation\Page\Uri;
use Laminas\Permissions\Acl\Acl;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\PluginManagerInterface;
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

use function assert;

use const PHP_EOL;

final class BreadcrumbsTest extends TestCase
{
    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    protected function tearDown(): void
    {
        Breadcrumbs::setDefaultAcl(null);
        Breadcrumbs::setDefaultRole(null);
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testSetMaxDepth(): void
    {
        $maxDepth = 4;

        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emerg');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('crit');
        $logger->expects(self::never())
            ->method('err');
        $logger->expects(self::never())
            ->method('warn');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

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



        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::never())
            ->method('render');

        $translatePlugin = $this->getMockBuilder(Translate::class)
            ->disableOriginalConstructor()
            ->getMock();
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs($serviceLocator, $logger, $htmlify, $containerParser, $renderer, $translatePlugin);

        self::assertNull($helper->getMaxDepth());

        $helper->setMaxDepth($maxDepth);

        self::assertSame($maxDepth, $helper->getMaxDepth());
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testSetMinDepth(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emerg');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('crit');
        $logger->expects(self::never())
            ->method('err');
        $logger->expects(self::never())
            ->method('warn');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

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



        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::never())
            ->method('render');

        $translatePlugin = $this->getMockBuilder(Translate::class)
            ->disableOriginalConstructor()
            ->getMock();
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs($serviceLocator, $logger, $htmlify, $containerParser, $renderer, $translatePlugin);

        self::assertSame(1, $helper->getMinDepth());

        $helper->setMinDepth(4);

        self::assertSame(4, $helper->getMinDepth());

        $helper->setMinDepth(-1);

        self::assertSame(1, $helper->getMinDepth());

        $helper->setMinDepth(0);

        self::assertSame(0, $helper->getMinDepth());

        $helper->setMinDepth(1);

        self::assertSame(1, $helper->getMinDepth());

        $helper->setMinDepth(4);

        self::assertSame(4, $helper->getMinDepth());
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testSetRenderInvisible(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emerg');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('crit');
        $logger->expects(self::never())
            ->method('err');
        $logger->expects(self::never())
            ->method('warn');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

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



        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::never())
            ->method('render');

        $translatePlugin = $this->getMockBuilder(Translate::class)
            ->disableOriginalConstructor()
            ->getMock();
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs($serviceLocator, $logger, $htmlify, $containerParser, $renderer, $translatePlugin);

        self::assertFalse($helper->getRenderInvisible());

        $helper->setRenderInvisible(true);

        self::assertTrue($helper->getRenderInvisible());
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testSetRole(): void
    {
        $role        = 'testRole';
        $defaultRole = 'testDefaultRole';

        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emerg');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('crit');
        $logger->expects(self::never())
            ->method('err');
        $logger->expects(self::never())
            ->method('warn');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

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



        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::never())
            ->method('render');

        $translatePlugin = $this->getMockBuilder(Translate::class)
            ->disableOriginalConstructor()
            ->getMock();
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs($serviceLocator, $logger, $htmlify, $containerParser, $renderer, $translatePlugin);

        self::assertNull($helper->getRole());
        self::assertFalse($helper->hasRole());

        Breadcrumbs::setDefaultRole($defaultRole);

        self::assertSame($defaultRole, $helper->getRole());
        self::assertTrue($helper->hasRole());

        $helper->setRole($role);

        self::assertSame($role, $helper->getRole());
        self::assertTrue($helper->hasRole());
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testSetUseAuthorization(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emerg');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('crit');
        $logger->expects(self::never())
            ->method('err');
        $logger->expects(self::never())
            ->method('warn');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

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



        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::never())
            ->method('render');

        $translatePlugin = $this->getMockBuilder(Translate::class)
            ->disableOriginalConstructor()
            ->getMock();
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs($serviceLocator, $logger, $htmlify, $containerParser, $renderer, $translatePlugin);

        self::assertTrue($helper->getUseAcl());

        $helper->setUseAcl(false);

        self::assertFalse($helper->getUseAcl());

        $helper->setUseAcl();

        self::assertTrue($helper->getUseAcl());
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testsetAcl(): void
    {
        $auth        = $this->createMock(Acl::class);
        $defaultAuth = $this->createMock(Acl::class);

        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emerg');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('crit');
        $logger->expects(self::never())
            ->method('err');
        $logger->expects(self::never())
            ->method('warn');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

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



        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::never())
            ->method('render');

        $translatePlugin = $this->getMockBuilder(Translate::class)
            ->disableOriginalConstructor()
            ->getMock();
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs($serviceLocator, $logger, $htmlify, $containerParser, $renderer, $translatePlugin);

        self::assertNull($helper->getAcl());
        self::assertFalse($helper->hasAcl());

        assert($defaultAuth instanceof Acl);
        assert($defaultAuth instanceof Acl);

        Breadcrumbs::setDefaultAcl($defaultAuth);

        self::assertSame($defaultAuth, $helper->getAcl());
        self::assertTrue($helper->hasAcl());

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        self::assertSame($auth, $helper->getAcl());
        self::assertTrue($helper->hasAcl());
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testSetView(): void
    {
        $view = $this->createMock(RendererInterface::class);

        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emerg');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('crit');
        $logger->expects(self::never())
            ->method('err');
        $logger->expects(self::never())
            ->method('warn');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

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



        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::never())
            ->method('render');

        $translatePlugin = $this->getMockBuilder(Translate::class)
            ->disableOriginalConstructor()
            ->getMock();
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs($serviceLocator, $logger, $htmlify, $containerParser, $renderer, $translatePlugin);

        self::assertNull($helper->getView());

        assert($view instanceof RendererInterface);
        $helper->setView($view);

        self::assertSame($view, $helper->getView());
        self::assertSame($serviceLocator, $helper->getServiceLocator());
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testSetContainer(): void
    {
        $container = $this->createMock(AbstractContainer::class);

        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emerg');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('crit');
        $logger->expects(self::never())
            ->method('err');
        $logger->expects(self::never())
            ->method('warn');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

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
        $containerParser->expects(self::exactly(2))
            ->method('parseContainer')
            ->withConsecutive([null], [$container])
            ->willReturnOnConsecutiveCalls(null, $container);

        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::never())
            ->method('render');

        $translatePlugin = $this->getMockBuilder(Translate::class)
            ->disableOriginalConstructor()
            ->getMock();
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs($serviceLocator, $logger, $htmlify, $containerParser, $renderer, $translatePlugin);

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
     */
    public function testSetContainerWithStringDefaultAndNavigationNotFound(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emerg');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('crit');
        $logger->expects(self::never())
            ->method('err');
        $logger->expects(self::never())
            ->method('warn');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

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



        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::never())
            ->method('render');

        $translatePlugin = $this->getMockBuilder(Translate::class)
            ->disableOriginalConstructor()
            ->getMock();
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs($serviceLocator, $logger, $htmlify, $containerParser, $renderer, $translatePlugin);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('test');
        $this->expectExceptionCode(0);

        $helper->setContainer($name);
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testSetContainerWithStringFound(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emerg');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('crit');
        $logger->expects(self::never())
            ->method('err');
        $logger->expects(self::never())
            ->method('warn');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $container = $this->createMock(AbstractContainer::class);
        $name      = 'Mezzio\\Navigation\\Top';

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



        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::never())
            ->method('render');

        $translatePlugin = $this->getMockBuilder(Translate::class)
            ->disableOriginalConstructor()
            ->getMock();
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs($serviceLocator, $logger, $htmlify, $containerParser, $renderer, $translatePlugin);

        $helper->setContainer($name);

        self::assertSame($container, $helper->getContainer());
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testDoNotAccept(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emerg');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('crit');
        $logger->expects(self::never())
            ->method('err');
        $logger->expects(self::never())
            ->method('warn');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $container = $this->createMock(AbstractContainer::class);
        $name      = 'Mezzio\\Navigation\\Top';

        $page = $this->getMockBuilder(AbstractPage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::never())
            ->method('isVisible');
        $page->expects(self::never())
            ->method('getResource');
        $page->expects(self::never())
            ->method('getPrivilege');

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
                ]
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



        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::never())
            ->method('render');

        $translatePlugin = $this->getMockBuilder(Translate::class)
            ->disableOriginalConstructor()
            ->getMock();
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs($serviceLocator, $logger, $htmlify, $containerParser, $renderer, $translatePlugin);

        $helper->setContainer($name);
        $helper->setRole($role);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        self::assertFalse($helper->accept($page));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testDoNotAcceptWithException(): void
    {
        $exception = new ServiceNotFoundException('test');

        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emerg');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('crit');
        $logger->expects(self::once())
            ->method('err')
            ->with($exception);
        $logger->expects(self::never())
            ->method('warn');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $container = $this->createMock(AbstractContainer::class);
        $name      = 'Mezzio\\Navigation\\Top';

        $page = $this->getMockBuilder(AbstractPage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::never())
            ->method('isVisible');
        $page->expects(self::never())
            ->method('getResource');
        $page->expects(self::never())
            ->method('getPrivilege');

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
                ]
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



        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::never())
            ->method('render');

        $translatePlugin = $this->getMockBuilder(Translate::class)
            ->disableOriginalConstructor()
            ->getMock();
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs($serviceLocator, $logger, $htmlify, $containerParser, $renderer, $translatePlugin);

        $helper->setContainer($name);
        $helper->setRole($role);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        self::assertFalse($helper->accept($page));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testHtmlify(): void
    {
        $expected = '<a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped" targetEscaped="_blankEscaped">testLabelTranslatedAndEscaped</a>';

        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emerg');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('crit');
        $logger->expects(self::never())
            ->method('err');
        $logger->expects(self::never())
            ->method('warn');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $container = $this->createMock(AbstractContainer::class);
        $name      = 'Mezzio\\Navigation\\Top';

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
            ->method('getLabel');
        $page->expects(self::never())
            ->method('getTitle');
        $page->expects(self::never())
            ->method('getTextDomain');
        $page->expects(self::never())
            ->method('getId');
        $page->expects(self::never())
            ->method('getClass');
        $page->expects(self::never())
            ->method('getHref');
        $page->expects(self::never())
            ->method('getTarget');


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



        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::never())
            ->method('render');

        $translatePlugin = $this->getMockBuilder(Translate::class)
            ->disableOriginalConstructor()
            ->getMock();
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs($serviceLocator, $logger, $htmlify, $containerParser, $renderer, $translatePlugin);

        $helper->setContainer($name);

        $view = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $view->expects(self::never())
            ->method('plugin');
        $view->expects(self::never())
            ->method('getHelperPluginManager');

        assert($view instanceof PhpRenderer);
        $helper->setView($view);


        self::assertSame($expected, $helper->htmlify($page));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testSetIndent(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emerg');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('crit');
        $logger->expects(self::never())
            ->method('err');
        $logger->expects(self::never())
            ->method('warn');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

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



        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::never())
            ->method('render');

        $translatePlugin = $this->getMockBuilder(Translate::class)
            ->disableOriginalConstructor()
            ->getMock();
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs($serviceLocator, $logger, $htmlify, $containerParser, $renderer, $translatePlugin);

        self::assertSame('', $helper->getIndent());

        $helper->setIndent(1);

        self::assertSame(' ', $helper->getIndent());

        $helper->setIndent('    ');

        self::assertSame('    ', $helper->getIndent());
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testFindActiveNoActivePages(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emerg');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('crit');
        $logger->expects(self::never())
            ->method('err');
        $logger->expects(self::never())
            ->method('warn');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $name = 'Mezzio\\Navigation\\Top';

        $parentPage = $this->getMockBuilder(AbstractPage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $parentPage->expects(self::never())
            ->method('isVisible');
        $parentPage->expects(self::never())
            ->method('getResource');
        $parentPage->expects(self::never())
            ->method('getPrivilege');
        $parentPage->expects(self::never())
            ->method('getParent');
        $parentPage->expects(self::never())
            ->method('isActive');

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
                ]
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



        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::never())
            ->method('render');

        $translatePlugin = $this->getMockBuilder(Translate::class)
            ->disableOriginalConstructor()
            ->getMock();
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs($serviceLocator, $logger, $htmlify, $containerParser, $renderer, $translatePlugin);

        $helper->setRole($role);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        self::assertSame([], $helper->findActive($name, $minDepth, $maxDepth));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     */
    public function testFindActiveOneActivePage(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emerg');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('crit');
        $logger->expects(self::never())
            ->method('err');
        $logger->expects(self::never())
            ->method('warn');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $name = 'Mezzio\\Navigation\\Top';

        $parentPage = $this->getMockBuilder(AbstractPage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $parentPage->expects(self::never())
            ->method('isVisible');
        $parentPage->expects(self::never())
            ->method('getResource');
        $parentPage->expects(self::never())
            ->method('getPrivilege');
        $parentPage->expects(self::never())
            ->method('getParent');
        $parentPage->expects(self::never())
            ->method('isActive');

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
                ]
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
                ]
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



        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::never())
            ->method('render');

        $translatePlugin = $this->getMockBuilder(Translate::class)
            ->disableOriginalConstructor()
            ->getMock();
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs($serviceLocator, $logger, $htmlify, $containerParser, $renderer, $translatePlugin);

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
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testFindActiveWithoutContainer(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emerg');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('crit');
        $logger->expects(self::never())
            ->method('err');
        $logger->expects(self::never())
            ->method('warn');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

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
                ]
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



        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::never())
            ->method('render');

        $translatePlugin = $this->getMockBuilder(Translate::class)
            ->disableOriginalConstructor()
            ->getMock();
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs($serviceLocator, $logger, $htmlify, $containerParser, $renderer, $translatePlugin);

        $helper->setRole($role);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        $expected = [];

        self::assertSame($expected, $helper->findActive(null, $minDepth, $maxDepth));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     */
    public function testFindActiveNoActivePageWithoutDepth(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emerg');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('crit');
        $logger->expects(self::never())
            ->method('err');
        $logger->expects(self::never())
            ->method('warn');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $name = 'Mezzio\\Navigation\\Top';

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
                ]
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



        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::never())
            ->method('render');

        $translatePlugin = $this->getMockBuilder(Translate::class)
            ->disableOriginalConstructor()
            ->getMock();
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs($serviceLocator, $logger, $htmlify, $containerParser, $renderer, $translatePlugin);

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
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     */
    public function testFindActiveOneActivePageOutOfRange(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emerg');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('crit');
        $logger->expects(self::never())
            ->method('err');
        $logger->expects(self::never())
            ->method('warn');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $name = 'Mezzio\\Navigation\\Top';

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
                ]
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



        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::never())
            ->method('render');

        $translatePlugin = $this->getMockBuilder(Translate::class)
            ->disableOriginalConstructor()
            ->getMock();
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs($serviceLocator, $logger, $htmlify, $containerParser, $renderer, $translatePlugin);

        $helper->setRole($role);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        $expected = [];

        self::assertSame($expected, $helper->findActive($name, $minDepth, $maxDepth));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     */
    public function testFindActiveOneActivePageRecursive(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emerg');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('crit');
        $logger->expects(self::never())
            ->method('err');
        $logger->expects(self::never())
            ->method('warn');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $name = 'Mezzio\\Navigation\\Top';

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
                ]
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
                ]
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



        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::never())
            ->method('render');

        $translatePlugin = $this->getMockBuilder(Translate::class)
            ->disableOriginalConstructor()
            ->getMock();
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs($serviceLocator, $logger, $htmlify, $containerParser, $renderer, $translatePlugin);

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
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     */
    public function testFindActiveOneActivePageRecursive2(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emerg');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('crit');
        $logger->expects(self::never())
            ->method('err');
        $logger->expects(self::never())
            ->method('warn');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $name = 'Mezzio\\Navigation\\Top';

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
                ]
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



        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::never())
            ->method('render');

        $translatePlugin = $this->getMockBuilder(Translate::class)
            ->disableOriginalConstructor()
            ->getMock();
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs($serviceLocator, $logger, $htmlify, $containerParser, $renderer, $translatePlugin);

        $helper->setRole($role);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        $expected = [];

        self::assertSame($expected, $helper->findActive($name, $minDepth, $maxDepth));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     */
    public function testFindActiveOneActivePageRecursive3(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emerg');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('crit');
        $logger->expects(self::never())
            ->method('err');
        $logger->expects(self::never())
            ->method('warn');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $name = 'Mezzio\\Navigation\\Top';

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
                ]
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



        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::never())
            ->method('render');

        $translatePlugin = $this->getMockBuilder(Translate::class)
            ->disableOriginalConstructor()
            ->getMock();
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs($serviceLocator, $logger, $htmlify, $containerParser, $renderer, $translatePlugin);

        $helper->setRole($role);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        $helper->setMinDepth(-1);
        $helper->setMaxDepth($maxDepth);

        $expected = [];

        self::assertSame($expected, $helper->findActive($name));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testSetPartial(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emerg');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('crit');
        $logger->expects(self::never())
            ->method('err');
        $logger->expects(self::never())
            ->method('warn');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

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



        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::never())
            ->method('render');

        $translatePlugin = $this->getMockBuilder(Translate::class)
            ->disableOriginalConstructor()
            ->getMock();
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs($serviceLocator, $logger, $htmlify, $containerParser, $renderer, $translatePlugin);

        self::assertNull($helper->getPartial());

        $helper->setPartial('test');

        self::assertSame('test', $helper->getPartial());

        $helper->setPartial(1);

        self::assertSame('test', $helper->getPartial());
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testSetLinkLast(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emerg');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('crit');
        $logger->expects(self::never())
            ->method('err');
        $logger->expects(self::never())
            ->method('warn');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

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



        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::never())
            ->method('render');

        $translatePlugin = $this->getMockBuilder(Translate::class)
            ->disableOriginalConstructor()
            ->getMock();
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs($serviceLocator, $logger, $htmlify, $containerParser, $renderer, $translatePlugin);

        self::assertFalse($helper->getLinkLast());

        $helper->setLinkLast(true);

        self::assertTrue($helper->getLinkLast());

        $helper->setLinkLast(false);

        self::assertFalse($helper->getLinkLast());
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testSetSeparator(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emerg');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('crit');
        $logger->expects(self::never())
            ->method('err');
        $logger->expects(self::never())
            ->method('warn');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

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



        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::never())
            ->method('render');

        $translatePlugin = $this->getMockBuilder(Translate::class)
            ->disableOriginalConstructor()
            ->getMock();
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs($serviceLocator, $logger, $htmlify, $containerParser, $renderer, $translatePlugin);

        self::assertSame(' &gt; ', $helper->getSeparator());

        $helper->setSeparator('/');

        self::assertSame('/', $helper->getSeparator());
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     */
    public function testRenderPartialWithParamsWithoutPartial(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emerg');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('crit');
        $logger->expects(self::never())
            ->method('err');
        $logger->expects(self::never())
            ->method('warn');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $name = 'Mezzio\\Navigation\\Top';

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



        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::never())
            ->method('render');

        $translatePlugin = $this->getMockBuilder(Translate::class)
            ->disableOriginalConstructor()
            ->getMock();
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs($serviceLocator, $logger, $htmlify, $containerParser, $renderer, $translatePlugin);

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

        $view = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $view->expects(self::never())
            ->method('plugin');

        assert($view instanceof PhpRenderer);
        $helper->setView($view);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to render breadcrumbs: No partial view script provided');
        $this->expectExceptionCode(0);

        $helper->renderPartialWithParams(['abc' => 'test'], $name);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     */
    public function testRenderPartialWithParamsWithWrongPartial(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emerg');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('crit');
        $logger->expects(self::never())
            ->method('err');
        $logger->expects(self::never())
            ->method('warn');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $name = 'Mezzio\\Navigation\\Top';

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



        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::never())
            ->method('render');

        $translatePlugin = $this->getMockBuilder(Translate::class)
            ->disableOriginalConstructor()
            ->getMock();
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs($serviceLocator, $logger, $htmlify, $containerParser, $renderer, $translatePlugin);

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

        $view = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $view->expects(self::never())
            ->method('plugin');

        assert($view instanceof PhpRenderer);
        $helper->setView($view);

        $helper->setPartial(['a', 'b', 'c']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to render breadcrumbs: A view partial supplied as an array must contain one value: the partial view script');
        $this->expectExceptionCode(0);

        $helper->renderPartialWithParams(['abc' => 'test'], $name);
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     */
    public function testRenderPartialWithParams(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emerg');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('crit');
        $logger->expects(self::never())
            ->method('err');
        $logger->expects(self::never())
            ->method('warn');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $name = 'Mezzio\\Navigation\\Top';

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
                ]
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
                ]
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
        $containerParser->expects(self::exactly(2))
            ->method('parseContainer')
            ->withConsecutive([$name], [$container])
            ->willReturnOnConsecutiveCalls($container, $container);



        $partial   = 'testPartial';
        $expected  = 'renderedPartial';
        $seperator = '/';

        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::once())
            ->method('render')
            ->with($partial, ['abc' => 'test', 'pages' => [$parentPage, $page], 'separator' => $seperator])
            ->willReturn($expected);

        $translatePlugin = $this->getMockBuilder(Translate::class)
            ->disableOriginalConstructor()
            ->getMock();
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs($serviceLocator, $logger, $htmlify, $containerParser, $renderer, $translatePlugin);

        $helper->setRole($role);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        $helper->setSeparator($seperator);
        $helper->setLinkLast(true);
        $helper->setPartial($partial);

        $view = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $view->expects(self::never())
            ->method('plugin');
        $view->expects(self::never())
            ->method('getHelperPluginManager');

        assert($view instanceof PhpRenderer);
        $helper->setView($view);

        self::assertSame($expected, $helper->renderPartialWithParams(['abc' => 'test'], $name));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     */
    public function testRenderPartialWithParamsAndArrayPartial(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emerg');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('crit');
        $logger->expects(self::never())
            ->method('err');
        $logger->expects(self::never())
            ->method('warn');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

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
                ]
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
                ]
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
        $containerParser->expects(self::exactly(3))
            ->method('parseContainer')
            ->withConsecutive([$container], [null], [$container])
            ->willReturnOnConsecutiveCalls($container, null, $container);



        $partial   = 'testPartial';
        $expected  = 'renderedPartial';
        $seperator = '/';

        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::once())
            ->method('render')
            ->with($partial, ['pages' => [$parentPage, $page], 'separator' => $seperator, 'abc' => 'test'])
            ->willReturn($expected);

        $translatePlugin = $this->getMockBuilder(Translate::class)
            ->disableOriginalConstructor()
            ->getMock();
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs($serviceLocator, $logger, $htmlify, $containerParser, $renderer, $translatePlugin);

        $helper->setRole($role);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        $helper->setSeparator($seperator);
        $helper->setLinkLast(true);
        $helper->setContainer($container);

        $view = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $view->expects(self::never())
            ->method('plugin');
        $view->expects(self::never())
            ->method('getHelperPluginManager');

        assert($view instanceof PhpRenderer);
        $helper->setView($view);

        self::assertSame($expected, $helper->renderPartialWithParams(['abc' => 'test'], null, [$partial, 'test']));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     */
    public function testRenderPartialWithParamsAndArrayPartialRenderingPage(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emerg');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('crit');
        $logger->expects(self::never())
            ->method('err');
        $logger->expects(self::never())
            ->method('warn');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

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
            ->willReturn($parentPage);
        $subPage->expects(self::never())
            ->method('isActive');

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
                ]
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
                ]
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
        $containerParser->expects(self::exactly(3))
            ->method('parseContainer')
            ->withConsecutive([$parentPage], [null], [$parentPage])
            ->willReturnOnConsecutiveCalls($parentPage, null, $parentPage);



        $partial   = 'testPartial';
        $expected  = 'renderedPartial';
        $seperator = '/';

        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::once())
            ->method('render')
            ->with($partial, ['pages' => [$parentPage, $subPage], 'separator' => $seperator, 'abc' => 'test'])
            ->willReturn($expected);

        $translatePlugin = $this->getMockBuilder(Translate::class)
            ->disableOriginalConstructor()
            ->getMock();
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs($serviceLocator, $logger, $htmlify, $containerParser, $renderer, $translatePlugin);

        $helper->setRole($role);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        $helper->setSeparator($seperator);
        $helper->setLinkLast(true);
        $helper->setContainer($parentPage);

        $view = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $view->expects(self::never())
            ->method('plugin');
        $view->expects(self::never())
            ->method('getHelperPluginManager');

        assert($view instanceof PhpRenderer);
        $helper->setView($view);

        self::assertSame($expected, $helper->renderPartialWithParams(['abc' => 'test'], null, [$partial, 'test']));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     */
    public function testRenderPartialWithParamsNoActivePage(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emerg');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('crit');
        $logger->expects(self::never())
            ->method('err');
        $logger->expects(self::never())
            ->method('warn');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $name = 'Mezzio\\Navigation\\Top';

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
            ->method('isActive');
        $page->expects(self::never())
            ->method('getParent');

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
                ]
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
        $containerParser->expects(self::exactly(2))
            ->method('parseContainer')
            ->withConsecutive([$name], [$container])
            ->willReturnOnConsecutiveCalls($container, $container);



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

        $translatePlugin = $this->getMockBuilder(Translate::class)
            ->disableOriginalConstructor()
            ->getMock();
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs($serviceLocator, $logger, $htmlify, $containerParser, $renderer, $translatePlugin);

        $helper->setRole($role);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        $helper->setSeparator($seperator);
        $helper->setLinkLast(true);
        $helper->setPartial($partial);

        $view = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $view->expects(self::never())
            ->method('plugin');
        $view->expects(self::never())
            ->method('getHelperPluginManager');

        assert($view instanceof PhpRenderer);
        $helper->setView($view);

        self::assertSame($expected, $helper->renderPartialWithParams(['abc' => 'test'], $name));
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     */
    public function testRenderPartialWithoutPartial(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emerg');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('crit');
        $logger->expects(self::never())
            ->method('err');
        $logger->expects(self::never())
            ->method('warn');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $name = 'Mezzio\\Navigation\\Top';

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



        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::never())
            ->method('render');

        $translatePlugin = $this->getMockBuilder(Translate::class)
            ->disableOriginalConstructor()
            ->getMock();
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs($serviceLocator, $logger, $htmlify, $containerParser, $renderer, $translatePlugin);

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

        $view = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $view->expects(self::never())
            ->method('plugin');

        assert($view instanceof PhpRenderer);
        $helper->setView($view);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to render breadcrumbs: No partial view script provided');
        $this->expectExceptionCode(0);

        $helper->renderPartial($name);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     */
    public function testRenderPartialWithWrongPartial(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emerg');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('crit');
        $logger->expects(self::never())
            ->method('err');
        $logger->expects(self::never())
            ->method('warn');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $name = 'Mezzio\\Navigation\\Top';

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



        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::never())
            ->method('render');

        $translatePlugin = $this->getMockBuilder(Translate::class)
            ->disableOriginalConstructor()
            ->getMock();
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs($serviceLocator, $logger, $htmlify, $containerParser, $renderer, $translatePlugin);

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

        $view = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $view->expects(self::never())
            ->method('plugin');

        assert($view instanceof PhpRenderer);
        $helper->setView($view);

        $helper->setPartial(['a', 'b', 'c']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to render breadcrumbs: A view partial supplied as an array must contain one value: the partial view script');
        $this->expectExceptionCode(0);

        $helper->renderPartial($name);
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     */
    public function testRenderPartial(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emerg');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('crit');
        $logger->expects(self::never())
            ->method('err');
        $logger->expects(self::never())
            ->method('warn');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $name = 'Mezzio\\Navigation\\Top';

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
                ]
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
                ]
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
        $containerParser->expects(self::exactly(2))
            ->method('parseContainer')
            ->withConsecutive([$name], [$container])
            ->willReturnOnConsecutiveCalls($container, $container);



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

        $translatePlugin = $this->getMockBuilder(Translate::class)
            ->disableOriginalConstructor()
            ->getMock();
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs($serviceLocator, $logger, $htmlify, $containerParser, $renderer, $translatePlugin);

        $helper->setRole($role);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        $helper->setSeparator($seperator);
        $helper->setLinkLast(true);
        $helper->setPartial($partial);

        $view = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $view->expects(self::never())
            ->method('plugin');
        $view->expects(self::never())
            ->method('getHelperPluginManager');

        assert($view instanceof PhpRenderer);
        $helper->setView($view);

        self::assertSame($expected, $helper->renderPartial($name));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     */
    public function testRenderPartialNoActivePage(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emerg');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('crit');
        $logger->expects(self::never())
            ->method('err');
        $logger->expects(self::never())
            ->method('warn');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $name = 'Mezzio\\Navigation\\Top';

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
            ->method('isActive');
        $page->expects(self::never())
            ->method('getParent');

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
                ]
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
        $containerParser->expects(self::exactly(2))
            ->method('parseContainer')
            ->withConsecutive([$name], [$container])
            ->willReturnOnConsecutiveCalls($container, $container);



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

        $translatePlugin = $this->getMockBuilder(Translate::class)
            ->disableOriginalConstructor()
            ->getMock();
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs($serviceLocator, $logger, $htmlify, $containerParser, $renderer, $translatePlugin);

        $helper->setRole($role);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        $helper->setSeparator($seperator);
        $helper->setLinkLast(true);
        $helper->setPartial($partial);

        $view = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $view->expects(self::never())
            ->method('plugin');
        $view->expects(self::never())
            ->method('getHelperPluginManager');

        assert($view instanceof PhpRenderer);
        $helper->setView($view);

        self::assertSame($expected, $helper->renderPartial($name));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     */
    public function testRenderPartialWithArrayPartial(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emerg');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('crit');
        $logger->expects(self::never())
            ->method('err');
        $logger->expects(self::never())
            ->method('warn');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

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
                ]
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
                ]
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
        $containerParser->expects(self::exactly(3))
            ->method('parseContainer')
            ->withConsecutive([$container], [null], [$container])
            ->willReturnOnConsecutiveCalls($container, null, $container);



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

        $translatePlugin = $this->getMockBuilder(Translate::class)
            ->disableOriginalConstructor()
            ->getMock();
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs($serviceLocator, $logger, $htmlify, $containerParser, $renderer, $translatePlugin);

        $helper->setRole($role);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        $helper->setSeparator($seperator);
        $helper->setLinkLast(true);
        $helper->setContainer($container);

        $view = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $view->expects(self::never())
            ->method('plugin');
        $view->expects(self::never())
            ->method('getHelperPluginManager');

        assert($view instanceof PhpRenderer);
        $helper->setView($view);

        self::assertSame($expected, $helper->renderPartial(null, [$partial, 'test']));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     */
    public function testRenderPartialWithArrayPartialRenderingPage(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emerg');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('crit');
        $logger->expects(self::never())
            ->method('err');
        $logger->expects(self::never())
            ->method('warn');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

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
            ->willReturn($parentPage);
        $subPage->expects(self::never())
            ->method('isActive');

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
                ]
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
                ]
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
        $containerParser->expects(self::exactly(3))
            ->method('parseContainer')
            ->withConsecutive([$parentPage], [null], [$parentPage])
            ->willReturnOnConsecutiveCalls($parentPage, null, $parentPage);



        $expected  = 'renderedPartial';
        $partial   = 'testPartial';
        $seperator = '/';

        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::once())
            ->method('render')
            ->with($partial, ['pages' => [$parentPage, $subPage], 'separator' => $seperator])
            ->willReturn($expected);

        $translatePlugin = $this->getMockBuilder(Translate::class)
            ->disableOriginalConstructor()
            ->getMock();
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs($serviceLocator, $logger, $htmlify, $containerParser, $renderer, $translatePlugin);

        $helper->setRole($role);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        $helper->setSeparator($seperator);
        $helper->setLinkLast(true);
        $helper->setContainer($parentPage);

        $view = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $view->expects(self::never())
            ->method('plugin');
        $view->expects(self::never())
            ->method('getHelperPluginManager');

        assert($view instanceof PhpRenderer);
        $helper->setView($view);

        self::assertSame($expected, $helper->renderPartial(null, [$partial, 'test']));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     */
    public function testRenderStraightNoActivePage(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emerg');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('crit');
        $logger->expects(self::never())
            ->method('err');
        $logger->expects(self::never())
            ->method('warn');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $name = 'Mezzio\\Navigation\\Top';

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
            ->method('isActive');
        $page->expects(self::never())
            ->method('getParent');

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
                ]
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
        $containerParser->expects(self::exactly(2))
            ->method('parseContainer')
            ->withConsecutive([$name], [$container])
            ->willReturnOnConsecutiveCalls($container, $container);



        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::never())
            ->method('render');

        $translatePlugin = $this->getMockBuilder(Translate::class)
            ->disableOriginalConstructor()
            ->getMock();
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs($serviceLocator, $logger, $htmlify, $containerParser, $renderer, $translatePlugin);

        $helper->setRole($role);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        $expected  = '';
        $partial   = 'testPartial';
        $seperator = '/';

        $helper->setSeparator($seperator);
        $helper->setLinkLast(true);
        $helper->setPartial($partial);

        $view = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $view->expects(self::never())
            ->method('plugin');

        assert($view instanceof PhpRenderer);
        $helper->setView($view);

        self::assertSame($expected, $helper->renderStraight($name));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     */
    public function testRenderStraight(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emerg');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('crit');
        $logger->expects(self::never())
            ->method('err');
        $logger->expects(self::never())
            ->method('warn');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $name = 'Mezzio\\Navigation\\Top';

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
                ]
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
                ]
            )
            ->willReturn($findActiveHelper);

        $expected1 = '<a parent-id-escaped="parent-id-escaped" parent-title-escaped="parent-title-escaped" parent-class-escaped="parent-class-escaped" parent-href-escaped="##-escaped" parent-target-escaped="self-escaped">parent-label-escaped</a>';
        $expected2 = '<a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>';

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::exactly(2))
            ->method('toHtml')
            ->withConsecutive([Breadcrumbs::class, $page], [Breadcrumbs::class, $parentPage])
            ->willReturnOnConsecutiveCalls($expected2, $expected1);

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::exactly(2))
            ->method('parseContainer')
            ->withConsecutive([$name], [$container])
            ->willReturn($container, $container);



        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::never())
            ->method('render');

        $translatePlugin = $this->getMockBuilder(Translate::class)
            ->disableOriginalConstructor()
            ->getMock();
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs($serviceLocator, $logger, $htmlify, $containerParser, $renderer, $translatePlugin);

        $helper->setRole($role);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        $expected  = '<nav aria-label="breadcrumb">'
            . PHP_EOL . '<ul class="breadcrumb">'
            . PHP_EOL . '<li class="breadcrumb-item">'
            . PHP_EOL . '<a parent-id-escaped="parent-id-escaped" parent-title-escaped="parent-title-escaped" parent-class-escaped="parent-class-escaped" parent-href-escaped="##-escaped" parent-target-escaped="self-escaped">parent-label-escaped</a>'
            . PHP_EOL . '</li>'
            . PHP_EOL . '/'
            . PHP_EOL . '<li class="breadcrumb-item active" aria-current="page">'
            . PHP_EOL . '<a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>'
            . PHP_EOL . '</li>'
            . PHP_EOL . '</ul>'
            . PHP_EOL . '</nav>'
            . PHP_EOL;
        $seperator = '/';

        $helper->setSeparator($seperator);
        $helper->setLinkLast(true);

        $view = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $view->expects(self::never())
            ->method('plugin');
        $view->expects(self::never())
            ->method('getHelperPluginManager');

        assert($view instanceof PhpRenderer);
        $helper->setView($view);

        self::assertSame($expected, $helper->renderStraight($name));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     */
    public function testRenderStraightWithoutLinkAtEnd(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emerg');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('crit');
        $logger->expects(self::never())
            ->method('err');
        $logger->expects(self::never())
            ->method('warn');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

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
                ]
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
                ]
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
        $containerParser->expects(self::exactly(3))
            ->method('parseContainer')
            ->withConsecutive([$container], [null], [$container])
            ->willReturnOnConsecutiveCalls($container, null, $container);

        $escapePlugin = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapePlugin->expects(self::once())
            ->method('__invoke')
            ->with($tranalatedLabel)
            ->willReturn($escapedTranalatedLabel);

        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::never())
            ->method('render');

        $translatePlugin = $this->getMockBuilder(Translate::class)
            ->disableOriginalConstructor()
            ->getMock();
        $translatePlugin->expects(self::once())
            ->method('__invoke')
            ->with($label, $textDomain)
            ->willReturn($tranalatedLabel);

        $helper = new Breadcrumbs($serviceLocator, $logger, $htmlify, $containerParser, $renderer, $translatePlugin);

        $helper->setRole($role);
        $helper->setContainer($container);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        $expected  = '<nav aria-label="breadcrumb">'
            . PHP_EOL . '<ul class="breadcrumb">'
            . PHP_EOL . '<li class="breadcrumb-item">'
            . PHP_EOL . '<a parent-id-escaped="parent-id-escaped" parent-title-escaped="parent-title-escaped" parent-class-escaped="parent-class-escaped" parent-href-escaped="##-escaped" parent-target-escaped="self-escaped">parent-label-escaped</a>'
            . PHP_EOL . '</li>'
            . PHP_EOL . '/'
            . PHP_EOL . '<li class="breadcrumb-item">'
            . PHP_EOL . 'testLabelTranslatedAndEscaped'
            . PHP_EOL . '</li>'
            . PHP_EOL . '</ul>'
            . PHP_EOL . '</nav>'
            . PHP_EOL;
        $seperator = '/';

        $helper->setSeparator($seperator);
        $helper->setLinkLast(false);

        $view = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $view->expects(self::never())
            ->method('plugin');
        $view->expects(self::never())
            ->method('getHelperPluginManager');

        assert($view instanceof PhpRenderer);
        $helper->setView($view);

        self::assertSame($expected, $helper->renderStraight());
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     */
    public function testRenderStraightWithoutLinkAtEndWithLiClass(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emerg');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('crit');
        $logger->expects(self::never())
            ->method('err');
        $logger->expects(self::never())
            ->method('warn');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

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
                ]
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
                ]
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
        $containerParser->expects(self::exactly(3))
            ->method('parseContainer')
            ->withConsecutive([$container], [null], [$container])
            ->willReturnOnConsecutiveCalls($container, null, $container);

        $escapePlugin = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapePlugin->expects(self::once())
            ->method('__invoke')
            ->with($tranalatedLabel)
            ->willReturn($escapedTranalatedLabel);

        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::never())
            ->method('render');

        $translatePlugin = $this->getMockBuilder(Translate::class)
            ->disableOriginalConstructor()
            ->getMock();
        $translatePlugin->expects(self::once())
            ->method('__invoke')
            ->with($label, $textDomain)
            ->willReturn($tranalatedLabel);

        $helper = new Breadcrumbs($serviceLocator, $logger, $htmlify, $containerParser, $renderer, $translatePlugin);

        $helper->setRole($role);
        $helper->setContainer($container);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        $expected  = '<nav aria-label="breadcrumb">'
            . PHP_EOL . '<ul class="breadcrumb">'
            . PHP_EOL . '<li class="breadcrumb-item">'
            . PHP_EOL . '<a parent-id-escaped="parent-id-escaped" parent-title-escaped="parent-title-escaped" parent-class-escaped="parent-class-escaped" parent-href-escaped="##-escaped" parent-target-escaped="self-escaped">parent-label-escaped</a>'
            . PHP_EOL . '</li>'
            . PHP_EOL . '/'
            . PHP_EOL . '<li class="breadcrumb-item li-class">'
            . PHP_EOL . 'testLabelTranslatedAndEscaped'
            . PHP_EOL . '</li>'
            . PHP_EOL . '</ul>'
            . PHP_EOL . '</nav>'
            . PHP_EOL;
        $seperator = '/';

        $helper->setSeparator($seperator);
        $helper->setLinkLast(false);

        $view = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $view->expects(self::never())
            ->method('plugin');
        $view->expects(self::never())
            ->method('getHelperPluginManager');

        assert($view instanceof PhpRenderer);
        $helper->setView($view);

        self::assertSame($expected, $helper->renderStraight());
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     */
    public function testRenderStraightWithoutLinkAtEndWithLiClass2(): void
    {
        $indent = '    ';

        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emerg');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('crit');
        $logger->expects(self::never())
            ->method('err');
        $logger->expects(self::never())
            ->method('warn');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

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
                ]
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
                ]
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
        $containerParser->expects(self::exactly(3))
            ->method('parseContainer')
            ->withConsecutive([$container], [null], [$container])
            ->willReturnOnConsecutiveCalls($container, null, $container);

        $escapePlugin = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapePlugin->expects(self::once())
            ->method('__invoke')
            ->with($tranalatedLabel)
            ->willReturn($escapedTranalatedLabel);

        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::never())
            ->method('render');

        $translatePlugin = $this->getMockBuilder(Translate::class)
            ->disableOriginalConstructor()
            ->getMock();
        $translatePlugin->expects(self::once())
            ->method('__invoke')
            ->with($label, $textDomain)
            ->willReturn($tranalatedLabel);

        $helper = new Breadcrumbs($serviceLocator, $logger, $htmlify, $containerParser, $renderer, $translatePlugin);

        $helper->setRole($role);
        $helper->setContainer($container);

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

        $view = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $view->expects(self::never())
            ->method('plugin');
        $view->expects(self::never())
            ->method('getHelperPluginManager');

        assert($view instanceof PhpRenderer);
        $helper->setView($view);
        $helper->setIndent($indent);

        self::assertSame($expected, $helper->renderStraight());
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     */
    public function testRenderWithoutPartial(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emerg');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('crit');
        $logger->expects(self::never())
            ->method('err');
        $logger->expects(self::never())
            ->method('warn');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $name      = 'Mezzio\\Navigation\\Top';
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
                ]
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
                ]
            )
            ->willReturn($findActiveHelper);

        $expected1 = '<a parent-id-escaped="parent-id-escaped" parent-title-escaped="parent-title-escaped" parent-class-escaped="parent-class-escaped" parent-href-escaped="##-escaped" parent-target-escaped="self-escaped">parent-label-escaped</a>';
        $expected2 = '<a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>';

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::exactly(2))
            ->method('toHtml')
            ->withConsecutive([Breadcrumbs::class, $page], [Breadcrumbs::class, $parentPage])
            ->willReturnOnConsecutiveCalls($expected2, $expected1);

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::exactly(2))
            ->method('parseContainer')
            ->withConsecutive([$name], [$container])
            ->willReturnOnConsecutiveCalls($container, $container);



        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::never())
            ->method('render');

        $translatePlugin = $this->getMockBuilder(Translate::class)
            ->disableOriginalConstructor()
            ->getMock();
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs($serviceLocator, $logger, $htmlify, $containerParser, $renderer, $translatePlugin);

        $helper->setRole($role);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        $expected  = '<nav aria-label="breadcrumb">'
            . PHP_EOL . '<ul class="breadcrumb">'
            . PHP_EOL . '<li class="breadcrumb-item">'
            . PHP_EOL . '<a parent-id-escaped="parent-id-escaped" parent-title-escaped="parent-title-escaped" parent-class-escaped="parent-class-escaped" parent-href-escaped="##-escaped" parent-target-escaped="self-escaped">parent-label-escaped</a>'
            . PHP_EOL . '</li>'
            . PHP_EOL . '/'
            . PHP_EOL . '<li class="breadcrumb-item">'
            . PHP_EOL . '<a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>'
            . PHP_EOL . '</li>'
            . PHP_EOL . '</ul>'
            . PHP_EOL . '</nav>'
            . PHP_EOL;
        $seperator = '/';

        $helper->setSeparator($seperator);
        $helper->setLinkLast(true);

        $view = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $view->expects(self::never())
            ->method('plugin');
        $view->expects(self::never())
            ->method('getHelperPluginManager');

        assert($view instanceof PhpRenderer);
        $helper->setView($view);

        self::assertSame($expected, $helper->render($name));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     */
    public function testRenderWithPartial(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emerg');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('crit');
        $logger->expects(self::never())
            ->method('err');
        $logger->expects(self::never())
            ->method('warn');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $name = 'Mezzio\\Navigation\\Top';

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
            ->method('isActive');
        $page->expects(self::never())
            ->method('getParent');

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
                ]
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
        $containerParser->expects(self::exactly(2))
            ->method('parseContainer')
            ->withConsecutive([$name], [$container])
            ->willReturnOnConsecutiveCalls($container, $container);



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

        $translatePlugin = $this->getMockBuilder(Translate::class)
            ->disableOriginalConstructor()
            ->getMock();
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs($serviceLocator, $logger, $htmlify, $containerParser, $renderer, $translatePlugin);

        $helper->setRole($role);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        $helper->setSeparator($seperator);
        $helper->setLinkLast(true);
        $helper->setPartial($partial);

        $view = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $view->expects(self::never())
            ->method('plugin');
        $view->expects(self::never())
            ->method('getHelperPluginManager');

        assert($view instanceof PhpRenderer);
        $helper->setView($view);

        self::assertSame($expected, $helper->render($name));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     */
    public function testToStringWithPartial(): void
    {
        $auth = $this->getMockBuilder(Acl::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isAllowed');
        assert($auth instanceof Acl);

        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emerg');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('crit');
        $logger->expects(self::never())
            ->method('err');
        $logger->expects(self::never())
            ->method('warn');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $name = 'Mezzio\\Navigation\\Top';

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
            ->method('isActive');
        $page->expects(self::never())
            ->method('getParent');

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
                ]
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
        $containerParser->expects(self::exactly(3))
            ->method('parseContainer')
            ->withConsecutive([$name], [null], [$container])
            ->willReturnOnConsecutiveCalls($container, null, $container);



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

        $translatePlugin = $this->getMockBuilder(Translate::class)
            ->disableOriginalConstructor()
            ->getMock();
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs($serviceLocator, $logger, $htmlify, $containerParser, $renderer, $translatePlugin);

        $helper->setRole($role);
        $helper->setAcl($auth);
        $helper->setSeparator($seperator);
        $helper->setLinkLast(true);
        $helper->setPartial($partial);

        $view = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $view->expects(self::never())
            ->method('plugin');
        $view->expects(self::never())
            ->method('getHelperPluginManager');

        assert($view instanceof PhpRenderer);
        $helper->setView($view);

        self::assertSame($expected, (string) $helper($name));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testInvoke(): void
    {
        $container = $this->createMock(AbstractContainer::class);

        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emerg');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('crit');
        $logger->expects(self::never())
            ->method('err');
        $logger->expects(self::never())
            ->method('warn');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

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



        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::never())
            ->method('render');

        $translatePlugin = $this->getMockBuilder(Translate::class)
            ->disableOriginalConstructor()
            ->getMock();
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs($serviceLocator, $logger, $htmlify, $containerParser, $renderer, $translatePlugin);

        $container1 = $helper->getContainer();

        self::assertInstanceOf(Navigation::class, $container1);

        $helper($container);

        self::assertSame($container, $helper->getContainer());
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     */
    public function testDoNotRenderIfNoPageIsActive(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emerg');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('crit');
        $logger->expects(self::never())
            ->method('err');
        $logger->expects(self::never())
            ->method('warn');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

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
            ->method('isActive');
        $page->expects(self::never())
            ->method('getParent');

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
                ]
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
        $containerParser->expects(self::exactly(3))
            ->method('parseContainer')
            ->withConsecutive([$container], [null], [$container])
            ->willReturnOnConsecutiveCalls($container, null, $container);



        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::never())
            ->method('render');

        $translatePlugin = $this->getMockBuilder(Translate::class)
            ->disableOriginalConstructor()
            ->getMock();
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs($serviceLocator, $logger, $htmlify, $containerParser, $renderer, $translatePlugin);

        $helper->setContainer($container);

        self::assertSame('', $helper->render());
    }
}
