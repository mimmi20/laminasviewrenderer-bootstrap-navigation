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

namespace Mimmi20Test\LaminasView\BootstrapNavigation;

use Laminas\I18n\Translator\TranslatorInterface as Translator;
use Laminas\Log\Logger;
use Laminas\Navigation\AbstractContainer;
use Laminas\Navigation\Navigation;
use Laminas\Navigation\Page\AbstractPage;
use Laminas\Navigation\Page\Uri;
use Laminas\Permissions\Acl\Acl;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\Exception\ExceptionInterface;
use Laminas\View\Exception\InvalidArgumentException;
use Laminas\View\Exception\RuntimeException;
use Laminas\View\Helper\EscapeHtml;
use Laminas\View\Helper\EscapeHtmlAttr;
use Laminas\View\Model\ModelInterface;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Renderer\RendererInterface;
use Mimmi20\LaminasView\BootstrapNavigation\Menu;
use Mimmi20\LaminasView\Helper\HtmlElement\Helper\HtmlElementInterface;
use Mimmi20\NavigationHelper\Accept\AcceptHelperInterface;
use Mimmi20\NavigationHelper\ContainerParser\ContainerParserInterface;
use Mimmi20\NavigationHelper\FindActive\FindActiveInterface;
use PHPUnit\Framework\Constraint\IsInstanceOf;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;

use function assert;

use const PHP_EOL;

final class MenuTest extends TestCase
{
    /**
     * @throws InvalidArgumentException
     */
    protected function tearDown(): void
    {
        Menu::setDefaultAcl(null);
        Menu::setDefaultRole(null);
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

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $escapeHtmlAttr = $this->getMockBuilder(EscapeHtmlAttr::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtmlAttr->expects(self::never())
            ->method('__invoke');

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

        $htmlElement = $this->getMockBuilder(HtmlElementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlElement->expects(self::never())
            ->method('toHtml');

        $helper = new Menu(
            $serviceLocator,
            $logger,
            $containerParser,
            $escapeHtmlAttr,
            $renderer,
            $escapeHtml,
            $htmlElement
        );

        self::assertSame($renderer, $helper->getView());

        assert($view instanceof RendererInterface);
        $helper->setView($view);

        self::assertSame($view, $helper->getView());
        self::assertSame($serviceLocator, $helper->getServiceLocator());
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
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

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::exactly(2))
            ->method('parseContainer')
            ->withConsecutive([null], [$container])
            ->willReturnOnConsecutiveCalls(null, $container);

        $escapeHtmlAttr = $this->getMockBuilder(EscapeHtmlAttr::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtmlAttr->expects(self::never())
            ->method('__invoke');

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

        $htmlElement = $this->getMockBuilder(HtmlElementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlElement->expects(self::never())
            ->method('toHtml');

        $helper = new Menu(
            $serviceLocator,
            $logger,
            $containerParser,
            $escapeHtmlAttr,
            $renderer,
            $escapeHtml,
            $htmlElement
        );

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

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willThrowException(new InvalidArgumentException('test'));

        $escapeHtmlAttr = $this->getMockBuilder(EscapeHtmlAttr::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtmlAttr->expects(self::never())
            ->method('__invoke');

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

        $htmlElement = $this->getMockBuilder(HtmlElementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlElement->expects(self::never())
            ->method('toHtml');

        $helper = new Menu(
            $serviceLocator,
            $logger,
            $containerParser,
            $escapeHtmlAttr,
            $renderer,
            $escapeHtml,
            $htmlElement
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('test');
        $this->expectExceptionCode(0);

        $helper->setContainer($name);
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
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

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        $escapeHtmlAttr = $this->getMockBuilder(EscapeHtmlAttr::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtmlAttr->expects(self::never())
            ->method('__invoke');

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

        $htmlElement = $this->getMockBuilder(HtmlElementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlElement->expects(self::never())
            ->method('toHtml');

        $helper = new Menu(
            $serviceLocator,
            $logger,
            $containerParser,
            $escapeHtmlAttr,
            $renderer,
            $escapeHtml,
            $htmlElement
        );

        $helper->setContainer($name);

        self::assertSame($container, $helper->getContainer());
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
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
                ]
            )
            ->willReturn($acceptHelper);

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        $escapeHtmlAttr = $this->getMockBuilder(EscapeHtmlAttr::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtmlAttr->expects(self::never())
            ->method('__invoke');

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

        $htmlElement = $this->getMockBuilder(HtmlElementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlElement->expects(self::never())
            ->method('toHtml');

        $helper = new Menu(
            $serviceLocator,
            $logger,
            $containerParser,
            $escapeHtmlAttr,
            $renderer,
            $escapeHtml,
            $htmlElement
        );

        $helper->setContainer($name);
        $helper->setRole($role);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        assert($page instanceof AbstractPage);
        self::assertFalse($helper->accept($page));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
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

        $pageLabel                  = 'page-label';
        $pageLabelTranslated        = 'page-label-translated';
        $pageLabelTranslatedEscaped = 'page-label-translated-escaped';
        $pageTitle                  = 'page-title';
        $pageTitleTranslated        = 'page-title-translated';
        $pageTextDomain             = 'page-text-domain';
        $pageId                     = 'page-id';
        $pageHref                   = 'http://page';
        $pageTarget                 = 'page-target';

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
        $page->expects(self::once())
            ->method('getLabel')
            ->willReturn($pageLabel);
        $page->expects(self::once())
            ->method('getTextDomain')
            ->willReturn($pageTextDomain);
        $page->expects(self::once())
            ->method('getTitle')
            ->willReturn($pageTitle);
        $page->expects(self::once())
            ->method('getId')
            ->willReturn($pageId);
        $page->expects(self::never())
            ->method('getClass');
        $page->expects(self::exactly(2))
            ->method('getHref')
            ->willReturn($pageHref);
        $page->expects(self::once())
            ->method('getTarget')
            ->willReturn($pageTarget);
        $page->expects(self::never())
            ->method('hasPage');
        $page->expects(self::never())
            ->method('hasPages');
        $page->expects(self::once())
            ->method('getCustomProperties')
            ->willReturn([]);
        $page->expects(self::never())
            ->method('get');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        $escapeHtmlAttr = $this->getMockBuilder(EscapeHtmlAttr::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtmlAttr->expects(self::never())
            ->method('__invoke');

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::once())
            ->method('__invoke')
            ->with($pageLabelTranslated)
            ->willReturn($pageLabelTranslatedEscaped);

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
        $translator->expects(self::exactly(2))
            ->method('translate')
            ->withConsecutive([$pageLabel, $pageTextDomain], [$pageTitle, $pageTextDomain])
            ->willReturnOnConsecutiveCalls($pageLabelTranslated, $pageTitleTranslated);

        $htmlElement = $this->getMockBuilder(HtmlElementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlElement->expects(self::once())
            ->method('toHtml')
            ->with('a', ['id' => $pageId, 'title' => $pageTitleTranslated, 'href' => $pageHref, 'target' => $pageTarget], $pageLabelTranslatedEscaped)
            ->willReturn($expected);

        $helper = new Menu(
            $serviceLocator,
            $logger,
            $containerParser,
            $escapeHtmlAttr,
            $renderer,
            $escapeHtml,
            $htmlElement
        );

        $helper->setContainer($name);
        $helper->setTranslator($translator);

        assert($page instanceof AbstractPage);
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

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $escapeHtmlAttr = $this->getMockBuilder(EscapeHtmlAttr::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtmlAttr->expects(self::never())
            ->method('__invoke');

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

        $htmlElement = $this->getMockBuilder(HtmlElementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlElement->expects(self::never())
            ->method('toHtml');

        $helper = new Menu(
            $serviceLocator,
            $logger,
            $containerParser,
            $escapeHtmlAttr,
            $renderer,
            $escapeHtml,
            $htmlElement
        );

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
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
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
                ]
            )
            ->willReturn($findActiveHelper);

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        $escapeHtmlAttr = $this->getMockBuilder(EscapeHtmlAttr::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtmlAttr->expects(self::never())
            ->method('__invoke');

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

        $htmlElement = $this->getMockBuilder(HtmlElementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlElement->expects(self::never())
            ->method('toHtml');

        $helper = new Menu(
            $serviceLocator,
            $logger,
            $containerParser,
            $escapeHtmlAttr,
            $renderer,
            $escapeHtml,
            $htmlElement
        );

        $helper->setRole($role);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        self::assertSame([], $helper->findActive($name, $minDepth, $maxDepth));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
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

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        $escapeHtmlAttr = $this->getMockBuilder(EscapeHtmlAttr::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtmlAttr->expects(self::never())
            ->method('__invoke');

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

        $htmlElement = $this->getMockBuilder(HtmlElementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlElement->expects(self::never())
            ->method('toHtml');

        $helper = new Menu(
            $serviceLocator,
            $logger,
            $containerParser,
            $escapeHtmlAttr,
            $renderer,
            $escapeHtml,
            $htmlElement
        );

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
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
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
        $minDepth = 0;

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

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with(null)
            ->willReturn(null);

        $escapeHtmlAttr = $this->getMockBuilder(EscapeHtmlAttr::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtmlAttr->expects(self::never())
            ->method('__invoke');

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

        $htmlElement = $this->getMockBuilder(HtmlElementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlElement->expects(self::never())
            ->method('toHtml');

        $helper = new Menu(
            $serviceLocator,
            $logger,
            $containerParser,
            $escapeHtmlAttr,
            $renderer,
            $escapeHtml,
            $htmlElement
        );

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
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     */
    public function testFindActiveOneActivePageWithoutDepth(): void
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

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        $escapeHtmlAttr = $this->getMockBuilder(EscapeHtmlAttr::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtmlAttr->expects(self::never())
            ->method('__invoke');

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

        $htmlElement = $this->getMockBuilder(HtmlElementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlElement->expects(self::never())
            ->method('toHtml');

        $helper = new Menu(
            $serviceLocator,
            $logger,
            $containerParser,
            $escapeHtmlAttr,
            $renderer,
            $escapeHtml,
            $htmlElement
        );

        $helper->setRole($role);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        $expected = [
            'page' => $page,
            'depth' => 0,
        ];

        $helper->setMinDepth($minDepth);
        $helper->setMaxDepth($maxDepth);

        self::assertSame($expected, $helper->findActive($name));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
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
                ]
            )
            ->willReturn($findActiveHelper);

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        $escapeHtmlAttr = $this->getMockBuilder(EscapeHtmlAttr::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtmlAttr->expects(self::never())
            ->method('__invoke');

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

        $htmlElement = $this->getMockBuilder(HtmlElementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlElement->expects(self::never())
            ->method('toHtml');

        $helper = new Menu(
            $serviceLocator,
            $logger,
            $containerParser,
            $escapeHtmlAttr,
            $renderer,
            $escapeHtml,
            $htmlElement
        );

        $helper->setRole($role);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        $expected = [];

        self::assertSame($expected, $helper->findActive($name, 2, 42));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
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

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        $escapeHtmlAttr = $this->getMockBuilder(EscapeHtmlAttr::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtmlAttr->expects(self::never())
            ->method('__invoke');

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

        $htmlElement = $this->getMockBuilder(HtmlElementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlElement->expects(self::never())
            ->method('toHtml');

        $helper = new Menu(
            $serviceLocator,
            $logger,
            $containerParser,
            $escapeHtmlAttr,
            $renderer,
            $escapeHtml,
            $htmlElement
        );

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
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
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

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        $escapeHtmlAttr = $this->getMockBuilder(EscapeHtmlAttr::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtmlAttr->expects(self::never())
            ->method('__invoke');

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

        $htmlElement = $this->getMockBuilder(HtmlElementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlElement->expects(self::never())
            ->method('toHtml');

        $helper = new Menu(
            $serviceLocator,
            $logger,
            $containerParser,
            $escapeHtmlAttr,
            $renderer,
            $escapeHtml,
            $htmlElement
        );

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
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
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
            ->with($container, 0, $maxDepth)
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

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        $escapeHtmlAttr = $this->getMockBuilder(EscapeHtmlAttr::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtmlAttr->expects(self::never())
            ->method('__invoke');

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

        $htmlElement = $this->getMockBuilder(HtmlElementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlElement->expects(self::never())
            ->method('toHtml');

        $helper = new Menu(
            $serviceLocator,
            $logger,
            $containerParser,
            $escapeHtmlAttr,
            $renderer,
            $escapeHtml,
            $htmlElement
        );

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
    public function testSetAddClassToListItem(): void
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

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $escapeHtmlAttr = $this->getMockBuilder(EscapeHtmlAttr::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtmlAttr->expects(self::never())
            ->method('__invoke');

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

        $htmlElement = $this->getMockBuilder(HtmlElementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlElement->expects(self::never())
            ->method('toHtml');

        $helper = new Menu(
            $serviceLocator,
            $logger,
            $containerParser,
            $escapeHtmlAttr,
            $renderer,
            $escapeHtml,
            $htmlElement
        );

        self::assertFalse($helper->getAddClassToListItem());

        $helper->setAddClassToListItem(true);

        self::assertTrue($helper->getAddClassToListItem());
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testSetOnlyActiveBranch(): void
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

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $escapeHtmlAttr = $this->getMockBuilder(EscapeHtmlAttr::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtmlAttr->expects(self::never())
            ->method('__invoke');

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

        $htmlElement = $this->getMockBuilder(HtmlElementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlElement->expects(self::never())
            ->method('toHtml');

        $helper = new Menu(
            $serviceLocator,
            $logger,
            $containerParser,
            $escapeHtmlAttr,
            $renderer,
            $escapeHtml,
            $htmlElement
        );

        self::assertFalse($helper->getOnlyActiveBranch());

        $helper->setOnlyActiveBranch(true);

        self::assertTrue($helper->getOnlyActiveBranch());
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

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $escapeHtmlAttr = $this->getMockBuilder(EscapeHtmlAttr::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtmlAttr->expects(self::never())
            ->method('__invoke');

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

        $htmlElement = $this->getMockBuilder(HtmlElementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlElement->expects(self::never())
            ->method('toHtml');

        $helper = new Menu(
            $serviceLocator,
            $logger,
            $containerParser,
            $escapeHtmlAttr,
            $renderer,
            $escapeHtml,
            $htmlElement
        );

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
    public function testSetRenderParents(): void
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

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $escapeHtmlAttr = $this->getMockBuilder(EscapeHtmlAttr::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtmlAttr->expects(self::never())
            ->method('__invoke');

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

        $htmlElement = $this->getMockBuilder(HtmlElementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlElement->expects(self::never())
            ->method('toHtml');

        $helper = new Menu(
            $serviceLocator,
            $logger,
            $containerParser,
            $escapeHtmlAttr,
            $renderer,
            $escapeHtml,
            $htmlElement
        );

        self::assertTrue($helper->getRenderParents());

        $helper->setRenderParents(false);

        self::assertFalse($helper->getRenderParents());
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testSetUlClass(): void
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

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $escapeHtmlAttr = $this->getMockBuilder(EscapeHtmlAttr::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtmlAttr->expects(self::never())
            ->method('__invoke');

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

        $htmlElement = $this->getMockBuilder(HtmlElementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlElement->expects(self::never())
            ->method('toHtml');

        $helper = new Menu(
            $serviceLocator,
            $logger,
            $containerParser,
            $escapeHtmlAttr,
            $renderer,
            $escapeHtml,
            $htmlElement
        );

        self::assertSame('navigation', $helper->getUlClass());

        $helper->setUlClass('test');

        self::assertSame('test', $helper->getUlClass());
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testSetLiActiveClass(): void
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

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $escapeHtmlAttr = $this->getMockBuilder(EscapeHtmlAttr::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtmlAttr->expects(self::never())
            ->method('__invoke');

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

        $htmlElement = $this->getMockBuilder(HtmlElementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlElement->expects(self::never())
            ->method('toHtml');

        $helper = new Menu(
            $serviceLocator,
            $logger,
            $containerParser,
            $escapeHtmlAttr,
            $renderer,
            $escapeHtml,
            $htmlElement
        );

        self::assertSame('active', $helper->getLiActiveClass());

        $helper->setLiActiveClass('test');

        self::assertSame('test', $helper->getLiActiveClass());
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
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

        $container = $this->createMock(AbstractContainer::class);

        $findActiveHelper = $this->getMockBuilder(FindActiveInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, 0, null)
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

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::exactly(3))
            ->method('parseContainer')
            ->withConsecutive([$container], [null], [$container])
            ->willReturnOnConsecutiveCalls($container, null, $container);

        $escapeHtmlAttr = $this->getMockBuilder(EscapeHtmlAttr::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtmlAttr->expects(self::never())
            ->method('__invoke');

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

        $htmlElement = $this->getMockBuilder(HtmlElementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlElement->expects(self::never())
            ->method('toHtml');

        $helper = new Menu(
            $serviceLocator,
            $logger,
            $containerParser,
            $escapeHtmlAttr,
            $renderer,
            $escapeHtml,
            $htmlElement
        );

        $helper->setContainer($container);

        self::assertSame('', $helper->render());
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
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

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $escapeHtmlAttr = $this->getMockBuilder(EscapeHtmlAttr::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtmlAttr->expects(self::never())
            ->method('__invoke');

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

        $htmlElement = $this->getMockBuilder(HtmlElementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlElement->expects(self::never())
            ->method('toHtml');

        $helper = new Menu(
            $serviceLocator,
            $logger,
            $containerParser,
            $escapeHtmlAttr,
            $renderer,
            $escapeHtml,
            $htmlElement
        );

        $role = 'testRole';

        $helper->setRole($role);

        $auth = $this->getMockBuilder(Acl::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isAllowed');

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to render menu: No partial view script provided');
        $this->expectExceptionCode(0);

        $helper->renderPartial($name);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
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

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $escapeHtmlAttr = $this->getMockBuilder(EscapeHtmlAttr::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtmlAttr->expects(self::never())
            ->method('__invoke');

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

        $htmlElement = $this->getMockBuilder(HtmlElementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlElement->expects(self::never())
            ->method('toHtml');

        $helper = new Menu(
            $serviceLocator,
            $logger,
            $containerParser,
            $escapeHtmlAttr,
            $renderer,
            $escapeHtml,
            $htmlElement
        );

        $role = 'testRole';

        $helper->setRole($role);

        $auth = $this->getMockBuilder(Acl::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isAllowed');

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        $helper->setPartial(['a', 'b', 'c']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to render menu: A view partial supplied as an array must contain one value: the partial view script');
        $this->expectExceptionCode(0);

        $helper->renderPartial($name);
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
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

        $role = 'testRole';

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
        $serviceLocator->expects(self::never())
            ->method('build');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        $escapeHtmlAttr = $this->getMockBuilder(EscapeHtmlAttr::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtmlAttr->expects(self::never())
            ->method('__invoke');

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::never())
            ->method('__invoke');

        $partial  = 'testPartial';
        $expected = 'renderedPartial';

        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::once())
            ->method('render')
            ->with($partial, ['container' => $container])
            ->willReturn($expected);
        $renderer->expects(self::never())
            ->method('plugin');
        $renderer->expects(self::never())
            ->method('getHelperPluginManager');

        $htmlElement = $this->getMockBuilder(HtmlElementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlElement->expects(self::never())
            ->method('toHtml');

        $helper = new Menu(
            $serviceLocator,
            $logger,
            $containerParser,
            $escapeHtmlAttr,
            $renderer,
            $escapeHtml,
            $htmlElement
        );

        $helper->setRole($role);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        $helper->setPartial($partial);

        self::assertSame($expected, $helper->renderPartial($name));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
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

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        $escapeHtmlAttr = $this->getMockBuilder(EscapeHtmlAttr::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtmlAttr->expects(self::never())
            ->method('__invoke');

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::never())
            ->method('__invoke');

        $partial  = 'testPartial';
        $expected = 'renderedPartial';

        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::once())
            ->method('render')
            ->with($partial, ['container' => $container])
            ->willReturn($expected);
        $renderer->expects(self::never())
            ->method('plugin');
        $renderer->expects(self::never())
            ->method('getHelperPluginManager');

        $htmlElement = $this->getMockBuilder(HtmlElementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlElement->expects(self::never())
            ->method('toHtml');

        $helper = new Menu(
            $serviceLocator,
            $logger,
            $containerParser,
            $escapeHtmlAttr,
            $renderer,
            $escapeHtml,
            $htmlElement
        );

        $helper->setRole($role);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        $helper->setPartial($partial);

        self::assertSame($expected, $helper->renderPartial($name));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
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

        $role = 'testRole';

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
        $serviceLocator->expects(self::never())
            ->method('build');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::exactly(2))
            ->method('parseContainer')
            ->withConsecutive([$container], [null])
            ->willReturnOnConsecutiveCalls($container, null);

        $escapeHtmlAttr = $this->getMockBuilder(EscapeHtmlAttr::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtmlAttr->expects(self::never())
            ->method('__invoke');

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::never())
            ->method('__invoke');

        $partial  = 'testPartial';
        $expected = 'renderedPartial';

        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::once())
            ->method('render')
            ->with($partial, ['container' => $container])
            ->willReturn($expected);
        $renderer->expects(self::never())
            ->method('plugin');
        $renderer->expects(self::never())
            ->method('getHelperPluginManager');

        $htmlElement = $this->getMockBuilder(HtmlElementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlElement->expects(self::never())
            ->method('toHtml');

        $helper = new Menu(
            $serviceLocator,
            $logger,
            $containerParser,
            $escapeHtmlAttr,
            $renderer,
            $escapeHtml,
            $htmlElement
        );

        $helper->setRole($role);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        $helper->setContainer($container);

        self::assertSame($expected, $helper->renderPartial(null, [$partial, 'test']));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
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
        $subPage->expects(self::never())
            ->method('getParent');
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
        $serviceLocator->expects(self::never())
            ->method('build');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::exactly(2))
            ->method('parseContainer')
            ->withConsecutive([$parentPage], [null])
            ->willReturnOnConsecutiveCalls($parentPage, null);

        $escapeHtmlAttr = $this->getMockBuilder(EscapeHtmlAttr::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtmlAttr->expects(self::never())
            ->method('__invoke');

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::never())
            ->method('__invoke');

        $expected = 'renderedPartial';
        $partial  = 'testPartial';

        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::once())
            ->method('render')
            ->with($partial, ['container' => $parentPage])
            ->willReturn($expected);
        $renderer->expects(self::never())
            ->method('plugin');
        $renderer->expects(self::never())
            ->method('getHelperPluginManager');

        $htmlElement = $this->getMockBuilder(HtmlElementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlElement->expects(self::never())
            ->method('toHtml');

        $helper = new Menu(
            $serviceLocator,
            $logger,
            $containerParser,
            $escapeHtmlAttr,
            $renderer,
            $escapeHtml,
            $htmlElement
        );

        $helper->setRole($role);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        $helper->setContainer($parentPage);

        self::assertSame($expected, $helper->renderPartial(null, [$partial, 'test']));
    }

    /**
     * @throws Exception
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     */
    public function testDoNotRenderMenuIfNoPageIsActive(): void
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

        $findActiveHelper = $this->getMockBuilder(FindActiveInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, 0, null)
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

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::exactly(3))
            ->method('parseContainer')
            ->withConsecutive([$container], [null], [$container])
            ->willReturnOnConsecutiveCalls($container, null, $container);

        $escapeHtmlAttr = $this->getMockBuilder(EscapeHtmlAttr::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtmlAttr->expects(self::never())
            ->method('__invoke');

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

        $htmlElement = $this->getMockBuilder(HtmlElementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlElement->expects(self::never())
            ->method('toHtml');

        $helper = new Menu(
            $serviceLocator,
            $logger,
            $containerParser,
            $escapeHtmlAttr,
            $renderer,
            $escapeHtml,
            $htmlElement
        );

        $helper->setContainer($container);

        self::assertSame('', $helper->renderMenu());
    }

    /**
     * @throws Exception
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     */
    public function testRenderMenuNoActivePage(): void
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
        $page->expects(self::once())
            ->method('hasPages')
            ->with(false)
            ->willReturn(false);
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
            ->with($container, 0, null)
            ->willReturn([]);

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
        $auth->expects(self::never())
            ->method('isAllowed');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::exactly(2))
            ->method('build')
            ->withConsecutive(
                [
                    FindActiveInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ]
            )
            ->willReturnOnConsecutiveCalls($findActiveHelper, $acceptHelper);

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::exactly(2))
            ->method('parseContainer')
            ->withConsecutive([$name], [$container])
            ->willReturnOnConsecutiveCalls($container, $container);

        $escapeHtmlAttr = $this->getMockBuilder(EscapeHtmlAttr::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtmlAttr->expects(self::never())
            ->method('__invoke');

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

        $htmlElement = $this->getMockBuilder(HtmlElementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlElement->expects(self::never())
            ->method('toHtml');

        $helper = new Menu(
            $serviceLocator,
            $logger,
            $containerParser,
            $escapeHtmlAttr,
            $renderer,
            $escapeHtml,
            $htmlElement
        );

        $helper->setRole($role);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        $expected = '';
        $partial  = 'testPartial';

        $helper->setPartial($partial);

        self::assertSame($expected, $helper->renderMenu($name));
    }

    /**
     * @throws Exception
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     */
    public function testRenderMenu(): void
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

        $parentLabel                  = 'parent-label';
        $parentTranslatedLabel        = 'parent-label-translated';
        $parentTranslatedLabelEscaped = 'parent-label-translated-escaped';
        $parentTextDomain             = 'parent-text-domain';
        $parentTitle                  = 'parent-title';
        $parentTranslatedTitle        = 'parent-title-translated';

        $pageLabel                  = 'page-label';
        $pageLabelTranslated        = 'page-label-translated';
        $pageLabelTranslatedEscaped = 'page-label-translated-escaped';
        $pageTitle                  = 'page-title';
        $pageTitleTranslated        = 'page-title-translated';
        $pageTextDomain             = 'page-text-domain';
        $pageId                     = 'page-id';
        $pageHref                   = 'http://page';
        $pageTarget                 = 'page-target';

        $parentPage = new Uri();
        $parentPage->setVisible(true);
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);
        $parentPage->setId('parent-id');
        $parentPage->setClass('parent-class');
        $parentPage->setUri('##');
        $parentPage->setTarget('self');
        $parentPage->setLabel($parentLabel);
        $parentPage->setTitle($parentTitle);
        $parentPage->setTextDomain($parentTextDomain);

        $page = $this->getMockBuilder(AbstractPage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::once())
            ->method('isVisible')
            ->with(false)
            ->willReturn(false);
        $page->expects(self::never())
            ->method('getResource');
        $page->expects(self::never())
            ->method('getPrivilege');
        $page->expects(self::never())
            ->method('getParent');
        $page->expects(self::exactly(2))
            ->method('isActive')
            ->with(true)
            ->willReturn(true);
        $page->expects(self::once())
            ->method('getLabel')
            ->willReturn($pageLabel);
        $page->expects(self::once())
            ->method('getTextDomain')
            ->willReturn($pageTextDomain);
        $page->expects(self::once())
            ->method('getTitle')
            ->willReturn($pageTitle);
        $page->expects(self::once())
            ->method('getId')
            ->willReturn($pageId);
        $page->expects(self::exactly(2))
            ->method('getClass')
            ->willReturn('xxxx');
        $page->expects(self::exactly(2))
            ->method('getHref')
            ->willReturn($pageHref);
        $page->expects(self::once())
            ->method('getTarget')
            ->willReturn($pageTarget);
        $page->expects(self::never())
            ->method('hasPage');
        $page->expects(self::exactly(2))
            ->method('hasPages')
            ->withConsecutive([false], [true])
            ->willReturnOnConsecutiveCalls(false, false);
        $page->expects(self::once())
            ->method('getCustomProperties')
            ->willReturn([]);
        $page->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive(['li-active-class'], ['li-class'])
            ->willReturnOnConsecutiveCalls(null, null);

        $parentPage->addPage($page);

        $container = new Navigation();
        $container->addPage($parentPage);

        $role = 'testRole';

        $findActiveHelper = $this->getMockBuilder(FindActiveInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, 0, null)
            ->willReturn(
                [
                    'page' => $page,
                    'depth' => 1,
                ]
            );

        $acceptHelper = $this->getMockBuilder(AcceptHelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $acceptHelper->expects(self::exactly(2))
            ->method('accept')
            ->withConsecutive([$parentPage], [$page])
            ->willReturnOnConsecutiveCalls(true, true);

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
        $serviceLocator->expects(self::exactly(3))
            ->method('build')
            ->withConsecutive(
                [
                    FindActiveInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ]
            )
            ->willReturnOnConsecutiveCalls(
                $findActiveHelper,
                $acceptHelper,
                $acceptHelper
            );

        $expected1 = '<a parent-id-escaped="parent-id-escaped" parent-title-escaped="parent-title-escaped" parent-class-escaped="parent-class-escaped" parent-href-escaped="##-escaped" parent-target-escaped="self-escaped">parent-label-escaped</a>';
        $expected2 = '<a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>';

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::exactly(2))
            ->method('parseContainer')
            ->withConsecutive([$name], [$container])
            ->willReturnOnConsecutiveCalls($container, $container);

        $escapeHtmlAttr = $this->getMockBuilder(EscapeHtmlAttr::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtmlAttr->expects(self::exactly(5))
            ->method('__invoke')
            ->withConsecutive(['nav navigation'], ['nav-item active'], ['dropdown-menu'], ['parent-id'], ['active'])
            ->willReturnOnConsecutiveCalls('nav-escaped navigation-escaped', 'nav-item-escaped active-escaped', 'dropdown-menu-escaped', 'parent-id-escaped', 'active-escaped');

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::exactly(2))
            ->method('__invoke')
            ->withConsecutive([$parentTranslatedLabel], [$pageLabelTranslated])
            ->willReturnOnConsecutiveCalls($parentTranslatedLabelEscaped, $pageLabelTranslatedEscaped);

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
        $translator->expects(self::exactly(4))
            ->method('translate')
            ->withConsecutive([$parentLabel, $parentTextDomain], [$parentTitle, $parentTextDomain], [$pageLabel, $pageTextDomain], [$pageTitle, $pageTextDomain])
            ->willReturnOnConsecutiveCalls($parentTranslatedLabel, $parentTranslatedTitle, $pageLabelTranslated, $pageTitleTranslated);

        $expected = '<ul class="nav-escaped navigation-escaped">' . PHP_EOL . '    <li class="nav-item-escaped active-escaped">' . PHP_EOL . '        <a parent-id-escaped="parent-id-escaped" parent-title-escaped="parent-title-escaped" parent-class-escaped="parent-class-escaped" parent-href-escaped="##-escaped" parent-target-escaped="self-escaped">parent-label-escaped</a>' . PHP_EOL . '        <ul class="dropdown-menu-escaped" aria-labelledby="parent-id-escaped">' . PHP_EOL . '            <li class="active-escaped">' . PHP_EOL . '                <a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>' . PHP_EOL . '            </li>' . PHP_EOL . '        </ul>' . PHP_EOL . '    </li>' . PHP_EOL . '</ul>';

        $htmlElement = $this->getMockBuilder(HtmlElementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlElement->expects(self::exactly(2))
            ->method('toHtml')
            ->withConsecutive(
                ['a', ['aria-current' => 'page', 'class' => 'nav-link parent-class', 'id' => 'parent-id', 'title' => $parentTranslatedTitle, 'href' => '##', 'target' => 'self'], $parentTranslatedLabelEscaped],
                ['a', ['class' => 'dropdown-item xxxx', 'id' => $pageId, 'title' => $pageTitleTranslated, 'href' => $pageHref, 'target' => $pageTarget], $pageLabelTranslatedEscaped]
            )
            ->willReturnOnConsecutiveCalls($expected1, $expected2);

        $helper = new Menu(
            $serviceLocator,
            $logger,
            $containerParser,
            $escapeHtmlAttr,
            $renderer,
            $escapeHtml,
            $htmlElement
        );

        $helper->setRole($role);
        $helper->setTranslator($translator);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        self::assertSame($expected, $helper->renderMenu($name));
    }

    /**
     * @throws Exception
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     */
    public function testRenderMenuWithIndent(): void
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

        $name = 'Mezzio\\Navigation\\Top';

        $resource  = 'testResource';
        $privilege = 'testPrivilege';

        $parentLabel                  = 'parent-label';
        $parentTranslatedLabel        = 'parent-label-translated';
        $parentTranslatedLabelEscaped = 'parent-label-translated-escaped';
        $parentTextDomain             = 'parent-text-domain';
        $parentTitle                  = 'parent-title';
        $parentTranslatedTitle        = 'parent-title-translated';

        $pageLabel                  = 'page-label';
        $pageLabelTranslated        = 'page-label-translated';
        $pageLabelTranslatedEscaped = 'page-label-translated-escaped';
        $pageTitle                  = 'page-title';
        $pageTitleTranslated        = 'page-title-translated';
        $pageTextDomain             = 'page-text-domain';
        $pageId                     = 'page-id';
        $pageHref                   = 'http://page';
        $pageTarget                 = 'page-target';

        $parentPage = new Uri();
        $parentPage->setVisible(true);
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);
        $parentPage->setId('parent-id');
        $parentPage->setClass('parent-class');
        $parentPage->setUri('##');
        $parentPage->setTarget('self');
        $parentPage->setLabel($parentLabel);
        $parentPage->setTitle($parentTitle);
        $parentPage->setTextDomain($parentTextDomain);

        $page = $this->getMockBuilder(AbstractPage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::once())
            ->method('isVisible')
            ->with(false)
            ->willReturn(false);
        $page->expects(self::never())
            ->method('getResource');
        $page->expects(self::never())
            ->method('getPrivilege');
        $page->expects(self::never())
            ->method('getParent');
        $page->expects(self::exactly(2))
            ->method('isActive')
            ->with(true)
            ->willReturn(true);
        $page->expects(self::once())
            ->method('getLabel')
            ->willReturn($pageLabel);
        $page->expects(self::once())
            ->method('getTextDomain')
            ->willReturn($pageTextDomain);
        $page->expects(self::once())
            ->method('getTitle')
            ->willReturn($pageTitle);
        $page->expects(self::once())
            ->method('getId')
            ->willReturn($pageId);
        $page->expects(self::exactly(2))
            ->method('getClass')
            ->willReturn('xxxx');
        $page->expects(self::exactly(2))
            ->method('getHref')
            ->willReturn($pageHref);
        $page->expects(self::once())
            ->method('getTarget')
            ->willReturn($pageTarget);
        $page->expects(self::never())
            ->method('hasPage');
        $page->expects(self::exactly(2))
            ->method('hasPages')
            ->withConsecutive([false], [true])
            ->willReturnOnConsecutiveCalls(false, false);
        $page->expects(self::once())
            ->method('getCustomProperties')
            ->willReturn([]);
        $page->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive(['li-active-class'], ['li-class'])
            ->willReturnOnConsecutiveCalls(null, null);

        $parentPage->addPage($page);

        $container = new Navigation();
        $container->addPage($parentPage);

        $role = 'testRole';

        $findActiveHelper = $this->getMockBuilder(FindActiveInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, 0, null)
            ->willReturn(
                [
                    'page' => $page,
                    'depth' => 1,
                ]
            );

        $acceptHelper = $this->getMockBuilder(AcceptHelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $acceptHelper->expects(self::exactly(2))
            ->method('accept')
            ->withConsecutive([$parentPage], [$page])
            ->willReturnOnConsecutiveCalls(true, true);

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
        $serviceLocator->expects(self::exactly(3))
            ->method('build')
            ->withConsecutive(
                [
                    FindActiveInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ]
            )
            ->willReturnOnConsecutiveCalls(
                $findActiveHelper,
                $acceptHelper,
                $acceptHelper
            );

        $expected1 = '<a parent-id-escaped="parent-id-escaped" parent-title-escaped="parent-title-escaped" parent-class-escaped="parent-class-escaped" parent-href-escaped="##-escaped" parent-target-escaped="self-escaped">parent-label-escaped</a>';
        $expected2 = '<a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>';

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::exactly(2))
            ->method('parseContainer')
            ->withConsecutive([$name], [$container])
            ->willReturnOnConsecutiveCalls($container, $container);

        $escapeHtmlAttr = $this->getMockBuilder(EscapeHtmlAttr::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtmlAttr->expects(self::exactly(5))
            ->method('__invoke')
            ->withConsecutive(['nav navigation'], ['nav-item active'], ['dropdown-menu'], ['parent-id'], ['active'])
            ->willReturnOnConsecutiveCalls('nav-escaped navigation-escaped', 'nav-item-escaped active-escaped', 'dropdown-menu-escaped', 'parent-id-escaped', 'active-escaped');

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::exactly(2))
            ->method('__invoke')
            ->withConsecutive([$parentTranslatedLabel], [$pageLabelTranslated])
            ->willReturnOnConsecutiveCalls($parentTranslatedLabelEscaped, $pageLabelTranslatedEscaped);

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
        $translator->expects(self::exactly(4))
            ->method('translate')
            ->withConsecutive([$parentLabel, $parentTextDomain], [$parentTitle, $parentTextDomain], [$pageLabel, $pageTextDomain], [$pageTitle, $pageTextDomain])
            ->willReturnOnConsecutiveCalls($parentTranslatedLabel, $parentTranslatedTitle, $pageLabelTranslated, $pageTitleTranslated);

        $expected = $indent . '<ul class="nav-escaped navigation-escaped">' . PHP_EOL . $indent . '    <li class="nav-item-escaped active-escaped">' . PHP_EOL . $indent . '        <a parent-id-escaped="parent-id-escaped" parent-title-escaped="parent-title-escaped" parent-class-escaped="parent-class-escaped" parent-href-escaped="##-escaped" parent-target-escaped="self-escaped">parent-label-escaped</a>' . PHP_EOL . $indent . '        <ul class="dropdown-menu-escaped" aria-labelledby="parent-id-escaped">' . PHP_EOL . $indent . '            <li class="active-escaped">' . PHP_EOL . $indent . '                <a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>' . PHP_EOL . $indent . '            </li>' . PHP_EOL . $indent . '        </ul>' . PHP_EOL . $indent . '    </li>' . PHP_EOL . $indent . '</ul>';

        $htmlElement = $this->getMockBuilder(HtmlElementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlElement->expects(self::exactly(2))
            ->method('toHtml')
            ->withConsecutive(
                ['a', ['aria-current' => 'page', 'class' => 'nav-link parent-class', 'id' => 'parent-id', 'title' => $parentTranslatedTitle, 'href' => '##', 'target' => 'self'], $parentTranslatedLabelEscaped],
                ['a', ['class' => 'dropdown-item xxxx', 'id' => $pageId, 'title' => $pageTitleTranslated, 'href' => $pageHref, 'target' => $pageTarget], $pageLabelTranslatedEscaped]
            )
            ->willReturnOnConsecutiveCalls($expected1, $expected2);

        $helper = new Menu(
            $serviceLocator,
            $logger,
            $containerParser,
            $escapeHtmlAttr,
            $renderer,
            $escapeHtml,
            $htmlElement
        );

        $helper->setRole($role);
        $helper->setTranslator($translator);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);
        $helper->setIndent($indent);

        self::assertSame($expected, $helper->renderMenu($name));
    }

    /**
     * @throws Exception
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     */
    public function testRenderVerticalMenuException(): void
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

        $parentLabel      = 'parent-label';
        $parentTextDomain = 'parent-text-domain';
        $parentTitle      = 'parent-title';

        $parentPage = new Uri();
        $parentPage->setVisible(true);
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);
        $parentPage->setId('parent-id');
        $parentPage->setClass('parent-class');
        $parentPage->setUri('##');
        $parentPage->setTarget('self');
        $parentPage->setLabel($parentLabel);
        $parentPage->setTitle($parentTitle);
        $parentPage->setTextDomain($parentTextDomain);

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

        $role = 'testRole';

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
        $serviceLocator->expects(self::never())
            ->method('build');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        $escapeHtmlAttr = $this->getMockBuilder(EscapeHtmlAttr::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtmlAttr->expects(self::never())
            ->method('__invoke');

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

        $htmlElement = $this->getMockBuilder(HtmlElementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlElement->expects(self::never())
            ->method('toHtml');

        $helper = new Menu(
            $serviceLocator,
            $logger,
            $containerParser,
            $escapeHtmlAttr,
            $renderer,
            $escapeHtml,
            $htmlElement
        );

        $helper->setRole($role);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Size "xy" does not exist');
        $this->expectExceptionCode(0);

        $helper->renderMenu($name, ['vertical' => 'xy']);
    }

    /**
     * @throws Exception
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     */
    public function testRenderVerticalMenu(): void
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

        $parentLabel                  = 'parent-label';
        $parentTranslatedLabel        = 'parent-label-translated';
        $parentTranslatedLabelEscaped = 'parent-label-translated-escaped';
        $parentTextDomain             = 'parent-text-domain';
        $parentTitle                  = 'parent-title';
        $parentTranslatedTitle        = 'parent-title-translated';

        $pageLabel                  = 'page-label';
        $pageLabelTranslated        = 'page-label-translated';
        $pageLabelTranslatedEscaped = 'page-label-translated-escaped';
        $pageTitle                  = 'page-title';
        $pageTitleTranslated        = 'page-title-translated';
        $pageTextDomain             = 'page-text-domain';
        $pageId                     = 'page-id';
        $pageHref                   = 'http://page';
        $pageTarget                 = 'page-target';

        $parentPage = new Uri();
        $parentPage->setVisible(true);
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);
        $parentPage->setId('parent-id');
        $parentPage->setClass('parent-class');
        $parentPage->setUri('##');
        $parentPage->setTarget('self');
        $parentPage->setLabel($parentLabel);
        $parentPage->setTitle($parentTitle);
        $parentPage->setTextDomain($parentTextDomain);

        $page = $this->getMockBuilder(AbstractPage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::once())
            ->method('isVisible')
            ->with(false)
            ->willReturn(false);
        $page->expects(self::never())
            ->method('getResource');
        $page->expects(self::never())
            ->method('getPrivilege');
        $page->expects(self::never())
            ->method('getParent');
        $page->expects(self::exactly(2))
            ->method('isActive')
            ->with(true)
            ->willReturn(true);
        $page->expects(self::once())
            ->method('getLabel')
            ->willReturn($pageLabel);
        $page->expects(self::once())
            ->method('getTextDomain')
            ->willReturn($pageTextDomain);
        $page->expects(self::once())
            ->method('getTitle')
            ->willReturn($pageTitle);
        $page->expects(self::once())
            ->method('getId')
            ->willReturn($pageId);
        $page->expects(self::exactly(2))
            ->method('getClass')
            ->willReturn('xxxx');
        $page->expects(self::exactly(2))
            ->method('getHref')
            ->willReturn($pageHref);
        $page->expects(self::once())
            ->method('getTarget')
            ->willReturn($pageTarget);
        $page->expects(self::never())
            ->method('hasPage');
        $page->expects(self::exactly(2))
            ->method('hasPages')
            ->withConsecutive([false], [true])
            ->willReturnOnConsecutiveCalls(false, false);
        $page->expects(self::once())
            ->method('getCustomProperties')
            ->willReturn([]);
        $page->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive(['li-active-class'], ['li-class'])
            ->willReturnOnConsecutiveCalls(null, null);

        $parentPage->addPage($page);

        $container = new Navigation();
        $container->addPage($parentPage);

        $role = 'testRole';

        $findActiveHelper = $this->getMockBuilder(FindActiveInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, 0, null)
            ->willReturn(
                [
                    'page' => $page,
                    'depth' => 1,
                ]
            );

        $acceptHelper = $this->getMockBuilder(AcceptHelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $acceptHelper->expects(self::exactly(2))
            ->method('accept')
            ->withConsecutive([$parentPage], [$page])
            ->willReturnOnConsecutiveCalls(true, true);

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
        $serviceLocator->expects(self::exactly(3))
            ->method('build')
            ->withConsecutive(
                [
                    FindActiveInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ]
            )
            ->willReturnOnConsecutiveCalls(
                $findActiveHelper,
                $acceptHelper,
                $acceptHelper
            );

        $expected1 = '<a parent-id-escaped="parent-id-escaped" parent-title-escaped="parent-title-escaped" parent-class-escaped="parent-class-escaped" parent-href-escaped="##-escaped" parent-target-escaped="self-escaped">parent-label-escaped</a>';
        $expected2 = '<a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>';

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::exactly(2))
            ->method('parseContainer')
            ->withConsecutive([$name], [$container])
            ->willReturn($container, $container);

        $escapeHtmlAttr = $this->getMockBuilder(EscapeHtmlAttr::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtmlAttr->expects(self::exactly(5))
            ->method('__invoke')
            ->withConsecutive(['nav navigation flex-column flex-md-row'], ['nav-item active'], ['dropdown-menu'], ['parent-id'], ['active'])
            ->willReturnOnConsecutiveCalls('nav-escaped navigation-escaped flex-column-escaped flex-md-row-escaped', 'nav-item-escaped active-escaped', 'dropdown-menu-escaped', 'parent-id-escaped', 'active-escaped');

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::exactly(2))
            ->method('__invoke')
            ->withConsecutive([$parentTranslatedLabel], [$pageLabelTranslated])
            ->willReturnOnConsecutiveCalls($parentTranslatedLabelEscaped, $pageLabelTranslatedEscaped);

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
        $translator->expects(self::exactly(4))
            ->method('translate')
            ->withConsecutive([$parentLabel, $parentTextDomain], [$parentTitle, $parentTextDomain], [$pageLabel, $pageTextDomain], [$pageTitle, $pageTextDomain])
            ->willReturnOnConsecutiveCalls($parentTranslatedLabel, $parentTranslatedTitle, $pageLabelTranslated, $pageTitleTranslated);

        $expected = '<ul class="nav-escaped navigation-escaped flex-column-escaped flex-md-row-escaped">' . PHP_EOL . '    <li class="nav-item-escaped active-escaped">' . PHP_EOL . '        <a parent-id-escaped="parent-id-escaped" parent-title-escaped="parent-title-escaped" parent-class-escaped="parent-class-escaped" parent-href-escaped="##-escaped" parent-target-escaped="self-escaped">parent-label-escaped</a>' . PHP_EOL . '        <ul class="dropdown-menu-escaped" aria-labelledby="parent-id-escaped">' . PHP_EOL . '            <li class="active-escaped">' . PHP_EOL . '                <a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>' . PHP_EOL . '            </li>' . PHP_EOL . '        </ul>' . PHP_EOL . '    </li>' . PHP_EOL . '</ul>';

        $htmlElement = $this->getMockBuilder(HtmlElementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlElement->expects(self::exactly(2))
            ->method('toHtml')
            ->withConsecutive(
                ['a', ['aria-current' => 'page', 'class' => 'nav-link parent-class', 'id' => 'parent-id', 'title' => $parentTranslatedTitle, 'href' => '##', 'target' => 'self'], $parentTranslatedLabelEscaped],
                ['a', ['class' => 'dropdown-item xxxx', 'id' => $pageId, 'title' => $pageTitleTranslated, 'href' => $pageHref, 'target' => $pageTarget], $pageLabelTranslatedEscaped]
            )
            ->willReturnOnConsecutiveCalls($expected1, $expected2);

        $helper = new Menu(
            $serviceLocator,
            $logger,
            $containerParser,
            $escapeHtmlAttr,
            $renderer,
            $escapeHtml,
            $htmlElement
        );

        $helper->setRole($role);
        $helper->setTranslator($translator);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        self::assertSame($expected, $helper->renderMenu($name, ['vertical' => 'md']));
    }

    /**
     * @throws Exception
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     */
    public function testRenderVerticalMenu2(): void
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

        $parentLabel                  = 'parent-label';
        $parentTranslatedLabel        = 'parent-label-translated';
        $parentTranslatedLabelEscaped = 'parent-label-translated-escaped';
        $parentTextDomain             = 'parent-text-domain';
        $parentTitle                  = 'parent-title';
        $parentTranslatedTitle        = 'parent-title-translated';

        $pageLabel                  = 'page-label';
        $pageLabelTranslated        = 'page-label-translated';
        $pageLabelTranslatedEscaped = 'page-label-translated-escaped';
        $pageTitle                  = 'page-title';
        $pageTitleTranslated        = 'page-title-translated';
        $pageTextDomain             = 'page-text-domain';
        $pageId                     = 'page-id';
        $pageHref                   = 'http://page';
        $pageTarget                 = 'page-target';

        $parentPage = new Uri();
        $parentPage->setVisible(true);
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);
        $parentPage->setId('parent-id');
        $parentPage->setClass('parent-class');
        $parentPage->setUri('##');
        $parentPage->setTarget('self');
        $parentPage->setLabel($parentLabel);
        $parentPage->setTitle($parentTitle);
        $parentPage->setTextDomain($parentTextDomain);

        $page = $this->getMockBuilder(AbstractPage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::once())
            ->method('isVisible')
            ->with(false)
            ->willReturn(false);
        $page->expects(self::never())
            ->method('getResource');
        $page->expects(self::never())
            ->method('getPrivilege');
        $page->expects(self::never())
            ->method('getParent');
        $page->expects(self::exactly(2))
            ->method('isActive')
            ->with(true)
            ->willReturn(true);
        $page->expects(self::once())
            ->method('getLabel')
            ->willReturn($pageLabel);
        $page->expects(self::once())
            ->method('getTextDomain')
            ->willReturn($pageTextDomain);
        $page->expects(self::once())
            ->method('getTitle')
            ->willReturn($pageTitle);
        $page->expects(self::once())
            ->method('getId')
            ->willReturn($pageId);
        $page->expects(self::exactly(2))
            ->method('getClass')
            ->willReturn('xxxx');
        $page->expects(self::exactly(2))
            ->method('getHref')
            ->willReturn($pageHref);
        $page->expects(self::once())
            ->method('getTarget')
            ->willReturn($pageTarget);
        $page->expects(self::never())
            ->method('hasPage');
        $page->expects(self::exactly(2))
            ->method('hasPages')
            ->withConsecutive([false], [true])
            ->willReturnOnConsecutiveCalls(false, false);
        $page->expects(self::once())
            ->method('getCustomProperties')
            ->willReturn([]);
        $page->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive(['li-active-class'], ['li-class'])
            ->willReturnOnConsecutiveCalls(null, null);

        $parentPage->addPage($page);

        $container = new Navigation();
        $container->addPage($parentPage);

        $role = 'testRole';

        $findActiveHelper = $this->getMockBuilder(FindActiveInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, 0, null)
            ->willReturn(
                [
                    'page' => $page,
                    'depth' => 1,
                ]
            );

        $acceptHelper = $this->getMockBuilder(AcceptHelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $acceptHelper->expects(self::exactly(2))
            ->method('accept')
            ->withConsecutive([$parentPage], [$page])
            ->willReturnOnConsecutiveCalls(true, true);

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
        $serviceLocator->expects(self::exactly(3))
            ->method('build')
            ->withConsecutive(
                [
                    FindActiveInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ]
            )
            ->willReturnOnConsecutiveCalls(
                $findActiveHelper,
                $acceptHelper,
                $acceptHelper
            );

        $expected1 = '<a parent-id-escaped="parent-id-escaped" parent-title-escaped="parent-title-escaped" parent-class-escaped="parent-class-escaped" parent-href-escaped="##-escaped" parent-target-escaped="self-escaped">parent-label-escaped</a>';
        $expected2 = '<a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>';

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::exactly(2))
            ->method('parseContainer')
            ->withConsecutive([$name], [$container])
            ->willReturn($container, $container);

        $escapeHtmlAttr = $this->getMockBuilder(EscapeHtmlAttr::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtmlAttr->expects(self::exactly(5))
            ->method('__invoke')
            ->withConsecutive(['nav navigation flex-column flex-md-row'], ['nav-item active'], ['dropdown-menu'], ['parent-id'], ['active'])
            ->willReturnOnConsecutiveCalls('nav-escaped navigation-escaped flex-column-escaped flex-md-row-escaped', 'nav-item-escaped active-escaped', 'dropdown-menu-escaped', 'parent-id-escaped', 'active-escaped');

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::exactly(2))
            ->method('__invoke')
            ->withConsecutive([$parentTranslatedLabel], [$pageLabelTranslated])
            ->willReturnOnConsecutiveCalls($parentTranslatedLabelEscaped, $pageLabelTranslatedEscaped);

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
        $translator->expects(self::exactly(4))
            ->method('translate')
            ->withConsecutive([$parentLabel, $parentTextDomain], [$parentTitle, $parentTextDomain], [$pageLabel, $pageTextDomain], [$pageTitle, $pageTextDomain])
            ->willReturnOnConsecutiveCalls($parentTranslatedLabel, $parentTranslatedTitle, $pageLabelTranslated, $pageTitleTranslated);

        $expected = '<ul class="nav-escaped navigation-escaped flex-column-escaped flex-md-row-escaped">' . PHP_EOL . '    <li class="nav-item-escaped active-escaped">' . PHP_EOL . '        <a parent-id-escaped="parent-id-escaped" parent-title-escaped="parent-title-escaped" parent-class-escaped="parent-class-escaped" parent-href-escaped="##-escaped" parent-target-escaped="self-escaped">parent-label-escaped</a>' . PHP_EOL . '        <ul class="dropdown-menu-escaped" aria-labelledby="parent-id-escaped">' . PHP_EOL . '            <li class="active-escaped">' . PHP_EOL . '                <a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>' . PHP_EOL . '            </li>' . PHP_EOL . '        </ul>' . PHP_EOL . '    </li>' . PHP_EOL . '</ul>';

        $htmlElement = $this->getMockBuilder(HtmlElementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlElement->expects(self::exactly(2))
            ->method('toHtml')
            ->withConsecutive(
                ['a', ['aria-current' => 'page', 'class' => 'nav-link parent-class', 'id' => 'parent-id', 'title' => $parentTranslatedTitle, 'href' => '##', 'target' => 'self'], $parentTranslatedLabelEscaped],
                ['a', ['class' => 'dropdown-item xxxx', 'id' => $pageId, 'title' => $pageTitleTranslated, 'href' => $pageHref, 'target' => $pageTarget], $pageLabelTranslatedEscaped]
            )
            ->willReturnOnConsecutiveCalls($expected1, $expected2);

        $helper = new Menu(
            $serviceLocator,
            $logger,
            $containerParser,
            $escapeHtmlAttr,
            $renderer,
            $escapeHtml,
            $htmlElement
        );

        $helper->setRole($role);
        $helper->setTranslator($translator);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        self::assertSame($expected, $helper->renderMenu($name, ['vertical' => 'md', 'direction' => Menu::DROP_ORIENTATION_START]));
    }

    /**
     * @throws Exception
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     */
    public function testRenderOlMenuWithMaxDepth(): void
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
        $maxDepth  = 1;

        $parentLabel                  = 'parent-label';
        $parentTranslatedLabel        = 'parent-label-translated';
        $parentTranslatedLabelEscaped = 'parent-label-translated-escaped';
        $parentTextDomain             = 'parent-text-domain';
        $parentTitle                  = 'parent-title';
        $parentTranslatedTitle        = 'parent-title-translated';

        $pageLabel                  = 'page-label';
        $pageLabelTranslated        = 'page-label-translated';
        $pageLabelTranslatedEscaped = 'page-label-translated-escaped';
        $pageTitle                  = 'page-title';
        $pageTitleTranslated        = 'page-title-translated';
        $pageTextDomain             = 'page-text-domain';
        $pageId                     = 'page-id';
        $pageHref                   = 'http://page';
        $pageTarget                 = 'page-target';

        $parentPage = new Uri();
        $parentPage->setVisible(true);
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);
        $parentPage->setId('parent-id');
        $parentPage->setClass('parent-class');
        $parentPage->setUri('##');
        $parentPage->setTarget('self');
        $parentPage->setLabel($parentLabel);
        $parentPage->setTitle($parentTitle);
        $parentPage->setTextDomain($parentTextDomain);
        //$parentPage->set('li-class', 'li-class');

        $page = $this->getMockBuilder(AbstractPage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::once())
            ->method('isVisible')
            ->with(false)
            ->willReturn(false);
        $page->expects(self::never())
            ->method('getResource');
        $page->expects(self::never())
            ->method('getPrivilege');
        $page->expects(self::never())
            ->method('getParent');
        $page->expects(self::exactly(2))
            ->method('isActive')
            ->with(true)
            ->willReturn(true);
        $page->expects(self::once())
            ->method('getLabel')
            ->willReturn($pageLabel);
        $page->expects(self::once())
            ->method('getTextDomain')
            ->willReturn($pageTextDomain);
        $page->expects(self::once())
            ->method('getTitle')
            ->willReturn($pageTitle);
        $page->expects(self::once())
            ->method('getId')
            ->willReturn($pageId);
        $page->expects(self::exactly(2))
            ->method('getClass')
            ->willReturn('xxxx');
        $page->expects(self::exactly(2))
            ->method('getHref')
            ->willReturn($pageHref);
        $page->expects(self::once())
            ->method('getTarget')
            ->willReturn($pageTarget);
        $page->expects(self::never())
            ->method('hasPage');
        $page->expects(self::exactly(2))
            ->method('hasPages')
            ->withConsecutive([false], [true])
            ->willReturnOnConsecutiveCalls(false, false);
        $page->expects(self::once())
            ->method('getCustomProperties')
            ->willReturn([]);
        $page->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive(['li-active-class'], ['li-class'])
            ->willReturnOnConsecutiveCalls('li-class', null);

        $parentPage->addPage($page);

        $container = new Navigation();
        $container->addPage($parentPage);

        $role = 'testRole';

        $findActiveHelper = $this->getMockBuilder(FindActiveInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, 0, $maxDepth)
            ->willReturn(
                [
                    'page' => $page,
                    'depth' => 1,
                ]
            );

        $acceptHelper = $this->getMockBuilder(AcceptHelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $acceptHelper->expects(self::exactly(2))
            ->method('accept')
            ->withConsecutive([$parentPage], [$page])
            ->willReturnOnConsecutiveCalls(true, true);

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
        $serviceLocator->expects(self::exactly(3))
            ->method('build')
            ->withConsecutive(
                [
                    FindActiveInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ]
            )
            ->willReturnOnConsecutiveCalls(
                $findActiveHelper,
                $acceptHelper,
                $acceptHelper
            );

        $expected1 = '<a parent-id-escaped="parent-id-escaped" parent-title-escaped="parent-title-escaped" parent-class-escaped="parent-class-escaped" parent-href-escaped="##-escaped" parent-target-escaped="self-escaped">parent-label-escaped</a>';
        $expected2 = '<a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>';

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::exactly(2))
            ->method('parseContainer')
            ->withConsecutive([$name], [$container])
            ->willReturnOnConsecutiveCalls($container, $container);

        $escapeHtmlAttr = $this->getMockBuilder(EscapeHtmlAttr::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtmlAttr->expects(self::exactly(5))
            ->method('__invoke')
            ->withConsecutive(['nav navigation'], ['nav-item active'], ['dropdown-menu'], ['parent-id'], ['active li-class'])
            ->willReturnOnConsecutiveCalls('nav-escaped navigation-escaped', 'nav-item-escaped active-escaped', 'dropdown-menu-escaped', 'parent-id-escaped', 'active-escaped li-class-escaped');

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::exactly(2))
            ->method('__invoke')
            ->withConsecutive([$parentTranslatedLabel], [$pageLabelTranslated])
            ->willReturnOnConsecutiveCalls($parentTranslatedLabelEscaped, $pageLabelTranslatedEscaped);

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
        $translator->expects(self::exactly(4))
            ->method('translate')
            ->withConsecutive([$parentLabel, $parentTextDomain], [$parentTitle, $parentTextDomain], [$pageLabel, $pageTextDomain], [$pageTitle, $pageTextDomain])
            ->willReturnOnConsecutiveCalls($parentTranslatedLabel, $parentTranslatedTitle, $pageLabelTranslated, $pageTitleTranslated);

        $expected = '<ol class="nav-escaped navigation-escaped">' . PHP_EOL . '    <li class="nav-item-escaped active-escaped">' . PHP_EOL . '        <a parent-id-escaped="parent-id-escaped" parent-title-escaped="parent-title-escaped" parent-class-escaped="parent-class-escaped" parent-href-escaped="##-escaped" parent-target-escaped="self-escaped">parent-label-escaped</a>' . PHP_EOL . '        <ol class="dropdown-menu-escaped" aria-labelledby="parent-id-escaped">' . PHP_EOL . '            <li class="active-escaped li-class-escaped">' . PHP_EOL . '                <a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>' . PHP_EOL . '            </li>' . PHP_EOL . '        </ol>' . PHP_EOL . '    </li>' . PHP_EOL . '</ol>';

        $htmlElement = $this->getMockBuilder(HtmlElementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlElement->expects(self::exactly(2))
            ->method('toHtml')
            ->withConsecutive(
                ['a', ['aria-current' => 'page', 'class' => 'nav-link parent-class', 'id' => 'parent-id', 'title' => $parentTranslatedTitle, 'href' => '##', 'target' => 'self'], $parentTranslatedLabelEscaped],
                ['a', ['class' => 'dropdown-item xxxx', 'id' => $pageId, 'title' => $pageTitleTranslated, 'href' => $pageHref, 'target' => $pageTarget], $pageLabelTranslatedEscaped]
            )
            ->willReturnOnConsecutiveCalls($expected1, $expected2);

        $helper = new Menu(
            $serviceLocator,
            $logger,
            $containerParser,
            $escapeHtmlAttr,
            $renderer,
            $escapeHtml,
            $htmlElement
        );

        $helper->setRole($role);
        $helper->setTranslator($translator);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        self::assertSame($expected, $helper->renderMenu($name, ['style' => Menu::STYLE_OL, 'maxDepth' => $maxDepth]));
    }

    /**
     * @throws Exception
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     */
    public function testRenderUlMenuWithTabs(): void
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

        $parentLabel                  = 'parent-label';
        $parentTranslatedLabel        = 'parent-label-translated';
        $parentTranslatedLabelEscaped = 'parent-label-translated-escaped';
        $parentTextDomain             = 'parent-text-domain';
        $parentTitle                  = 'parent-title';
        $parentTranslatedTitle        = 'parent-title-translated';

        $pageLabel                  = 'page-label';
        $pageLabelTranslated        = 'page-label-translated';
        $pageLabelTranslatedEscaped = 'page-label-translated-escaped';
        $pageTitle                  = 'page-title';
        $pageTitleTranslated        = 'page-title-translated';
        $pageTextDomain             = 'page-text-domain';
        $pageId                     = 'page-id';
        $pageHref                   = 'http://page';
        $pageTarget                 = 'page-target';

        $parentPage = new Uri();
        $parentPage->setVisible(true);
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);
        $parentPage->setId('parent-id');
        $parentPage->setClass('parent-class');
        $parentPage->setUri('##');
        $parentPage->setTarget('self');
        $parentPage->setLabel($parentLabel);
        $parentPage->setTitle($parentTitle);
        $parentPage->setTextDomain($parentTextDomain);

        $page = $this->getMockBuilder(AbstractPage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::once())
            ->method('isVisible')
            ->with(false)
            ->willReturn(false);
        $page->expects(self::never())
            ->method('getResource');
        $page->expects(self::never())
            ->method('getPrivilege');
        $page->expects(self::never())
            ->method('getParent');
        $page->expects(self::exactly(2))
            ->method('isActive')
            ->with(true)
            ->willReturn(true);
        $page->expects(self::once())
            ->method('getLabel')
            ->willReturn($pageLabel);
        $page->expects(self::once())
            ->method('getTextDomain')
            ->willReturn($pageTextDomain);
        $page->expects(self::once())
            ->method('getTitle')
            ->willReturn($pageTitle);
        $page->expects(self::once())
            ->method('getId')
            ->willReturn($pageId);
        $page->expects(self::exactly(2))
            ->method('getClass')
            ->willReturn('xxxx');
        $page->expects(self::exactly(2))
            ->method('getHref')
            ->willReturn($pageHref);
        $page->expects(self::once())
            ->method('getTarget')
            ->willReturn($pageTarget);
        $page->expects(self::never())
            ->method('hasPage');
        $page->expects(self::exactly(2))
            ->method('hasPages')
            ->withConsecutive([false], [true])
            ->willReturnOnConsecutiveCalls(false, false);
        $page->expects(self::once())
            ->method('getCustomProperties')
            ->willReturn([]);
        $page->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive(['li-active-class'], ['li-class'])
            ->willReturnOnConsecutiveCalls('li-class', null);

        $parentPage->addPage($page);

        $container = new Navigation();
        $container->addPage($parentPage);

        $role = 'testRole';

        $findActiveHelper = $this->getMockBuilder(FindActiveInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, 0, null)
            ->willReturn(
                [
                    'page' => $page,
                    'depth' => 1,
                ]
            );

        $acceptHelper = $this->getMockBuilder(AcceptHelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $acceptHelper->expects(self::exactly(2))
            ->method('accept')
            ->withConsecutive([$parentPage], [$page])
            ->willReturnOnConsecutiveCalls(true, true);

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
        $serviceLocator->expects(self::exactly(3))
            ->method('build')
            ->withConsecutive(
                [
                    FindActiveInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ]
            )
            ->willReturnOnConsecutiveCalls(
                $findActiveHelper,
                $acceptHelper,
                $acceptHelper
            );

        $expected1 = '<a parent-id-escaped="parent-id-escaped" parent-title-escaped="parent-title-escaped" parent-class-escaped="parent-class-escaped" parent-href-escaped="##-escaped" parent-target-escaped="self-escaped">parent-label-escaped</a>';
        $expected2 = '<a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>';

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::exactly(2))
            ->method('parseContainer')
            ->withConsecutive([$name], [$container])
            ->willReturnOnConsecutiveCalls($container, $container);

        $escapeHtmlAttr = $this->getMockBuilder(EscapeHtmlAttr::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtmlAttr->expects(self::exactly(7))
            ->method('__invoke')
            ->withConsecutive(['navbar-nav navigation nav-tabs'], ['tablist'], ['nav-item active'], ['presentation'], ['dropdown-menu dropdown-menu-dark'], ['parent-id'], ['active li-class'])
            ->willReturnOnConsecutiveCalls('navbar-nav-escaped navigation-escaped nav-tabs-escaped', 'tablist-escaped', 'nav-item-escaped active-escaped', 'presentation-escaped', 'dropdown-menu-escaped dropdown-menu-dark-escaped', 'parent-id-escaped', 'active-escaped li-class-escaped');

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::exactly(2))
            ->method('__invoke')
            ->withConsecutive([$parentTranslatedLabel], [$pageLabelTranslated])
            ->willReturnOnConsecutiveCalls($parentTranslatedLabelEscaped, $pageLabelTranslatedEscaped);

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
        $translator->expects(self::exactly(4))
            ->method('translate')
            ->withConsecutive([$parentLabel, $parentTextDomain], [$parentTitle, $parentTextDomain], [$pageLabel, $pageTextDomain], [$pageTitle, $pageTextDomain])
            ->willReturnOnConsecutiveCalls($parentTranslatedLabel, $parentTranslatedTitle, $pageLabelTranslated, $pageTitleTranslated);

        $expected = '<ul class="navbar-nav-escaped navigation-escaped nav-tabs-escaped" role="tablist-escaped">' . PHP_EOL . '    <li class="nav-item-escaped active-escaped" role="presentation-escaped">' . PHP_EOL . '        <a parent-id-escaped="parent-id-escaped" parent-title-escaped="parent-title-escaped" parent-class-escaped="parent-class-escaped" parent-href-escaped="##-escaped" parent-target-escaped="self-escaped">parent-label-escaped</a>' . PHP_EOL . '        <ul class="dropdown-menu-escaped dropdown-menu-dark-escaped" aria-labelledby="parent-id-escaped">' . PHP_EOL . '            <li class="active-escaped li-class-escaped">' . PHP_EOL . '                <a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>' . PHP_EOL . '            </li>' . PHP_EOL . '        </ul>' . PHP_EOL . '    </li>' . PHP_EOL . '</ul>';

        $htmlElement = $this->getMockBuilder(HtmlElementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlElement->expects(self::exactly(2))
            ->method('toHtml')
            ->withConsecutive(
                ['a', ['aria-current' => 'page', 'class' => 'nav-link parent-class', 'id' => 'parent-id', 'title' => $parentTranslatedTitle, 'href' => '##', 'target' => 'self', 'role' => 'tab'], $parentTranslatedLabelEscaped],
                ['a', ['class' => 'dropdown-item xxxx', 'id' => $pageId, 'title' => $pageTitleTranslated, 'href' => $pageHref, 'target' => $pageTarget], $pageLabelTranslatedEscaped]
            )
            ->willReturnOnConsecutiveCalls($expected1, $expected2);

        $helper = new Menu(
            $serviceLocator,
            $logger,
            $containerParser,
            $escapeHtmlAttr,
            $renderer,
            $escapeHtml,
            $htmlElement
        );

        $helper->setRole($role);
        $helper->setTranslator($translator);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        self::assertSame($expected, $helper->renderMenu($name, ['tabs' => true, 'dark' => true, 'in-navbar' => true]));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     */
    public function testRenderPartialWithPartialModel(): void
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
        $subPage->expects(self::never())
            ->method('getParent');
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
        $serviceLocator->expects(self::never())
            ->method('build');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::exactly(2))
            ->method('parseContainer')
            ->withConsecutive([$parentPage], [null])
            ->willReturnOnConsecutiveCalls($parentPage, null);

        $expected = 'renderedPartial';
        $data     = ['container' => $parentPage];

        $model = $this->getMockBuilder(ModelInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $model->expects(self::never())
            ->method('setVariables');
        $model->expects(self::never())
            ->method('getTemplate');

        $escapeHtmlAttr = $this->getMockBuilder(EscapeHtmlAttr::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtmlAttr->expects(self::never())
            ->method('__invoke');

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::never())
            ->method('__invoke');

        $renderer = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->expects(self::once())
            ->method('render')
            ->with($model, $data)
            ->willReturn($expected);
        $renderer->expects(self::never())
            ->method('plugin');
        $renderer->expects(self::never())
            ->method('getHelperPluginManager');

        $htmlElement = $this->getMockBuilder(HtmlElementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlElement->expects(self::never())
            ->method('toHtml');

        $helper = new Menu(
            $serviceLocator,
            $logger,
            $containerParser,
            $escapeHtmlAttr,
            $renderer,
            $escapeHtml,
            $htmlElement
        );

        $helper->setRole($role);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        $helper->setContainer($parentPage);

        self::assertSame($expected, $helper->renderPartial(null, $model));
    }

    /**
     * @throws Exception
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     */
    public function testRenderMenuWithTabsOnlyActiveBranchWithoutParents(): void
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

        $parentLabel      = 'parent-label';
        $parentTextDomain = 'parent-text-domain';
        $parentTitle      = 'parent-title';

        $pageLabel                  = 'page-label';
        $pageLabelTranslated        = 'page-label-translated';
        $pageLabelTranslatedEscaped = 'page-label-translated-escaped';
        $pageTitle                  = 'page-title';
        $pageTitleTranslated        = 'page-title-translated';
        $pageTextDomain             = 'page-text-domain';
        $pageId                     = 'page-id';
        $pageHref                   = 'http://page';
        $pageTarget                 = 'page-target';

        $parentPage = new Uri();
        $parentPage->setVisible(true);
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);
        $parentPage->setId('parent-id');
        $parentPage->setClass('parent-class');
        $parentPage->setUri('##');
        $parentPage->setTarget('self');
        $parentPage->setLabel($parentLabel);
        $parentPage->setTitle($parentTitle);
        $parentPage->setTextDomain($parentTextDomain);

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
            ->with(true)
            ->willReturn(true);
        $page->expects(self::once())
            ->method('getLabel')
            ->willReturn($pageLabel);
        $page->expects(self::once())
            ->method('getTextDomain')
            ->willReturn($pageTextDomain);
        $page->expects(self::once())
            ->method('getTitle')
            ->willReturn($pageTitle);
        $page->expects(self::once())
            ->method('getId')
            ->willReturn($pageId);
        $page->expects(self::exactly(2))
            ->method('getClass')
            ->willReturn('xxxx');
        $page->expects(self::exactly(2))
            ->method('getHref')
            ->willReturn($pageHref);
        $page->expects(self::once())
            ->method('getTarget')
            ->willReturn($pageTarget);
        $page->expects(self::never())
            ->method('hasPage');
        $page->expects(self::once())
            ->method('hasPages')
            ->with(true)
            ->willReturn(false);
        $page->expects(self::once())
            ->method('getCustomProperties')
            ->willReturn([]);
        $page->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive(['li-active-class'], ['li-class'])
            ->willReturnOnConsecutiveCalls(null, null);

        $parentPage->addPage($page);

        $container = new Navigation();
        $container->addPage($parentPage);

        $role = 'testRole';

        $findActiveHelper = $this->getMockBuilder(FindActiveInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, -1, null)
            ->willReturn(
                [
                    'page' => $page,
                    'depth' => 1,
                ]
            );

        $acceptHelper = $this->getMockBuilder(AcceptHelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $acceptHelper->expects(self::once())
            ->method('accept')
            ->with($page)
            ->willReturn(true);

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
        $serviceLocator->expects(self::exactly(2))
            ->method('build')
            ->withConsecutive(
                [
                    FindActiveInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ]
            )
            ->willReturnOnConsecutiveCalls(
                $findActiveHelper,
                $acceptHelper
            );

        $expected2 = '<a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>';

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::exactly(2))
            ->method('parseContainer')
            ->withConsecutive([$name], [$container])
            ->willReturnOnConsecutiveCalls($container, $container);

        $escapeHtmlAttr = $this->getMockBuilder(EscapeHtmlAttr::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtmlAttr->expects(self::exactly(4))
            ->method('__invoke')
            ->withConsecutive(['nav-item active'], ['presentation'], ['navbar-nav navigation nav-tabs'], ['tablist'])
            ->willReturnOnConsecutiveCalls('nav-item-escaped active-escaped', 'presentation-escaped', 'navbar-nav-escaped navigation-escaped nav-tabs-escaped', 'tablist-escaped');

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::once())
            ->method('__invoke')
            ->with($pageLabelTranslated)
            ->willReturn($pageLabelTranslatedEscaped);

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
        $translator->expects(self::exactly(2))
            ->method('translate')
            ->withConsecutive([$pageLabel, $pageTextDomain], [$pageTitle, $pageTextDomain])
            ->willReturnOnConsecutiveCalls($pageLabelTranslated, $pageTitleTranslated);

        $expected = '<ul class="navbar-nav-escaped navigation-escaped nav-tabs-escaped" role="tablist-escaped">' . PHP_EOL . '    <li class="nav-item-escaped active-escaped" role="presentation-escaped">' . PHP_EOL . '        <a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>' . PHP_EOL . '    </li>' . PHP_EOL . '</ul>';

        $htmlElement = $this->getMockBuilder(HtmlElementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlElement->expects(self::once())
            ->method('toHtml')
            ->with('a', ['aria-current' => 'page', 'class' => 'nav-link xxxx', 'id' => $pageId, 'title' => $pageTitleTranslated, 'href' => $pageHref, 'target' => $pageTarget, 'role' => 'tab'], $pageLabelTranslatedEscaped)
            ->willReturn($expected2);

        $helper = new Menu(
            $serviceLocator,
            $logger,
            $containerParser,
            $escapeHtmlAttr,
            $renderer,
            $escapeHtml,
            $htmlElement
        );

        $helper->setRole($role);
        $helper->setTranslator($translator);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        self::assertSame($expected, $helper->renderMenu($name, ['tabs' => true, 'dark' => true, 'in-navbar' => true, 'onlyActiveBranch' => true, 'renderParents' => false]));
    }

    /**
     * @throws Exception
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     */
    public function testRenderMenuWithTabsOnlyActiveBranchWithoutParentsWithIndent(): void
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

        $name = 'Mezzio\\Navigation\\Top';

        $resource  = 'testResource';
        $privilege = 'testPrivilege';

        $parentLabel      = 'parent-label';
        $parentTextDomain = 'parent-text-domain';
        $parentTitle      = 'parent-title';

        $pageLabel                  = 'page-label';
        $pageLabelTranslated        = 'page-label-translated';
        $pageLabelTranslatedEscaped = 'page-label-translated-escaped';
        $pageTitle                  = 'page-title';
        $pageTitleTranslated        = 'page-title-translated';
        $pageTextDomain             = 'page-text-domain';
        $pageId                     = 'page-id';
        $pageHref                   = 'http://page';
        $pageTarget                 = 'page-target';

        $parentPage = new Uri();
        $parentPage->setVisible(true);
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);
        $parentPage->setId('parent-id');
        $parentPage->setClass('parent-class');
        $parentPage->setUri('##');
        $parentPage->setTarget('self');
        $parentPage->setLabel($parentLabel);
        $parentPage->setTitle($parentTitle);
        $parentPage->setTextDomain($parentTextDomain);

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
            ->with(true)
            ->willReturn(true);
        $page->expects(self::once())
            ->method('getLabel')
            ->willReturn($pageLabel);
        $page->expects(self::once())
            ->method('getTextDomain')
            ->willReturn($pageTextDomain);
        $page->expects(self::once())
            ->method('getTitle')
            ->willReturn($pageTitle);
        $page->expects(self::once())
            ->method('getId')
            ->willReturn($pageId);
        $page->expects(self::exactly(2))
            ->method('getClass')
            ->willReturn('xxxx');
        $page->expects(self::exactly(2))
            ->method('getHref')
            ->willReturn($pageHref);
        $page->expects(self::once())
            ->method('getTarget')
            ->willReturn($pageTarget);
        $page->expects(self::never())
            ->method('hasPage');
        $page->expects(self::once())
            ->method('hasPages')
            ->with(true)
            ->willReturn(false);
        $page->expects(self::once())
            ->method('getCustomProperties')
            ->willReturn([]);
        $page->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive(['li-active-class'], ['li-class'])
            ->willReturnOnConsecutiveCalls(null, null);

        $parentPage->addPage($page);

        $container = new Navigation();
        $container->addPage($parentPage);

        $role = 'testRole';

        $findActiveHelper = $this->getMockBuilder(FindActiveInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, -1, null)
            ->willReturn(
                [
                    'page' => $page,
                    'depth' => 1,
                ]
            );

        $acceptHelper = $this->getMockBuilder(AcceptHelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $acceptHelper->expects(self::once())
            ->method('accept')
            ->with($page)
            ->willReturn(true);

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
        $serviceLocator->expects(self::exactly(2))
            ->method('build')
            ->withConsecutive(
                [
                    FindActiveInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ]
            )
            ->willReturnOnConsecutiveCalls(
                $findActiveHelper,
                $acceptHelper
            );

        $expected2 = '<a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>';

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::exactly(2))
            ->method('parseContainer')
            ->withConsecutive([$name], [$container])
            ->willReturnOnConsecutiveCalls($container, $container);

        $escapeHtmlAttr = $this->getMockBuilder(EscapeHtmlAttr::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtmlAttr->expects(self::exactly(4))
            ->method('__invoke')
            ->withConsecutive(['nav-item active'], ['presentation'], ['navbar-nav navigation nav-tabs'], ['tablist'])
            ->willReturnOnConsecutiveCalls('nav-item-escaped active-escaped', 'presentation-escaped', 'navbar-nav-escaped navigation-escaped nav-tabs-escaped', 'tablist-escaped');

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::once())
            ->method('__invoke')
            ->with($pageLabelTranslated)
            ->willReturn($pageLabelTranslatedEscaped);

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
        $translator->expects(self::exactly(2))
            ->method('translate')
            ->withConsecutive([$pageLabel, $pageTextDomain], [$pageTitle, $pageTextDomain])
            ->willReturnOnConsecutiveCalls($pageLabelTranslated, $pageTitleTranslated);

        $expected = $indent . '<ul class="navbar-nav-escaped navigation-escaped nav-tabs-escaped" role="tablist-escaped">' . PHP_EOL . $indent . '    <li class="nav-item-escaped active-escaped" role="presentation-escaped">' . PHP_EOL . $indent . '        <a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>' . PHP_EOL . $indent . '    </li>' . PHP_EOL . $indent . '</ul>';

        $htmlElement = $this->getMockBuilder(HtmlElementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlElement->expects(self::once())
            ->method('toHtml')
            ->with('a', ['aria-current' => 'page', 'class' => 'nav-link xxxx', 'id' => $pageId, 'title' => $pageTitleTranslated, 'href' => $pageHref, 'target' => $pageTarget, 'role' => 'tab'], $pageLabelTranslatedEscaped)
            ->willReturn($expected2);

        $helper = new Menu(
            $serviceLocator,
            $logger,
            $containerParser,
            $escapeHtmlAttr,
            $renderer,
            $escapeHtml,
            $htmlElement
        );

        $helper->setRole($role);
        $helper->setTranslator($translator);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);
        $helper->setIndent($indent);

        self::assertSame($expected, $helper->renderMenu($name, ['tabs' => true, 'dark' => true, 'in-navbar' => true, 'onlyActiveBranch' => true, 'renderParents' => false]));
    }

    /**
     * @throws Exception
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     */
    public function testRenderSubMenuWithTabsOnlyActiveBranchWithoutParents(): void
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

        $parentLabel      = 'parent-label';
        $parentTextDomain = 'parent-text-domain';
        $parentTitle      = 'parent-title';

        $pageLabel                  = 'page-label';
        $pageLabelTranslated        = 'page-label-translated';
        $pageLabelTranslatedEscaped = 'page-label-translated-escaped';
        $pageTitle                  = 'page-title';
        $pageTitleTranslated        = 'page-title-translated';
        $pageTextDomain             = 'page-text-domain';
        $pageId                     = 'page-id';
        $pageHref                   = 'http://page';
        $pageTarget                 = 'page-target';

        $parentPage = new Uri();
        $parentPage->setVisible(true);
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);
        $parentPage->setId('parent-id');
        $parentPage->setClass('parent-class');
        $parentPage->setUri('##');
        $parentPage->setTarget('self');
        $parentPage->setLabel($parentLabel);
        $parentPage->setTitle($parentTitle);
        $parentPage->setTextDomain($parentTextDomain);

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
            ->with(true)
            ->willReturn(true);
        $page->expects(self::once())
            ->method('getLabel')
            ->willReturn($pageLabel);
        $page->expects(self::once())
            ->method('getTextDomain')
            ->willReturn($pageTextDomain);
        $page->expects(self::once())
            ->method('getTitle')
            ->willReturn($pageTitle);
        $page->expects(self::once())
            ->method('getId')
            ->willReturn($pageId);
        $page->expects(self::exactly(2))
            ->method('getClass')
            ->willReturn('xxxx');
        $page->expects(self::exactly(2))
            ->method('getHref')
            ->willReturn($pageHref);
        $page->expects(self::once())
            ->method('getTarget')
            ->willReturn($pageTarget);
        $page->expects(self::never())
            ->method('hasPage');
        $page->expects(self::once())
            ->method('hasPages')
            ->with(true)
            ->willReturn(false);
        $page->expects(self::once())
            ->method('getCustomProperties')
            ->willReturn([]);
        $page->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive(['li-active-class'], ['li-class'])
            ->willReturnOnConsecutiveCalls('li-class', null);

        $parentPage->addPage($page);

        $container = new Navigation();
        $container->addPage($parentPage);

        $role = 'testRole';

        $findActiveHelper = $this->getMockBuilder(FindActiveInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, -1, null)
            ->willReturn(
                [
                    'page' => $page,
                    'depth' => 1,
                ]
            );

        $acceptHelper = $this->getMockBuilder(AcceptHelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $acceptHelper->expects(self::once())
            ->method('accept')
            ->with($page)
            ->willReturn(true);

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
        $serviceLocator->expects(self::exactly(2))
            ->method('build')
            ->withConsecutive(
                [
                    FindActiveInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ]
            )
            ->willReturnOnConsecutiveCalls(
                $findActiveHelper,
                $acceptHelper
            );

        $expected2 = '<a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>';

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::exactly(2))
            ->method('parseContainer')
            ->withConsecutive([$container], [$container])
            ->willReturnOnConsecutiveCalls($container, $container);

        $escapeHtmlAttr = $this->getMockBuilder(EscapeHtmlAttr::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtmlAttr->expects(self::exactly(2))
            ->method('__invoke')
            ->withConsecutive(['nav-item active li-class'], ['nav navigation'])
            ->willReturnOnConsecutiveCalls('nav-item-escaped active-escaped li-class-escaped', 'nav-escaped navigation-escaped');

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::once())
            ->method('__invoke')
            ->with($pageLabelTranslated)
            ->willReturn($pageLabelTranslatedEscaped);

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
        $translator->expects(self::exactly(2))
            ->method('translate')
            ->withConsecutive([$pageLabel, $pageTextDomain], [$pageTitle, $pageTextDomain])
            ->willReturnOnConsecutiveCalls($pageLabelTranslated, $pageTitleTranslated);

        $expected = '<ul class="nav-escaped navigation-escaped">' . PHP_EOL . '    <li class="nav-item-escaped active-escaped li-class-escaped">' . PHP_EOL . '        <a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>' . PHP_EOL . '    </li>' . PHP_EOL . '</ul>';

        $htmlElement = $this->getMockBuilder(HtmlElementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlElement->expects(self::once())
            ->method('toHtml')
            ->with('a', ['aria-current' => 'page', 'class' => 'nav-link xxxx', 'id' => $pageId, 'title' => $pageTitleTranslated, 'href' => $pageHref, 'target' => $pageTarget], $pageLabelTranslatedEscaped)
            ->willReturn($expected2);

        $helper = new Menu(
            $serviceLocator,
            $logger,
            $containerParser,
            $escapeHtmlAttr,
            $renderer,
            $escapeHtml,
            $htmlElement
        );

        $helper->setRole($role);
        $helper->setTranslator($translator);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        self::assertSame($expected, $helper->renderSubMenu($container));
    }

    /**
     * @throws Exception
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     */
    public function testRenderSubMenuWithTabsOnlyActiveBranchWithoutParents2(): void
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

        $parentLabel      = 'parent-label';
        $parentTextDomain = 'parent-text-domain';
        $parentTitle      = 'parent-title';

        $parentPage = new Uri();
        $parentPage->setVisible(true);
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);
        $parentPage->setId('parent-id');
        $parentPage->setClass('parent-class');
        $parentPage->setUri('##');
        $parentPage->setTarget('self');
        $parentPage->setLabel($parentLabel);
        $parentPage->setTitle($parentTitle);
        $parentPage->setTextDomain($parentTextDomain);

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

        $role = 'testRole';

        $findActiveHelper = $this->getMockBuilder(FindActiveInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, -1, null)
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

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::exactly(2))
            ->method('parseContainer')
            ->withConsecutive([$container], [$container])
            ->willReturnOnConsecutiveCalls($container, $container);

        $escapeHtmlAttr = $this->getMockBuilder(EscapeHtmlAttr::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtmlAttr->expects(self::never())
            ->method('__invoke');

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

        $expected = '';

        $htmlElement = $this->getMockBuilder(HtmlElementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlElement->expects(self::never())
            ->method('toHtml');

        $helper = new Menu(
            $serviceLocator,
            $logger,
            $containerParser,
            $escapeHtmlAttr,
            $renderer,
            $escapeHtml,
            $htmlElement
        );

        $helper->setRole($role);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        self::assertSame($expected, $helper->renderSubMenu($container));
    }

    /**
     * @throws Exception
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     */
    public function testRenderSubMenuWithTabsOnlyActiveBranchWithoutParents3(): void
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

        $parentLabel      = 'parent-label';
        $parentTextDomain = 'parent-text-domain';
        $parentTitle      = 'parent-title';

        $parentPage = new Uri();
        $parentPage->setVisible(true);
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);
        $parentPage->setId('parent-id');
        $parentPage->setClass('parent-class');
        $parentPage->setUri('##');
        $parentPage->setTarget('self');
        $parentPage->setLabel($parentLabel);
        $parentPage->setTitle($parentTitle);
        $parentPage->setTextDomain($parentTextDomain);

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

        $role = 'testRole';

        $findActiveHelper = $this->getMockBuilder(FindActiveInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, -1, null)
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

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::exactly(2))
            ->method('parseContainer')
            ->withConsecutive([$container], [$container])
            ->willReturnOnConsecutiveCalls($container, $container);

        $escapeHtmlAttr = $this->getMockBuilder(EscapeHtmlAttr::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtmlAttr->expects(self::never())
            ->method('__invoke');

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

        $expected = '';

        $htmlElement = $this->getMockBuilder(HtmlElementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlElement->expects(self::never())
            ->method('toHtml');

        $helper = new Menu(
            $serviceLocator,
            $logger,
            $containerParser,
            $escapeHtmlAttr,
            $renderer,
            $escapeHtml,
            $htmlElement
        );

        $helper->setRole($role);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        self::assertSame($expected, $helper->renderSubMenu($container));
    }

    /**
     * @throws Exception
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     */
    public function testRenderSubMenuWithTabsOnlyActiveBranchWithoutParents4(): void
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

        $resource  = 'testResource';
        $privilege = 'testPrivilege';

        $parentLabel      = 'parent-label';
        $parentTextDomain = 'parent-text-domain';
        $parentTitle      = 'parent-title';

        $pageLabel                  = 'page-label';
        $pageLabelTranslated        = 'page-label-translated';
        $pageLabelTranslatedEscaped = 'page-label-translated-escaped';
        $pageTitle                  = 'page-title';
        $pageTitleTranslated        = 'page-title-translated';
        $pageTextDomain             = 'page-text-domain';
        $pageId                     = 'page-id';
        $pageHref                   = 'http://page';
        $pageTarget                 = 'page-target';

        $parentPage = new Uri();
        $parentPage->setVisible(true);
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);
        $parentPage->setId('parent-id');
        $parentPage->setClass('parent-class');
        $parentPage->setUri('##');
        $parentPage->setTarget('self');
        $parentPage->setLabel($parentLabel);
        $parentPage->setTitle($parentTitle);
        $parentPage->setTextDomain($parentTextDomain);

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
            ->with(true)
            ->willReturn(true);
        $page->expects(self::once())
            ->method('getLabel')
            ->willReturn($pageLabel);
        $page->expects(self::once())
            ->method('getTextDomain')
            ->willReturn($pageTextDomain);
        $page->expects(self::once())
            ->method('getTitle')
            ->willReturn($pageTitle);
        $page->expects(self::once())
            ->method('getId')
            ->willReturn($pageId);
        $page->expects(self::exactly(2))
            ->method('getClass')
            ->willReturn('xxxx');
        $page->expects(self::exactly(2))
            ->method('getHref')
            ->willReturn($pageHref);
        $page->expects(self::once())
            ->method('getTarget')
            ->willReturn($pageTarget);
        $page->expects(self::never())
            ->method('hasPage');
        $page->expects(self::once())
            ->method('hasPages')
            ->with(true)
            ->willReturn(false);
        $page->expects(self::once())
            ->method('getCustomProperties')
            ->willReturn([]);
        $page->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive(['li-active-class'], ['li-class'])
            ->willReturnOnConsecutiveCalls('li-class', null);

        $parentPage->addPage($page);

        $container = new Navigation();
        $container->addPage($parentPage);

        $role = 'testRole';

        $findActiveHelper = $this->getMockBuilder(FindActiveInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, -1, null)
            ->willReturn(
                [
                    'page' => $page,
                    'depth' => 1,
                ]
            );

        $acceptHelper = $this->getMockBuilder(AcceptHelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $acceptHelper->expects(self::once())
            ->method('accept')
            ->with($page)
            ->willReturn(true);

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
        $serviceLocator->expects(self::exactly(2))
            ->method('build')
            ->withConsecutive(
                [
                    FindActiveInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ]
            )
            ->willReturnOnConsecutiveCalls(
                $findActiveHelper,
                $acceptHelper
            );

        $expected2 = '<a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>';

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::exactly(2))
            ->method('parseContainer')
            ->withConsecutive([$container], [$container])
            ->willReturnOnConsecutiveCalls($container, $container);

        $escapeHtmlAttr = $this->getMockBuilder(EscapeHtmlAttr::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtmlAttr->expects(self::exactly(2))
            ->method('__invoke')
            ->withConsecutive(['nav-item active li-class'], ['nav navigation'])
            ->willReturnOnConsecutiveCalls('nav-item-escaped active-escaped li-class-escaped', 'nav-escaped navigation-escaped');

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::once())
            ->method('__invoke')
            ->with($pageLabelTranslated)
            ->willReturn($pageLabelTranslatedEscaped);

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
        $translator->expects(self::exactly(2))
            ->method('translate')
            ->withConsecutive([$pageLabel, $pageTextDomain], [$pageTitle, $pageTextDomain])
            ->willReturnOnConsecutiveCalls($pageLabelTranslated, $pageTitleTranslated);

        $expected = $indent . '<ul class="nav-escaped navigation-escaped">' . PHP_EOL . $indent . '    <li class="nav-item-escaped active-escaped li-class-escaped">' . PHP_EOL . $indent . '        <a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>' . PHP_EOL . $indent . '    </li>' . PHP_EOL . $indent . '</ul>';

        $htmlElement = $this->getMockBuilder(HtmlElementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlElement->expects(self::once())
            ->method('toHtml')
            ->with('a', ['aria-current' => 'page', 'class' => 'nav-link xxxx', 'id' => $pageId, 'title' => $pageTitleTranslated, 'href' => $pageHref, 'target' => $pageTarget], $pageLabelTranslatedEscaped)
            ->willReturn($expected2);

        $helper = new Menu(
            $serviceLocator,
            $logger,
            $containerParser,
            $escapeHtmlAttr,
            $renderer,
            $escapeHtml,
            $htmlElement
        );

        $helper->setRole($role);
        $helper->setTranslator($translator);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);
        $helper->setIndent($indent);

        self::assertSame($expected, $helper->renderSubMenu($container));
    }

    /**
     * @throws Exception
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     */
    public function testRenderMenuWithTabsOnlyActiveBranchWithoutParents2(): void
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

        $parentLabel      = 'parent-label';
        $parentTextDomain = 'parent-text-domain';
        $parentTitle      = 'parent-title';

        $parentPage = new Uri();
        $parentPage->setVisible(true);
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);
        $parentPage->setId('parent-id');
        $parentPage->setClass('parent-class');
        $parentPage->setUri('##');
        $parentPage->setTarget('self');
        $parentPage->setLabel($parentLabel);
        $parentPage->setTitle($parentTitle);
        $parentPage->setTextDomain($parentTextDomain);

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
        $page->expects(self::once())
            ->method('hasPages')
            ->with(true)
            ->willReturn(false);
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

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::exactly(2))
            ->method('parseContainer')
            ->withConsecutive([$name], [$container])
            ->willReturnOnConsecutiveCalls($container, $container);

        $escapeHtmlAttr = $this->getMockBuilder(EscapeHtmlAttr::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtmlAttr->expects(self::never())
            ->method('__invoke');

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

        $expected = '';

        $htmlElement = $this->getMockBuilder(HtmlElementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlElement->expects(self::never())
            ->method('toHtml');

        $helper = new Menu(
            $serviceLocator,
            $logger,
            $containerParser,
            $escapeHtmlAttr,
            $renderer,
            $escapeHtml,
            $htmlElement
        );

        $helper->setRole($role);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        self::assertSame($expected, $helper->renderMenu($name, ['tabs' => true, 'dark' => true, 'in-navbar' => true, 'onlyActiveBranch' => true, 'renderParents' => false, 'minDepth' => 2]));
    }

    /**
     * @throws Exception
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     */
    public function testRenderMenuWithTabsOnlyActiveBranchWithoutParents3(): void
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

        $parentLabel      = 'parent-label';
        $parentTextDomain = 'parent-text-domain';
        $parentTitle      = 'parent-title';

        $pageLabel                  = 'page-label';
        $pageLabelTranslated        = 'page-label-translated';
        $pageLabelTranslatedEscaped = 'page-label-translated-escaped';
        $pageTitle                  = 'page-title';
        $pageTitleTranslated        = 'page-title-translated';
        $pageTextDomain             = 'page-text-domain';
        $pageId                     = 'page-id';
        $pageHref                   = 'http://page';
        $pageTarget                 = 'page-target';

        $parentPage = new Uri();
        $parentPage->setVisible(true);
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);
        $parentPage->setId('parent-id');
        $parentPage->setClass('parent-class');
        $parentPage->setUri('##');
        $parentPage->setTarget('self');
        $parentPage->setLabel($parentLabel);
        $parentPage->setTitle($parentTitle);
        $parentPage->setTextDomain($parentTextDomain);

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
            ->with(true)
            ->willReturn(true);
        $page->expects(self::once())
            ->method('getLabel')
            ->willReturn($pageLabel);
        $page->expects(self::once())
            ->method('getTextDomain')
            ->willReturn($pageTextDomain);
        $page->expects(self::once())
            ->method('getTitle')
            ->willReturn($pageTitle);
        $page->expects(self::once())
            ->method('getId')
            ->willReturn($pageId);
        $page->expects(self::exactly(2))
            ->method('getClass')
            ->willReturn('xxxx');
        $page->expects(self::exactly(2))
            ->method('getHref')
            ->willReturn($pageHref);
        $page->expects(self::once())
            ->method('getTarget')
            ->willReturn($pageTarget);
        $page->expects(self::never())
            ->method('hasPage');
        $page->expects(self::once())
            ->method('hasPages')
            ->with(true)
            ->willReturn(true);
        $page->expects(self::once())
            ->method('getCustomProperties')
            ->willReturn([]);
        $page->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive(['li-active-class'], ['li-class'])
            ->willReturnOnConsecutiveCalls(null, null);

        $parentPage->addPage($page);

        $container = new Navigation();
        $container->addPage($parentPage);

        $role = 'testRole';

        $findActiveHelper = $this->getMockBuilder(FindActiveInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, -1, 1)
            ->willReturn(
                [
                    'page' => $page,
                    'depth' => 1,
                ]
            );

        $acceptHelper = $this->getMockBuilder(AcceptHelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $acceptHelper->expects(self::once())
            ->method('accept')
            ->with($page)
            ->willReturn(true);

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
        $serviceLocator->expects(self::exactly(2))
            ->method('build')
            ->withConsecutive(
                [
                    FindActiveInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ]
            )
            ->willReturnOnConsecutiveCalls(
                $findActiveHelper,
                $acceptHelper
            );

        $expected2 = '<a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>';

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::exactly(2))
            ->method('parseContainer')
            ->withConsecutive([$name], [$container])
            ->willReturnOnConsecutiveCalls($container, $container);

        $escapeHtmlAttr = $this->getMockBuilder(EscapeHtmlAttr::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtmlAttr->expects(self::exactly(4))
            ->method('__invoke')
            ->withConsecutive(['nav-item active'], ['presentation'], ['navbar-nav navigation nav-tabs'], ['tablist'])
            ->willReturnOnConsecutiveCalls('nav-item-escaped active-escaped', 'presentation-escaped', 'navbar-nav-escaped navigation-escaped nav-tabs-escaped', 'tablist-escaped');

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::once())
            ->method('__invoke')
            ->with($pageLabelTranslated)
            ->willReturn($pageLabelTranslatedEscaped);

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
        $translator->expects(self::exactly(2))
            ->method('translate')
            ->withConsecutive([$pageLabel, $pageTextDomain], [$pageTitle, $pageTextDomain])
            ->willReturnOnConsecutiveCalls($pageLabelTranslated, $pageTitleTranslated);

        $expected = '<ul class="navbar-nav-escaped navigation-escaped nav-tabs-escaped" role="tablist-escaped">' . PHP_EOL . '    <li class="nav-item-escaped active-escaped" role="presentation-escaped">' . PHP_EOL . '        <a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>' . PHP_EOL . '    </li>' . PHP_EOL . '</ul>';

        $htmlElement = $this->getMockBuilder(HtmlElementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlElement->expects(self::once())
            ->method('toHtml')
            ->with('a', ['aria-current' => 'page', 'class' => 'nav-link xxxx', 'id' => $pageId, 'title' => $pageTitleTranslated, 'href' => $pageHref, 'target' => $pageTarget, 'role' => 'tab'], $pageLabelTranslatedEscaped)
            ->willReturn($expected2);

        $helper = new Menu(
            $serviceLocator,
            $logger,
            $containerParser,
            $escapeHtmlAttr,
            $renderer,
            $escapeHtml,
            $htmlElement
        );

        $helper->setRole($role);
        $helper->setTranslator($translator);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        self::assertSame($expected, $helper->renderMenu($name, ['tabs' => true, 'dark' => true, 'in-navbar' => true, 'onlyActiveBranch' => true, 'renderParents' => false, 'minDepth' => 0, 'maxDepth' => 1]));
    }

    /**
     * @throws Exception
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     */
    public function testRenderMenuWithTabsOnlyActiveBranchWithoutParents4(): void
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

        $parentLabel      = 'parent-label';
        $parentTextDomain = 'parent-text-domain';
        $parentTitle      = 'parent-title';

        $parentPage = new Uri();
        $parentPage->setVisible(true);
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);
        $parentPage->setId('parent-id');
        $parentPage->setClass('parent-class');
        $parentPage->setUri('##');
        $parentPage->setTarget('self');
        $parentPage->setLabel($parentLabel);
        $parentPage->setTitle($parentTitle);
        $parentPage->setTextDomain($parentTextDomain);

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
        $page->expects(self::once())
            ->method('hasPages')
            ->with(true)
            ->willReturn(false);
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
            ->with($container, -1, null)
            ->willReturn(
                [
                    'page' => $page,
                    'depth' => 1,
                ]
            );

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
        $auth->expects(self::never())
            ->method('isAllowed');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::exactly(2))
            ->method('build')
            ->withConsecutive(
                [
                    FindActiveInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ]
            )
            ->willReturnOnConsecutiveCalls(
                $findActiveHelper,
                $acceptHelper
            );

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::exactly(2))
            ->method('parseContainer')
            ->withConsecutive([$name], [$container])
            ->willReturnOnConsecutiveCalls($container, $container);

        $escapeHtmlAttr = $this->getMockBuilder(EscapeHtmlAttr::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtmlAttr->expects(self::never())
            ->method('__invoke');

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

        $expected = '';

        $htmlElement = $this->getMockBuilder(HtmlElementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlElement->expects(self::never())
            ->method('toHtml');

        $helper = new Menu(
            $serviceLocator,
            $logger,
            $containerParser,
            $escapeHtmlAttr,
            $renderer,
            $escapeHtml,
            $htmlElement
        );

        $helper->setRole($role);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        self::assertSame($expected, $helper->renderMenu($name, ['tabs' => true, 'dark' => true, 'in-navbar' => true, 'onlyActiveBranch' => true, 'renderParents' => false]));
    }

    /**
     * @throws Exception
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     */
    public function testRenderMenuWithTabsOnlyActiveBranchWithoutParents5(): void
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

        $parentLabel      = 'parent-label';
        $parentTextDomain = 'parent-text-domain';
        $parentTitle      = 'parent-title';

        $pageLabel                  = 'page-label';
        $pageLabelTranslated        = 'page-label-translated';
        $pageLabelTranslatedEscaped = 'page-label-translated-escaped';
        $pageTitle                  = 'page-title';
        $pageTitleTranslated        = 'page-title-translated';
        $pageTextDomain             = 'page-text-domain';
        $pageId                     = 'page-id';
        $pageHref                   = 'http://page';
        $pageTarget                 = 'page-target';

        $parentPage = new Uri();
        $parentPage->setVisible(true);
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);
        $parentPage->setId('parent-id');
        $parentPage->setClass('parent-class');
        $parentPage->setUri('##');
        $parentPage->setTarget('self');
        $parentPage->setLabel($parentLabel);
        $parentPage->setTitle($parentTitle);
        $parentPage->setTextDomain($parentTextDomain);

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
            ->with(true)
            ->willReturn(true);
        $page->expects(self::once())
            ->method('getLabel')
            ->willReturn($pageLabel);
        $page->expects(self::once())
            ->method('getTextDomain')
            ->willReturn($pageTextDomain);
        $page->expects(self::once())
            ->method('getTitle')
            ->willReturn($pageTitle);
        $page->expects(self::once())
            ->method('getId')
            ->willReturn($pageId);
        $page->expects(self::exactly(2))
            ->method('getClass')
            ->willReturn('xxxx');
        $page->expects(self::exactly(2))
            ->method('getHref')
            ->willReturn($pageHref);
        $page->expects(self::once())
            ->method('getTarget')
            ->willReturn($pageTarget);
        $page->expects(self::never())
            ->method('hasPage');
        $page->expects(self::once())
            ->method('hasPages')
            ->with(true)
            ->willReturn(false);
        $page->expects(self::once())
            ->method('getCustomProperties')
            ->willReturn([]);
        $page->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive(['li-active-class'], ['li-class'])
            ->willReturnOnConsecutiveCalls(null, null);

        $parentPage->addPage($page);

        $container = new Navigation();
        $container->addPage($parentPage);

        $role = 'testRole';

        $findActiveHelper = $this->getMockBuilder(FindActiveInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, -1, null)
            ->willReturn(
                [
                    'page' => $page,
                    'depth' => 1,
                ]
            );

        $acceptHelper = $this->getMockBuilder(AcceptHelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $acceptHelper->expects(self::once())
            ->method('accept')
            ->with($page)
            ->willReturn(true);

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
        $serviceLocator->expects(self::exactly(2))
            ->method('build')
            ->withConsecutive(
                [
                    FindActiveInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ]
            )
            ->willReturnOnConsecutiveCalls(
                $findActiveHelper,
                $acceptHelper
            );

        $expected2 = '<a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>';

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::exactly(2))
            ->method('parseContainer')
            ->withConsecutive([$name], [$container])
            ->willReturnOnConsecutiveCalls($container, $container);

        $escapeHtmlAttr = $this->getMockBuilder(EscapeHtmlAttr::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtmlAttr->expects(self::exactly(4))
            ->method('__invoke')
            ->withConsecutive(['nav-item active li-class xxxx'], ['presentation'], ['navbar-nav navigation nav-tabs'], ['tablist'])
            ->willReturnOnConsecutiveCalls('nav-item-escaped active-escaped li-class-escaped xxxx-escaped', 'presentation-escaped', 'navbar-nav-escaped navigation-escaped nav-tabs-escaped', 'tablist-escaped');

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::once())
            ->method('__invoke')
            ->with($pageLabelTranslated)
            ->willReturn($pageLabelTranslatedEscaped);

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
        $translator->expects(self::exactly(2))
            ->method('translate')
            ->withConsecutive([$pageLabel, $pageTextDomain], [$pageTitle, $pageTextDomain])
            ->willReturnOnConsecutiveCalls($pageLabelTranslated, $pageTitleTranslated);

        $expected = '<ul class="navbar-nav-escaped navigation-escaped nav-tabs-escaped" role="tablist-escaped">' . PHP_EOL . '    <li class="nav-item-escaped active-escaped li-class-escaped xxxx-escaped" role="presentation-escaped">' . PHP_EOL . '        <a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>' . PHP_EOL . '    </li>' . PHP_EOL . '</ul>';

        $htmlElement = $this->getMockBuilder(HtmlElementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlElement->expects(self::once())
            ->method('toHtml')
            ->with('a', ['aria-current' => 'page', 'class' => 'nav-link', 'id' => $pageId, 'title' => $pageTitleTranslated, 'href' => $pageHref, 'target' => $pageTarget, 'role' => 'tab'], $pageLabelTranslatedEscaped)
            ->willReturn($expected2);

        $helper = new Menu(
            $serviceLocator,
            $logger,
            $containerParser,
            $escapeHtmlAttr,
            $renderer,
            $escapeHtml,
            $htmlElement
        );

        $helper->setRole($role);
        $helper->setTranslator($translator);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        self::assertSame($expected, $helper->renderMenu($name, ['tabs' => true, 'dark' => true, 'in-navbar' => true, 'onlyActiveBranch' => true, 'renderParents' => false, 'liClass' => 'li-class', 'addClassToListItem' => true]));
    }

    /**
     * @throws Exception
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     */
    public function testRenderMenu2(): void
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

        $parentLabel                  = 'parent-label';
        $parentTranslatedLabel        = 'parent-label-translated';
        $parentTranslatedLabelEscaped = 'parent-label-translated-escaped';
        $parentTextDomain             = 'parent-text-domain';
        $parentTitle                  = 'parent-title';
        $parentTranslatedTitle        = 'parent-title-translated';

        $parentParentLabel                  = 'parent-parent-label';
        $parentParentTranslatedLabel        = 'parent-parent-label-translated';
        $parentParentTranslatedLabelEscaped = 'parent-parent-label-translated-escaped';
        $parentParentTextDomain             = 'parent-parent-text-domain';
        $parentParentTitle                  = 'parent-parent-title';
        $parentParentTranslatedTitle        = 'parent-parent-title-translated';

        $pageLabel                  = 'page-label';
        $pageLabelTranslated        = 'page-label-translated';
        $pageLabelTranslatedEscaped = 'page-label-translated-escaped';
        $pageTitle                  = 'page-title';
        $pageTitleTranslated        = 'page-title-translated';
        $pageTextDomain             = 'page-text-domain';
        $pageId                     = 'page-id';
        $pageHref                   = 'http://page';
        $pageTarget                 = 'page-target';

        $page2Label                  = 'page2-label';
        $page2LabelTranslated        = 'page2-label-translated';
        $page2LabelTranslatedEscaped = 'page2-label-translated-escaped';
        $page2Title                  = 'page2-title';
        $page2TitleTranslated        = 'page2-title-translated';
        $page2TextDomain             = 'page2-text-domain';
        $page2Id                     = 'page2-id';
        $page2Href                   = 'http://page2';
        $page2Target                 = 'page2-target';

        $page3Label                  = 'page3-label';
        $page3LabelTranslated        = 'page3-label-translated';
        $page3LabelTranslatedEscaped = 'page3-label-translated-escaped';
        $page3Title                  = 'page3-title';
        $page3TitleTranslated        = 'page3-title-translated';
        $page3TextDomain             = 'page3-text-domain';
        $page3Id                     = 'page3-id';
        $page3Href                   = 'http://page3';
        $page3Target                 = 'page3-target';

        $parentPage = new Uri();
        $parentPage->setVisible(true);
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);
        $parentPage->setId('parent-id');
        $parentPage->setClass('parent-class');
        $parentPage->setUri('##');
        $parentPage->setTarget('self');
        $parentPage->setLabel($parentLabel);
        $parentPage->setTitle($parentTitle);
        $parentPage->setTextDomain($parentTextDomain);

        $parentParentPage = new Uri();
        $parentParentPage->setVisible(true);
        $parentParentPage->setResource($resource);
        $parentParentPage->setPrivilege($privilege);
        $parentParentPage->setId('parent-parent-id');
        $parentParentPage->setClass('parent-parent-class');
        $parentParentPage->setUri('###');
        $parentParentPage->setTarget('self-parent');
        $parentParentPage->setLabel($parentParentLabel);
        $parentParentPage->setTitle($parentParentTitle);
        $parentParentPage->setTextDomain($parentParentTextDomain);

        $page = $this->getMockBuilder(AbstractPage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::once())
            ->method('isVisible')
            ->with(false)
            ->willReturn(true);
        $page->expects(self::never())
            ->method('getResource');
        $page->expects(self::never())
            ->method('getPrivilege');
        $page->expects(self::never())
            ->method('getParent');
        $page->expects(self::exactly(3))
            ->method('isActive')
            ->with(true)
            ->willReturn(true);
        $page->expects(self::once())
            ->method('getLabel')
            ->willReturn($pageLabel);
        $page->expects(self::once())
            ->method('getTextDomain')
            ->willReturn($pageTextDomain);
        $page->expects(self::once())
            ->method('getTitle')
            ->willReturn($pageTitle);
        $page->expects(self::once())
            ->method('getId')
            ->willReturn($pageId);
        $page->expects(self::exactly(2))
            ->method('getClass')
            ->willReturn('xxxx');
        $page->expects(self::exactly(2))
            ->method('getHref')
            ->willReturn($pageHref);
        $page->expects(self::once())
            ->method('getTarget')
            ->willReturn($pageTarget);
        $page->expects(self::never())
            ->method('hasPage');
        $page->expects(self::exactly(2))
            ->method('hasPages')
            ->withConsecutive([false], [true])
            ->willReturnOnConsecutiveCalls(false, false);
        $page->expects(self::once())
            ->method('getCustomProperties')
            ->willReturn([]);
        $page->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive(['li-active-class'], ['li-class'])
            ->willReturnOnConsecutiveCalls(null, null);

        $page2 = $this->getMockBuilder(AbstractPage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page2->expects(self::never())
            ->method('isVisible');
        $page2->expects(self::never())
            ->method('getResource');
        $page2->expects(self::never())
            ->method('getPrivilege');
        $page2->expects(self::never())
            ->method('getParent');
        $page2->expects(self::once())
            ->method('isActive')
            ->with(true)
            ->willReturn(true);
        $page2->expects(self::once())
            ->method('getLabel')
            ->willReturn($page2Label);
        $page2->expects(self::once())
            ->method('getTextDomain')
            ->willReturn($page2TextDomain);
        $page2->expects(self::once())
            ->method('getTitle')
            ->willReturn($page2Title);
        $page2->expects(self::once())
            ->method('getId')
            ->willReturn($page2Id);
        $page2->expects(self::exactly(2))
            ->method('getClass')
            ->willReturn('xxxx2');
        $page2->expects(self::exactly(2))
            ->method('getHref')
            ->willReturn($page2Href);
        $page2->expects(self::once())
            ->method('getTarget')
            ->willReturn($page2Target);
        $page2->expects(self::never())
            ->method('hasPage');
        $page2->expects(self::exactly(2))
            ->method('hasPages')
            ->withConsecutive([false], [true])
            ->willReturnOnConsecutiveCalls(false, false);
        $page2->expects(self::once())
            ->method('getCustomProperties')
            ->willReturn([]);
        $page2->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive(['li-active-class'], ['li-class'])
            ->willReturnOnConsecutiveCalls(null, null);

        $page3 = $this->getMockBuilder(AbstractPage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page3->expects(self::never())
            ->method('isVisible');
        $page3->expects(self::never())
            ->method('getResource');
        $page3->expects(self::never())
            ->method('getPrivilege');
        $page3->expects(self::never())
            ->method('getParent');
        $page3->expects(self::once())
            ->method('isActive')
            ->with(true)
            ->willReturn(true);
        $page3->expects(self::once())
            ->method('getLabel')
            ->willReturn($page3Label);
        $page3->expects(self::once())
            ->method('getTextDomain')
            ->willReturn($page3TextDomain);
        $page3->expects(self::once())
            ->method('getTitle')
            ->willReturn($page3Title);
        $page3->expects(self::once())
            ->method('getId')
            ->willReturn($page3Id);
        $page3->expects(self::exactly(2))
            ->method('getClass')
            ->willReturn('xxxx3');
        $page3->expects(self::exactly(2))
            ->method('getHref')
            ->willReturn($page3Href);
        $page3->expects(self::once())
            ->method('getTarget')
            ->willReturn($page3Target);
        $page3->expects(self::never())
            ->method('hasPage');
        $page3->expects(self::exactly(2))
            ->method('hasPages')
            ->withConsecutive([false], [true])
            ->willReturnOnConsecutiveCalls(false, false);
        $page3->expects(self::once())
            ->method('getCustomProperties')
            ->willReturn([]);
        $page3->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive(['li-active-class'], ['li-class'])
            ->willReturnOnConsecutiveCalls(null, null);

        $parentPage->addPage($page);
        $parentParentPage->addPage($parentPage);
        $parentParentPage->addPage($page2);
        $parentParentPage->addPage($page3);

        $container = new Navigation();
        $container->addPage($parentParentPage);

        $role = 'testRole';

        $findActiveHelper = $this->getMockBuilder(FindActiveInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, 0, null)
            ->willReturn(
                [
                    'page' => $page,
                    'depth' => 1,
                ]
            );

        $acceptHelper = $this->getMockBuilder(AcceptHelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $acceptHelper->expects(self::exactly(9))
            ->method('accept')
            ->withConsecutive([$parentParentPage], [$parentPage], [$page], [$page], [$parentPage], [$page], [$page], [$page2], [$page3])
            ->willReturnOnConsecutiveCalls(true, true, true, true, true, true, true, true, true);

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
        $serviceLocator->expects(self::exactly(10))
            ->method('build')
            ->withConsecutive(
                [
                    FindActiveInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ]
            )
            ->willReturnOnConsecutiveCalls(
                $findActiveHelper,
                $acceptHelper,
                $acceptHelper,
                $acceptHelper,
                $acceptHelper,
                $acceptHelper,
                $acceptHelper,
                $acceptHelper,
                $acceptHelper,
                $acceptHelper
            );

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::exactly(2))
            ->method('parseContainer')
            ->withConsecutive([$name], [$container])
            ->willReturnOnConsecutiveCalls($container, $container);

        $escapeHtmlAttr = $this->getMockBuilder(EscapeHtmlAttr::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtmlAttr->expects(self::exactly(10))
            ->method('__invoke')
            ->withConsecutive(['nav navigation'], ['nav-item dropdown active'], ['dropdown-menu'], ['parent-parent-id'], ['dropdown active'], ['dropdown-menu'], ['parent-id'], ['active'], ['active'], ['active'])
            ->willReturnOnConsecutiveCalls('nav-escaped navigation-escaped', 'nav-item-escaped dropdown-escaped active-escaped', 'dropdown-menu-escaped', 'parent-parent-id-escaped', 'dropdown-escaped active-escaped', 'dropdown-menu-escaped', 'parent-id-escaped', 'active-escaped', 'active-escaped', 'active-escaped');

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::exactly(5))
            ->method('__invoke')
            ->withConsecutive([$parentParentTranslatedLabel], [$parentTranslatedLabel], [$pageLabelTranslated], [$page2LabelTranslated], [$page3LabelTranslated])
            ->willReturnOnConsecutiveCalls($parentParentTranslatedLabelEscaped, $parentTranslatedLabelEscaped, $pageLabelTranslatedEscaped, $page2LabelTranslatedEscaped, $page3LabelTranslatedEscaped);

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
        $translator->expects(self::exactly(10))
            ->method('translate')
            ->withConsecutive([$parentParentLabel, $parentParentTextDomain], [$parentParentTitle, $parentParentTextDomain], [$parentLabel, $parentTextDomain], [$parentTitle, $parentTextDomain], [$pageLabel, $pageTextDomain], [$pageTitle, $pageTextDomain], [$page2Label, $page2TextDomain], [$page2Title, $page2TextDomain], [$page3Label, $page3TextDomain], [$page3Title, $page3TextDomain])
            ->willReturnOnConsecutiveCalls($parentParentTranslatedLabel, $parentParentTranslatedTitle, $parentTranslatedLabel, $parentTranslatedTitle, $pageLabelTranslated, $pageTitleTranslated, $page2LabelTranslated, $page2TitleTranslated, $page3LabelTranslated, $page3TitleTranslated);

        $expected = '<ul class="nav-escaped navigation-escaped">' . PHP_EOL . '    <li class="nav-item-escaped dropdown-escaped active-escaped">' . PHP_EOL . '        <a parent-id-escaped="parent-id-escaped" parent-title-escaped="parent-title-escaped" parent-class-escaped="parent-class-escaped" parent-href-escaped="##-escaped" parent-target-escaped="self-escaped">parent-label-escaped</a>' . PHP_EOL . '        <ul class="dropdown-menu-escaped" aria-labelledby="parent-parent-id-escaped">' . PHP_EOL . '            <li class="dropdown-escaped active-escaped">' . PHP_EOL . '                <a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>' . PHP_EOL . '                <ul class="dropdown-menu-escaped" aria-labelledby="parent-id-escaped">' . PHP_EOL . '                    <li class="active-escaped">' . PHP_EOL . '                        <a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>' . PHP_EOL . '                    </li>' . PHP_EOL . '                </ul>' . PHP_EOL . '            </li>' . PHP_EOL . '            <li class="active-escaped">' . PHP_EOL . '                <a idEscaped="test2IdEscaped" titleEscaped="test2TitleTranslatedAndEscaped" classEscaped="test2ClassEscaped" hrefEscaped="#2Escaped">test2LabelTranslatedAndEscaped</a>' . PHP_EOL . '            </li>' . PHP_EOL . '            <li class="active-escaped">' . PHP_EOL . '                <a idEscaped="test3IdEscaped" titleEscaped="test3TitleTranslatedAndEscaped" classEscaped="test3ClassEscaped" hrefEscaped="#3Escaped">test3LabelTranslatedAndEscaped</a>' . PHP_EOL . '            </li>' . PHP_EOL . '        </ul>' . PHP_EOL . '    </li>' . PHP_EOL . '</ul>';

        $expected1 = '<a parent-id-escaped="parent-id-escaped" parent-title-escaped="parent-title-escaped" parent-class-escaped="parent-class-escaped" parent-href-escaped="##-escaped" parent-target-escaped="self-escaped">parent-label-escaped</a>';
        $expected2 = '<a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>';
        $expected3 = '<a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>';
        $expected4 = '<a idEscaped="test2IdEscaped" titleEscaped="test2TitleTranslatedAndEscaped" classEscaped="test2ClassEscaped" hrefEscaped="#2Escaped">test2LabelTranslatedAndEscaped</a>';
        $expected5 = '<a idEscaped="test3IdEscaped" titleEscaped="test3TitleTranslatedAndEscaped" classEscaped="test3ClassEscaped" hrefEscaped="#3Escaped">test3LabelTranslatedAndEscaped</a>';

        $htmlElement = $this->getMockBuilder(HtmlElementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlElement->expects(self::exactly(5))
            ->method('toHtml')
            ->withConsecutive(
                ['a', ['aria-current' => 'page', 'class' => 'nav-link dropdown-toggle parent-parent-class', 'id' => 'parent-parent-id', 'title' => $parentParentTranslatedTitle, 'href' => '###', 'target' => 'self-parent', 'data-bs-toggle' => 'dropdown', 'aria-expanded' => 'false', 'role' => 'button'], $parentParentTranslatedLabelEscaped],
                ['a', ['class' => 'dropdown-item dropdown-toggle parent-class', 'id' => 'parent-id', 'title' => $parentTranslatedTitle, 'href' => '##', 'target' => 'self', 'data-bs-toggle' => 'dropdown', 'aria-expanded' => 'false', 'role' => 'button'], $parentTranslatedLabelEscaped],
                ['a', ['class' => 'dropdown-item xxxx', 'id' => $pageId, 'title' => $pageTitleTranslated, 'href' => $pageHref, 'target' => $pageTarget], $pageLabelTranslatedEscaped],
                ['a', ['class' => 'dropdown-item xxxx2', 'id' => $page2Id, 'title' => $page2TitleTranslated, 'href' => $page2Href, 'target' => $page2Target], $page2LabelTranslatedEscaped],
                ['a', ['class' => 'dropdown-item xxxx3', 'id' => $page3Id, 'title' => $page3TitleTranslated, 'href' => $page3Href, 'target' => $page3Target], $page3LabelTranslatedEscaped]
            )
            ->willReturnOnConsecutiveCalls($expected1, $expected2, $expected3, $expected4, $expected5);

        $helper = new Menu(
            $serviceLocator,
            $logger,
            $containerParser,
            $escapeHtmlAttr,
            $renderer,
            $escapeHtml,
            $htmlElement
        );

        $helper->setRole($role);
        $helper->setTranslator($translator);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        self::assertSame($expected, $helper->renderMenu($name));
    }

    /**
     * @throws Exception
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     */
    public function testRenderMenu3(): void
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

        $parentLabel                  = 'parent-label';
        $parentTranslatedLabel        = 'parent-label-translated';
        $parentTranslatedLabelEscaped = 'parent-label-translated-escaped';
        $parentTextDomain             = 'parent-text-domain';
        $parentTitle                  = 'parent-title';
        $parentTranslatedTitle        = 'parent-title-translated';

        $parentParentLabel                  = 'parent-parent-label';
        $parentParentTranslatedLabel        = 'parent-parent-label-translated';
        $parentParentTranslatedLabelEscaped = 'parent-parent-label-translated-escaped';
        $parentParentTextDomain             = 'parent-parent-text-domain';
        $parentParentTitle                  = 'parent-parent-title';
        $parentParentTranslatedTitle        = 'parent-parent-title-translated';

        $pageLabel                  = 'page-label';
        $pageLabelTranslated        = 'page-label-translated';
        $pageLabelTranslatedEscaped = 'page-label-translated-escaped';
        $pageTitle                  = 'page-title';
        $pageTitleTranslated        = 'page-title-translated';
        $pageTextDomain             = 'page-text-domain';
        $pageId                     = 'page-id';
        $pageHref                   = 'http://page';
        $pageTarget                 = 'page-target';

        $page3Label                  = 'page3-label';
        $page3LabelTranslated        = 'page3-label-translated';
        $page3LabelTranslatedEscaped = 'page3-label-translated-escaped';
        $page3Title                  = 'page3-title';
        $page3TitleTranslated        = 'page3-title-translated';
        $page3TextDomain             = 'page3-text-domain';
        $page3Id                     = 'page3-id';
        $page3Href                   = 'http://page3';
        $page3Target                 = 'page3-target';

        $parentPage = new Uri();
        $parentPage->setVisible(true);
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);
        $parentPage->setId('parent-id');
        $parentPage->setClass('parent-class');
        $parentPage->setUri('##');
        $parentPage->setTarget('self');
        $parentPage->setLabel($parentLabel);
        $parentPage->setTitle($parentTitle);
        $parentPage->setTextDomain($parentTextDomain);

        $parentParentPage = new Uri();
        $parentParentPage->setVisible(true);
        $parentParentPage->setResource($resource);
        $parentParentPage->setPrivilege($privilege);
        $parentParentPage->setId('parent-parent-id');
        $parentParentPage->setClass('parent-parent-class');
        $parentParentPage->setUri('###');
        $parentParentPage->setTarget('self-parent');
        $parentParentPage->setLabel($parentParentLabel);
        $parentParentPage->setTitle($parentParentTitle);
        $parentParentPage->setTextDomain($parentParentTextDomain);

        $page3 = $this->getMockBuilder(AbstractPage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $page = $this->getMockBuilder(AbstractPage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::once())
            ->method('isVisible')
            ->with(false)
            ->willReturn(true);
        $page->expects(self::never())
            ->method('getResource');
        $page->expects(self::never())
            ->method('getPrivilege');
        $page->expects(self::never())
            ->method('getParent');
        $page->expects(self::exactly(3))
            ->method('isActive')
            ->with(true)
            ->willReturn(true);
        $page->expects(self::once())
            ->method('getLabel')
            ->willReturn($pageLabel);
        $page->expects(self::once())
            ->method('getTextDomain')
            ->willReturn($pageTextDomain);
        $page->expects(self::once())
            ->method('getTitle')
            ->willReturn($pageTitle);
        $page->expects(self::once())
            ->method('getId')
            ->willReturn($pageId);
        $page->expects(self::exactly(2))
            ->method('getClass')
            ->willReturn('xxxx');
        $page->expects(self::exactly(2))
            ->method('getHref')
            ->willReturn($pageHref);
        $page->expects(self::once())
            ->method('getTarget')
            ->willReturn($pageTarget);
        $page->expects(self::once())
            ->method('hasPage')
            ->with($page3)
            ->willReturn(true);
        $page->expects(self::exactly(2))
            ->method('hasPages')
            ->withConsecutive([false], [true])
            ->willReturnOnConsecutiveCalls(false, false);
        $page->expects(self::once())
            ->method('getCustomProperties')
            ->willReturn([]);
        $page->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive(['li-active-class'], ['li-class'])
            ->willReturnOnConsecutiveCalls(null, null);

        $page2 = $this->getMockBuilder(AbstractPage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page2->expects(self::never())
            ->method('isVisible');
        $page2->expects(self::never())
            ->method('getResource');
        $page2->expects(self::never())
            ->method('getPrivilege');
        $page2->expects(self::never())
            ->method('getParent');
        $page2->expects(self::never())
            ->method('isActive');
        $page2->expects(self::never())
            ->method('getLabel');
        $page2->expects(self::never())
            ->method('getTextDomain');
        $page2->expects(self::never())
            ->method('getTitle');
        $page2->expects(self::never())
            ->method('getId');
        $page2->expects(self::never())
            ->method('getClass');
        $page2->expects(self::never())
            ->method('getHref');
        $page2->expects(self::never())
            ->method('getTarget');
        $page2->expects(self::never())
            ->method('hasPage');
        $page2->expects(self::once())
            ->method('hasPages')
            ->with(false)
            ->willReturn(false);
        $page2->expects(self::never())
            ->method('getCustomProperties');
        $page2->expects(self::never())
            ->method('get');

        $page3->expects(self::never())
            ->method('isVisible');
        $page3->expects(self::never())
            ->method('getResource');
        $page3->expects(self::never())
            ->method('getPrivilege');
        $page3->expects(self::never())
            ->method('getParent');
        $page3->expects(self::once())
            ->method('isActive')
            ->with(true)
            ->willReturn(false);
        $page3->expects(self::once())
            ->method('getLabel')
            ->willReturn($page3Label);
        $page3->expects(self::once())
            ->method('getTextDomain')
            ->willReturn($page3TextDomain);
        $page3->expects(self::once())
            ->method('getTitle')
            ->willReturn($page3Title);
        $page3->expects(self::once())
            ->method('getId')
            ->willReturn($page3Id);
        $page3->expects(self::exactly(2))
            ->method('getClass')
            ->willReturn('xxxx3');
        $page3->expects(self::exactly(2))
            ->method('getHref')
            ->willReturn($page3Href);
        $page3->expects(self::once())
            ->method('getTarget')
            ->willReturn($page3Target);
        $page3->expects(self::never())
            ->method('hasPage');
        $page3->expects(self::exactly(2))
            ->method('hasPages')
            ->withConsecutive([false], [true])
            ->willReturnOnConsecutiveCalls(false, false);
        $page3->expects(self::once())
            ->method('getCustomProperties')
            ->willReturn([]);
        $page3->expects(self::once())
            ->method('get')
            ->with('li-class')
            ->willReturn(null);

        $parentPage->addPage($page);
        $parentParentPage->addPage($parentPage);
        $parentParentPage->addPage($page2);
        $parentParentPage->addPage($page3);

        $container = new Navigation();
        $container->addPage($parentParentPage);

        $role = 'testRole';

        $findActiveHelper = $this->getMockBuilder(FindActiveInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, 0, null)
            ->willReturn(
                [
                    'page' => $page,
                    'depth' => 1,
                ]
            );

        $acceptHelper = $this->getMockBuilder(AcceptHelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $acceptHelper->expects(self::exactly(9))
            ->method('accept')
            ->withConsecutive([$parentParentPage], [$parentPage], [$page], [$page], [$parentPage], [$page], [$page], [$page2], [$page3])
            ->willReturnOnConsecutiveCalls(true, true, true, true, true, true, true, false, true);

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
        $serviceLocator->expects(self::exactly(10))
            ->method('build')
            ->withConsecutive(
                [
                    FindActiveInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ]
            )
            ->willReturnOnConsecutiveCalls(
                $findActiveHelper,
                $acceptHelper,
                $acceptHelper,
                $acceptHelper,
                $acceptHelper,
                $acceptHelper,
                $acceptHelper,
                $acceptHelper,
                $acceptHelper,
                $acceptHelper
            );

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::exactly(2))
            ->method('parseContainer')
            ->withConsecutive([$name], [$container])
            ->willReturnOnConsecutiveCalls($container, $container);

        $escapeHtmlAttr = $this->getMockBuilder(EscapeHtmlAttr::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtmlAttr->expects(self::exactly(8))
            ->method('__invoke')
            ->withConsecutive(['nav navigation'], ['nav-item dropstart active'], ['dropdown-menu'], ['parent-parent-id'], ['dropstart active'], ['dropdown-menu'], ['parent-id'], ['active'])
            ->willReturnOnConsecutiveCalls('nav-escaped navigation-escaped', 'nav-item-escaped dropdown-escaped active-escaped', 'dropdown-menu-escaped', 'parent-parent-id-escaped', 'dropdown-escaped active-escaped', 'dropdown-menu-escaped', 'parent-id-escaped', 'active-escaped');

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::exactly(4))
            ->method('__invoke')
            ->withConsecutive([$parentParentTranslatedLabel], [$parentTranslatedLabel], [$pageLabelTranslated], [$page3LabelTranslated])
            ->willReturnOnConsecutiveCalls($parentParentTranslatedLabelEscaped, $parentTranslatedLabelEscaped, $pageLabelTranslatedEscaped, $page3LabelTranslatedEscaped);

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
        $translator->expects(self::exactly(8))
            ->method('translate')
            ->withConsecutive([$parentParentLabel, $parentParentTextDomain], [$parentParentTitle, $parentParentTextDomain], [$parentLabel, $parentTextDomain], [$parentTitle, $parentTextDomain], [$pageLabel, $pageTextDomain], [$pageTitle, $pageTextDomain], [$page3Label, $page3TextDomain], [$page3Title, $page3TextDomain])
            ->willReturnOnConsecutiveCalls($parentParentTranslatedLabel, $parentParentTranslatedTitle, $parentTranslatedLabel, $parentTranslatedTitle, $pageLabelTranslated, $pageTitleTranslated, $page3LabelTranslated, $page3TitleTranslated);

        $expected = '<ul class="nav-escaped navigation-escaped">' . PHP_EOL . '    <li class="nav-item-escaped dropdown-escaped active-escaped">' . PHP_EOL . '        <a parent-id-escaped="parent-id-escaped" parent-title-escaped="parent-title-escaped" parent-class-escaped="parent-class-escaped" parent-href-escaped="##-escaped" parent-target-escaped="self-escaped">parent-label-escaped</a>' . PHP_EOL . '        <ul class="dropdown-menu-escaped" aria-labelledby="parent-parent-id-escaped">' . PHP_EOL . '            <li class="dropdown-escaped active-escaped">' . PHP_EOL . '                <a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>' . PHP_EOL . '                <ul class="dropdown-menu-escaped" aria-labelledby="parent-id-escaped">' . PHP_EOL . '                    <li class="active-escaped">' . PHP_EOL . '                        <a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>' . PHP_EOL . '                    </li>' . PHP_EOL . '                </ul>' . PHP_EOL . '            </li>' . PHP_EOL . '            <li>' . PHP_EOL . '                <a idEscaped="test3IdEscaped" titleEscaped="test3TitleTranslatedAndEscaped" classEscaped="test3ClassEscaped" hrefEscaped="#3Escaped">test3LabelTranslatedAndEscaped</a>' . PHP_EOL . '            </li>' . PHP_EOL . '        </ul>' . PHP_EOL . '    </li>' . PHP_EOL . '</ul>';

        $expected1 = '<a parent-id-escaped="parent-id-escaped" parent-title-escaped="parent-title-escaped" parent-class-escaped="parent-class-escaped" parent-href-escaped="##-escaped" parent-target-escaped="self-escaped">parent-label-escaped</a>';
        $expected2 = '<a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>';
        $expected3 = '<a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>';
        $expected5 = '<a idEscaped="test3IdEscaped" titleEscaped="test3TitleTranslatedAndEscaped" classEscaped="test3ClassEscaped" hrefEscaped="#3Escaped">test3LabelTranslatedAndEscaped</a>';

        $htmlElement = $this->getMockBuilder(HtmlElementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlElement->expects(self::exactly(4))
            ->method('toHtml')
            ->withConsecutive(
                ['span', ['aria-current' => 'page', 'class' => 'nav-link dropdown-toggle parent-parent-class', 'id' => 'parent-parent-id', 'title' => $parentParentTranslatedTitle, 'data-bs-toggle' => 'dropdown', 'aria-expanded' => 'false', 'role' => 'button'], $parentParentTranslatedLabelEscaped],
                ['span', ['class' => 'dropdown-item dropdown-toggle parent-class', 'id' => 'parent-id', 'title' => $parentTranslatedTitle, 'data-bs-toggle' => 'dropdown', 'aria-expanded' => 'false', 'role' => 'button'], $parentTranslatedLabelEscaped],
                ['a', ['class' => 'dropdown-item xxxx', 'id' => $pageId, 'title' => $pageTitleTranslated, 'href' => $pageHref, 'target' => $pageTarget], $pageLabelTranslatedEscaped],
                ['a', ['class' => 'dropdown-item xxxx3', 'id' => $page3Id, 'title' => $page3TitleTranslated, 'href' => $page3Href, 'target' => $page3Target], $page3LabelTranslatedEscaped]
            )
            ->willReturnOnConsecutiveCalls($expected1, $expected2, $expected3, $expected5);

        $helper = new Menu(
            $serviceLocator,
            $logger,
            $containerParser,
            $escapeHtmlAttr,
            $renderer,
            $escapeHtml,
            $htmlElement
        );

        $helper->setRole($role);
        $helper->setTranslator($translator);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        self::assertSame($expected, $helper->renderMenu($name, ['onlyActiveBranch' => true, 'direction' => Menu::DROP_ORIENTATION_START, 'sublink' => Menu::STYLE_SUBLINK_SPAN]));
    }

    /**
     * @throws Exception
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     */
    public function testRenderMenu4(): void
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

        $parentLabel                  = 'parent-label';
        $parentTranslatedLabel        = 'parent-label-translated';
        $parentTranslatedLabelEscaped = 'parent-label-translated-escaped';
        $parentTextDomain             = 'parent-text-domain';
        $parentTitle                  = 'parent-title';
        $parentTranslatedTitle        = 'parent-title-translated';

        $parentParentLabel                  = 'parent-parent-label';
        $parentParentTranslatedLabel        = 'parent-parent-label-translated';
        $parentParentTranslatedLabelEscaped = 'parent-parent-label-translated-escaped';
        $parentParentTextDomain             = 'parent-parent-text-domain';
        $parentParentTitle                  = 'parent-parent-title';
        $parentParentTranslatedTitle        = 'parent-parent-title-translated';

        $pageLabel                  = 'page-label';
        $pageLabelTranslated        = 'page-label-translated';
        $pageLabelTranslatedEscaped = 'page-label-translated-escaped';
        $pageTitle                  = 'page-title';
        $pageTitleTranslated        = 'page-title-translated';
        $pageTextDomain             = 'page-text-domain';
        $pageId                     = 'page-id';
        $pageHref                   = 'http://page';
        $pageTarget                 = 'page-target';

        $page3Label                  = 'page3-label';
        $page3LabelTranslated        = 'page3-label-translated';
        $page3LabelTranslatedEscaped = 'page3-label-translated-escaped';
        $page3Title                  = 'page3-title';
        $page3TitleTranslated        = 'page3-title-translated';
        $page3TextDomain             = 'page3-text-domain';
        $page3Id                     = 'page3-id';
        $page3Href                   = 'http://page3';
        $page3Target                 = 'page3-target';

        $parentPage = new Uri();
        $parentPage->setVisible(true);
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);
        $parentPage->setId('parent-id');
        $parentPage->setClass('parent-class');
        $parentPage->setUri('##');
        $parentPage->setTarget('self');
        $parentPage->setLabel($parentLabel);
        $parentPage->setTitle($parentTitle);
        $parentPage->setTextDomain($parentTextDomain);

        $parentParentPage = new Uri();
        $parentParentPage->setVisible(true);
        $parentParentPage->setResource($resource);
        $parentParentPage->setPrivilege($privilege);
        $parentParentPage->setId('parent-parent-id');
        $parentParentPage->setClass('parent-parent-class');
        $parentParentPage->setUri('###');
        $parentParentPage->setTarget('self-parent');
        $parentParentPage->setLabel($parentParentLabel);
        $parentParentPage->setTitle($parentParentTitle);
        $parentParentPage->setTextDomain($parentParentTextDomain);

        $page3 = $this->getMockBuilder(AbstractPage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $page = $this->getMockBuilder(AbstractPage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::once())
            ->method('isVisible')
            ->with(false)
            ->willReturn(true);
        $page->expects(self::never())
            ->method('getResource');
        $page->expects(self::never())
            ->method('getPrivilege');
        $page->expects(self::never())
            ->method('getParent');
        $page->expects(self::exactly(3))
            ->method('isActive')
            ->with(true)
            ->willReturn(true);
        $page->expects(self::once())
            ->method('getLabel')
            ->willReturn($pageLabel);
        $page->expects(self::once())
            ->method('getTextDomain')
            ->willReturn($pageTextDomain);
        $page->expects(self::once())
            ->method('getTitle')
            ->willReturn($pageTitle);
        $page->expects(self::once())
            ->method('getId')
            ->willReturn($pageId);
        $page->expects(self::exactly(2))
            ->method('getClass')
            ->willReturn('xxxx');
        $page->expects(self::exactly(2))
            ->method('getHref')
            ->willReturn($pageHref);
        $page->expects(self::once())
            ->method('getTarget')
            ->willReturn($pageTarget);
        $page->expects(self::once())
            ->method('hasPage')
            ->with($page3)
            ->willReturn(true);
        $page->expects(self::exactly(2))
            ->method('hasPages')
            ->withConsecutive([false], [true])
            ->willReturnOnConsecutiveCalls(false, false);
        $page->expects(self::once())
            ->method('getCustomProperties')
            ->willReturn([]);
        $page->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive(['li-active-class'], ['li-class'])
            ->willReturnOnConsecutiveCalls(null, null);

        $page2 = $this->getMockBuilder(AbstractPage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page2->expects(self::never())
            ->method('isVisible');
        $page2->expects(self::never())
            ->method('getResource');
        $page2->expects(self::never())
            ->method('getPrivilege');
        $page2->expects(self::never())
            ->method('getParent');
        $page2->expects(self::never())
            ->method('isActive');
        $page2->expects(self::never())
            ->method('getLabel');
        $page2->expects(self::never())
            ->method('getTextDomain');
        $page2->expects(self::never())
            ->method('getTitle');
        $page2->expects(self::never())
            ->method('getId');
        $page2->expects(self::never())
            ->method('getClass');
        $page2->expects(self::never())
            ->method('getHref');
        $page2->expects(self::never())
            ->method('getTarget');
        $page2->expects(self::never())
            ->method('hasPage');
        $page2->expects(self::once())
            ->method('hasPages')
            ->with(false)
            ->willReturn(false);
        $page2->expects(self::never())
            ->method('getCustomProperties');
        $page2->expects(self::never())
            ->method('get');

        $page3->expects(self::never())
            ->method('isVisible');
        $page3->expects(self::never())
            ->method('getResource');
        $page3->expects(self::never())
            ->method('getPrivilege');
        $page3->expects(self::never())
            ->method('getParent');
        $page3->expects(self::once())
            ->method('isActive')
            ->with(true)
            ->willReturn(false);
        $page3->expects(self::once())
            ->method('getLabel')
            ->willReturn($page3Label);
        $page3->expects(self::once())
            ->method('getTextDomain')
            ->willReturn($page3TextDomain);
        $page3->expects(self::once())
            ->method('getTitle')
            ->willReturn($page3Title);
        $page3->expects(self::once())
            ->method('getId')
            ->willReturn($page3Id);
        $page3->expects(self::exactly(2))
            ->method('getClass')
            ->willReturn('xxxx3');
        $page3->expects(self::exactly(2))
            ->method('getHref')
            ->willReturn($page3Href);
        $page3->expects(self::once())
            ->method('getTarget')
            ->willReturn($page3Target);
        $page3->expects(self::never())
            ->method('hasPage');
        $page3->expects(self::exactly(2))
            ->method('hasPages')
            ->withConsecutive([false], [true])
            ->willReturnOnConsecutiveCalls(false, false);
        $page3->expects(self::once())
            ->method('getCustomProperties')
            ->willReturn([]);
        $page3->expects(self::once())
            ->method('get')
            ->with('li-class')
            ->willReturn(null);

        $parentPage->addPage($page);
        $parentParentPage->addPage($parentPage);
        $parentParentPage->addPage($page2);
        $parentParentPage->addPage($page3);

        $container = new Navigation();
        $container->addPage($parentParentPage);

        $role = 'testRole';

        $findActiveHelper = $this->getMockBuilder(FindActiveInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, 0, null)
            ->willReturn(
                [
                    'page' => $page,
                    'depth' => 1,
                ]
            );

        $acceptHelper = $this->getMockBuilder(AcceptHelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $acceptHelper->expects(self::exactly(9))
            ->method('accept')
            ->withConsecutive([$parentParentPage], [$parentPage], [$page], [$page], [$parentPage], [$page], [$page], [$page2], [$page3])
            ->willReturnOnConsecutiveCalls(true, true, true, true, true, true, true, false, true);

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
        $serviceLocator->expects(self::exactly(10))
            ->method('build')
            ->withConsecutive(
                [
                    FindActiveInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ]
            )
            ->willReturnOnConsecutiveCalls(
                $findActiveHelper,
                $acceptHelper,
                $acceptHelper,
                $acceptHelper,
                $acceptHelper,
                $acceptHelper,
                $acceptHelper,
                $acceptHelper,
                $acceptHelper,
                $acceptHelper
            );

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::exactly(2))
            ->method('parseContainer')
            ->withConsecutive([$name], [$container])
            ->willReturnOnConsecutiveCalls($container, $container);

        $escapeHtmlAttr = $this->getMockBuilder(EscapeHtmlAttr::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtmlAttr->expects(self::exactly(8))
            ->method('__invoke')
            ->withConsecutive(['nav navigation'], ['nav-item dropend active'], ['dropdown-menu'], ['parent-parent-id'], ['dropend active'], ['dropdown-menu'], ['parent-id'], ['active'])
            ->willReturnOnConsecutiveCalls('nav-escaped navigation-escaped', 'nav-item-escaped dropdown-escaped active-escaped', 'dropdown-menu-escaped', 'parent-parent-id-escaped', 'dropdown-escaped active-escaped', 'dropdown-menu-escaped', 'parent-id-escaped', 'active-escaped');

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::exactly(4))
            ->method('__invoke')
            ->withConsecutive([$parentParentTranslatedLabel], [$parentTranslatedLabel], [$pageLabelTranslated], [$page3LabelTranslated])
            ->willReturnOnConsecutiveCalls($parentParentTranslatedLabelEscaped, $parentTranslatedLabelEscaped, $pageLabelTranslatedEscaped, $page3LabelTranslatedEscaped);

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
        $translator->expects(self::exactly(8))
            ->method('translate')
            ->withConsecutive([$parentParentLabel, $parentParentTextDomain], [$parentParentTitle, $parentParentTextDomain], [$parentLabel, $parentTextDomain], [$parentTitle, $parentTextDomain], [$pageLabel, $pageTextDomain], [$pageTitle, $pageTextDomain], [$page3Label, $page3TextDomain], [$page3Title, $page3TextDomain])
            ->willReturnOnConsecutiveCalls($parentParentTranslatedLabel, $parentParentTranslatedTitle, $parentTranslatedLabel, $parentTranslatedTitle, $pageLabelTranslated, $pageTitleTranslated, $page3LabelTranslated, $page3TitleTranslated);

        $expected = '<ul class="nav-escaped navigation-escaped">' . PHP_EOL . '    <li class="nav-item-escaped dropdown-escaped active-escaped">' . PHP_EOL . '        <a parent-id-escaped="parent-id-escaped" parent-title-escaped="parent-title-escaped" parent-class-escaped="parent-class-escaped" parent-href-escaped="##-escaped" parent-target-escaped="self-escaped">parent-label-escaped</a>' . PHP_EOL . '        <ul class="dropdown-menu-escaped" aria-labelledby="parent-parent-id-escaped">' . PHP_EOL . '            <li class="dropdown-escaped active-escaped">' . PHP_EOL . '                <a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>' . PHP_EOL . '                <ul class="dropdown-menu-escaped" aria-labelledby="parent-id-escaped">' . PHP_EOL . '                    <li class="active-escaped">' . PHP_EOL . '                        <a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>' . PHP_EOL . '                    </li>' . PHP_EOL . '                </ul>' . PHP_EOL . '            </li>' . PHP_EOL . '            <li>' . PHP_EOL . '                <a idEscaped="test3IdEscaped" titleEscaped="test3TitleTranslatedAndEscaped" classEscaped="test3ClassEscaped" hrefEscaped="#3Escaped">test3LabelTranslatedAndEscaped</a>' . PHP_EOL . '            </li>' . PHP_EOL . '        </ul>' . PHP_EOL . '    </li>' . PHP_EOL . '</ul>';

        $expected1 = '<a parent-id-escaped="parent-id-escaped" parent-title-escaped="parent-title-escaped" parent-class-escaped="parent-class-escaped" parent-href-escaped="##-escaped" parent-target-escaped="self-escaped">parent-label-escaped</a>';
        $expected2 = '<a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>';
        $expected3 = '<a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>';
        $expected5 = '<a idEscaped="test3IdEscaped" titleEscaped="test3TitleTranslatedAndEscaped" classEscaped="test3ClassEscaped" hrefEscaped="#3Escaped">test3LabelTranslatedAndEscaped</a>';

        $htmlElement = $this->getMockBuilder(HtmlElementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlElement->expects(self::exactly(4))
            ->method('toHtml')
            ->withConsecutive(
                ['button', ['aria-current' => 'page', 'class' => 'nav-link btn dropdown-toggle parent-parent-class', 'id' => 'parent-parent-id', 'title' => $parentParentTranslatedTitle, 'data-bs-toggle' => 'dropdown', 'aria-expanded' => 'false', 'role' => 'button', 'type' => 'button'], $parentParentTranslatedLabelEscaped],
                ['button', ['class' => 'dropdown-item btn dropdown-toggle parent-class', 'id' => 'parent-id', 'title' => $parentTranslatedTitle, 'data-bs-toggle' => 'dropdown', 'aria-expanded' => 'false', 'role' => 'button', 'type' => 'button'], $parentTranslatedLabelEscaped],
                ['a', ['class' => 'dropdown-item xxxx', 'id' => $pageId, 'title' => $pageTitleTranslated, 'href' => $pageHref, 'target' => $pageTarget], $pageLabelTranslatedEscaped],
                ['a', ['class' => 'dropdown-item xxxx3', 'id' => $page3Id, 'title' => $page3TitleTranslated, 'href' => $page3Href, 'target' => $page3Target], $page3LabelTranslatedEscaped]
            )
            ->willReturnOnConsecutiveCalls($expected1, $expected2, $expected3, $expected5);

        $helper = new Menu(
            $serviceLocator,
            $logger,
            $containerParser,
            $escapeHtmlAttr,
            $renderer,
            $escapeHtml,
            $htmlElement
        );

        $helper->setRole($role);
        $helper->setTranslator($translator);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        self::assertSame($expected, $helper->renderMenu($name, ['onlyActiveBranch' => true, 'direction' => Menu::DROP_ORIENTATION_END, 'sublink' => Menu::STYLE_SUBLINK_BUTTON]));
    }

    /**
     * @throws Exception
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     */
    public function testRenderMenu5(): void
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

        $parentLabel                  = 'parent-label';
        $parentTranslatedLabel        = 'parent-label-translated';
        $parentTranslatedLabelEscaped = 'parent-label-translated-escaped';
        $parentTextDomain             = 'parent-text-domain';
        $parentTitle                  = 'parent-title';
        $parentTranslatedTitle        = 'parent-title-translated';

        $parentParentLabel                  = 'parent-parent-label';
        $parentParentTranslatedLabel        = 'parent-parent-label-translated';
        $parentParentTranslatedLabelEscaped = 'parent-parent-label-translated-escaped';
        $parentParentTextDomain             = 'parent-parent-text-domain';
        $parentParentTitle                  = 'parent-parent-title';
        $parentParentTranslatedTitle        = 'parent-parent-title-translated';

        $pageLabel                  = 'page-label';
        $pageLabelTranslated        = 'page-label-translated';
        $pageLabelTranslatedEscaped = 'page-label-translated-escaped';
        $pageTitle                  = 'page-title';
        $pageTitleTranslated        = 'page-title-translated';
        $pageTextDomain             = 'page-text-domain';
        $pageId                     = 'page-id';
        $pageHref                   = 'http://page';
        $pageTarget                 = 'page-target';

        $page2Label                  = 'page2-label';
        $page2LabelTranslated        = 'page2-label-translated';
        $page2LabelTranslatedEscaped = 'page2-label-translated-escaped';
        $page2Title                  = 'page2-title';
        $page2TitleTranslated        = 'page2-title-translated';
        $page2TextDomain             = 'page2-text-domain';
        $page2Id                     = 'page2-id';
        $page2Href                   = 'http://page2';
        $page2Target                 = 'page2-target';

        $page3Label                  = 'page3-label';
        $page3LabelTranslated        = 'page3-label-translated';
        $page3LabelTranslatedEscaped = 'page3-label-translated-escaped';
        $page3Title                  = 'page3-title';
        $page3TitleTranslated        = 'page3-title-translated';
        $page3TextDomain             = 'page3-text-domain';
        $page3Id                     = 'page3-id';
        $page3Href                   = 'http://page3';
        $page3Target                 = 'page3-target';

        $parentPage = new Uri();
        $parentPage->setVisible(true);
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);
        $parentPage->setId('parent-id');
        $parentPage->setClass('parent-class');
        $parentPage->setUri('##');
        $parentPage->setTarget('self');
        $parentPage->setLabel($parentLabel);
        $parentPage->setTitle($parentTitle);
        $parentPage->setTextDomain($parentTextDomain);

        $parentParentPage = new Uri();
        $parentParentPage->setVisible(true);
        $parentParentPage->setResource($resource);
        $parentParentPage->setPrivilege($privilege);
        $parentParentPage->setId('parent-parent-id');
        $parentParentPage->setClass('parent-parent-class');
        $parentParentPage->setUri('###');
        $parentParentPage->setTarget('self-parent');
        $parentParentPage->setLabel($parentParentLabel);
        $parentParentPage->setTitle($parentParentTitle);
        $parentParentPage->setTextDomain($parentParentTextDomain);

        $page = $this->getMockBuilder(AbstractPage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::once())
            ->method('isVisible')
            ->with(false)
            ->willReturn(true);
        $page->expects(self::never())
            ->method('getResource');
        $page->expects(self::never())
            ->method('getPrivilege');
        $page->expects(self::never())
            ->method('getParent');
        $page->expects(self::exactly(3))
            ->method('isActive')
            ->with(true)
            ->willReturn(true);
        $page->expects(self::once())
            ->method('getLabel')
            ->willReturn($pageLabel);
        $page->expects(self::once())
            ->method('getTextDomain')
            ->willReturn($pageTextDomain);
        $page->expects(self::once())
            ->method('getTitle')
            ->willReturn($pageTitle);
        $page->expects(self::once())
            ->method('getId')
            ->willReturn($pageId);
        $page->expects(self::exactly(2))
            ->method('getClass')
            ->willReturn('xxxx');
        $page->expects(self::exactly(2))
            ->method('getHref')
            ->willReturn($pageHref);
        $page->expects(self::once())
            ->method('getTarget')
            ->willReturn($pageTarget);
        $page->expects(self::never())
            ->method('hasPage');
        $page->expects(self::exactly(2))
            ->method('hasPages')
            ->withConsecutive([false], [true])
            ->willReturnOnConsecutiveCalls(false, false);
        $page->expects(self::once())
            ->method('getCustomProperties')
            ->willReturn([]);
        $page->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive(['li-active-class'], ['li-class'])
            ->willReturnOnConsecutiveCalls(null, null);

        $page2 = $this->getMockBuilder(AbstractPage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page2->expects(self::never())
            ->method('isVisible');
        $page2->expects(self::never())
            ->method('getResource');
        $page2->expects(self::never())
            ->method('getPrivilege');
        $page2->expects(self::never())
            ->method('getParent');
        $page2->expects(self::once())
            ->method('isActive')
            ->with(true)
            ->willReturn(true);
        $page2->expects(self::once())
            ->method('getLabel')
            ->willReturn($page2Label);
        $page2->expects(self::once())
            ->method('getTextDomain')
            ->willReturn($page2TextDomain);
        $page2->expects(self::once())
            ->method('getTitle')
            ->willReturn($page2Title);
        $page2->expects(self::once())
            ->method('getId')
            ->willReturn($page2Id);
        $page2->expects(self::exactly(2))
            ->method('getClass')
            ->willReturn('xxxx2');
        $page2->expects(self::exactly(2))
            ->method('getHref')
            ->willReturn($page2Href);
        $page2->expects(self::once())
            ->method('getTarget')
            ->willReturn($page2Target);
        $page2->expects(self::never())
            ->method('hasPage');
        $page2->expects(self::exactly(2))
            ->method('hasPages')
            ->withConsecutive([false], [true])
            ->willReturnOnConsecutiveCalls(false, false);
        $page2->expects(self::once())
            ->method('getCustomProperties')
            ->willReturn([]);
        $page2->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive(['li-active-class'], ['li-class'])
            ->willReturnOnConsecutiveCalls(null, null);

        $page3 = $this->getMockBuilder(AbstractPage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page3->expects(self::never())
            ->method('isVisible');
        $page3->expects(self::never())
            ->method('getResource');
        $page3->expects(self::never())
            ->method('getPrivilege');
        $page3->expects(self::never())
            ->method('getParent');
        $page3->expects(self::once())
            ->method('isActive')
            ->with(true)
            ->willReturn(true);
        $page3->expects(self::once())
            ->method('getLabel')
            ->willReturn($page3Label);
        $page3->expects(self::once())
            ->method('getTextDomain')
            ->willReturn($page3TextDomain);
        $page3->expects(self::once())
            ->method('getTitle')
            ->willReturn($page3Title);
        $page3->expects(self::once())
            ->method('getId')
            ->willReturn($page3Id);
        $page3->expects(self::exactly(2))
            ->method('getClass')
            ->willReturn('xxxx3');
        $page3->expects(self::exactly(2))
            ->method('getHref')
            ->willReturn($page3Href);
        $page3->expects(self::once())
            ->method('getTarget')
            ->willReturn($page3Target);
        $page3->expects(self::never())
            ->method('hasPage');
        $page3->expects(self::exactly(2))
            ->method('hasPages')
            ->withConsecutive([false], [true])
            ->willReturnOnConsecutiveCalls(false, false);
        $page3->expects(self::once())
            ->method('getCustomProperties')
            ->willReturn([]);
        $page3->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive(['li-active-class'], ['li-class'])
            ->willReturnOnConsecutiveCalls(null, null);

        $parentPage->addPage($page);
        $parentParentPage->addPage($parentPage);
        $parentParentPage->addPage($page2);
        $parentParentPage->addPage($page3);

        $container = new Navigation();
        $container->addPage($parentParentPage);

        $role = 'testRole';

        $findActiveHelper = $this->getMockBuilder(FindActiveInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, 0, null)
            ->willReturn(
                [
                    'page' => $page,
                    'depth' => 1,
                ]
            );

        $acceptHelper = $this->getMockBuilder(AcceptHelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $acceptHelper->expects(self::exactly(9))
            ->method('accept')
            ->withConsecutive([$parentParentPage], [$parentPage], [$page], [$page], [$parentPage], [$page], [$page], [$page2], [$page3])
            ->willReturnOnConsecutiveCalls(true, true, true, true, true, true, true, true, true);

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
        $serviceLocator->expects(self::exactly(10))
            ->method('build')
            ->withConsecutive(
                [
                    FindActiveInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ]
            )
            ->willReturnOnConsecutiveCalls(
                $findActiveHelper,
                $acceptHelper,
                $acceptHelper,
                $acceptHelper,
                $acceptHelper,
                $acceptHelper,
                $acceptHelper,
                $acceptHelper,
                $acceptHelper,
                $acceptHelper
            );

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::exactly(2))
            ->method('parseContainer')
            ->withConsecutive([$name], [$container])
            ->willReturnOnConsecutiveCalls($container, $container);

        $escapeHtmlAttr = $this->getMockBuilder(EscapeHtmlAttr::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtmlAttr->expects(self::exactly(10))
            ->method('__invoke')
            ->withConsecutive(['nav navigation'], ['nav-item dropend active'], ['dropdown-menu'], ['parent-parent-id'], ['dropend active'], ['dropdown-menu'], ['parent-id'], ['active'], ['active'], ['active'])
            ->willReturnOnConsecutiveCalls('nav-escaped navigation-escaped', 'nav-item-escaped dropdown-escaped active-escaped', 'dropdown-menu-escaped', 'parent-parent-id-escaped', 'dropdown-escaped active-escaped', 'dropdown-menu-escaped', 'parent-id-escaped', 'active-escaped', 'active-escaped', 'active-escaped');

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::exactly(5))
            ->method('__invoke')
            ->withConsecutive([$parentParentTranslatedLabel], [$parentTranslatedLabel], [$pageLabelTranslated], [$page2LabelTranslated], [$page3LabelTranslated])
            ->willReturnOnConsecutiveCalls($parentParentTranslatedLabelEscaped, $parentTranslatedLabelEscaped, $pageLabelTranslatedEscaped, $page2LabelTranslatedEscaped, $page3LabelTranslatedEscaped);

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
        $translator->expects(self::exactly(10))
            ->method('translate')
            ->withConsecutive([$parentParentLabel, $parentParentTextDomain], [$parentParentTitle, $parentParentTextDomain], [$parentLabel, $parentTextDomain], [$parentTitle, $parentTextDomain], [$pageLabel, $pageTextDomain], [$pageTitle, $pageTextDomain], [$page2Label, $page2TextDomain], [$page2Title, $page2TextDomain], [$page3Label, $page3TextDomain], [$page3Title, $page3TextDomain])
            ->willReturnOnConsecutiveCalls($parentParentTranslatedLabel, $parentParentTranslatedTitle, $parentTranslatedLabel, $parentTranslatedTitle, $pageLabelTranslated, $pageTitleTranslated, $page2LabelTranslated, $page2TitleTranslated, $page3LabelTranslated, $page3TitleTranslated);

        $expected = '<ul class="nav-escaped navigation-escaped">' . PHP_EOL . '    <li class="nav-item-escaped dropdown-escaped active-escaped">' . PHP_EOL . '        <a parent-id-escaped="parent-id-escaped" parent-title-escaped="parent-title-escaped" parent-class-escaped="parent-class-escaped" parent-href-escaped="##-escaped" parent-target-escaped="self-escaped">parent-label-escaped</a>' . PHP_EOL . '        <ul class="dropdown-menu-escaped" aria-labelledby="parent-parent-id-escaped">' . PHP_EOL . '            <li class="dropdown-escaped active-escaped">' . PHP_EOL . '                <a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>' . PHP_EOL . '                <ul class="dropdown-menu-escaped" aria-labelledby="parent-id-escaped">' . PHP_EOL . '                    <li class="active-escaped">' . PHP_EOL . '                        <a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>' . PHP_EOL . '                    </li>' . PHP_EOL . '                </ul>' . PHP_EOL . '            </li>' . PHP_EOL . '            <li class="active-escaped">' . PHP_EOL . '                <a idEscaped="test2IdEscaped" titleEscaped="test2TitleTranslatedAndEscaped" classEscaped="test2ClassEscaped" hrefEscaped="#2Escaped">test2LabelTranslatedAndEscaped</a>' . PHP_EOL . '            </li>' . PHP_EOL . '            <li class="active-escaped">' . PHP_EOL . '                <a idEscaped="test3IdEscaped" titleEscaped="test3TitleTranslatedAndEscaped" classEscaped="test3ClassEscaped" hrefEscaped="#3Escaped">test3LabelTranslatedAndEscaped</a>' . PHP_EOL . '            </li>' . PHP_EOL . '        </ul>' . PHP_EOL . '    </li>' . PHP_EOL . '</ul>';

        $expected1 = '<a parent-id-escaped="parent-id-escaped" parent-title-escaped="parent-title-escaped" parent-class-escaped="parent-class-escaped" parent-href-escaped="##-escaped" parent-target-escaped="self-escaped">parent-label-escaped</a>';
        $expected2 = '<a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>';
        $expected3 = '<a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>';
        $expected4 = '<a idEscaped="test2IdEscaped" titleEscaped="test2TitleTranslatedAndEscaped" classEscaped="test2ClassEscaped" hrefEscaped="#2Escaped">test2LabelTranslatedAndEscaped</a>';
        $expected5 = '<a idEscaped="test3IdEscaped" titleEscaped="test3TitleTranslatedAndEscaped" classEscaped="test3ClassEscaped" hrefEscaped="#3Escaped">test3LabelTranslatedAndEscaped</a>';

        $htmlElement = $this->getMockBuilder(HtmlElementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlElement->expects(self::exactly(5))
            ->method('toHtml')
            ->withConsecutive(
                ['button', ['aria-current' => 'page', 'class' => 'nav-link btn dropdown-toggle parent-parent-class', 'id' => 'parent-parent-id', 'title' => $parentParentTranslatedTitle, 'data-bs-toggle' => 'dropdown', 'aria-expanded' => 'false', 'role' => 'button', 'type' => 'button'], $parentParentTranslatedLabelEscaped],
                ['button', ['class' => 'dropdown-item btn dropdown-toggle parent-class', 'id' => 'parent-id', 'title' => $parentTranslatedTitle, 'data-bs-toggle' => 'dropdown', 'aria-expanded' => 'false', 'role' => 'button', 'type' => 'button'], $parentTranslatedLabelEscaped],
                ['a', ['class' => 'dropdown-item xxxx', 'id' => $pageId, 'title' => $pageTitleTranslated, 'href' => $pageHref, 'target' => $pageTarget], $pageLabelTranslatedEscaped],
                ['a', ['class' => 'dropdown-item xxxx2', 'id' => $page2Id, 'title' => $page2TitleTranslated, 'href' => $page2Href, 'target' => $page2Target], $page2LabelTranslatedEscaped],
                ['a', ['class' => 'dropdown-item xxxx3', 'id' => $page3Id, 'title' => $page3TitleTranslated, 'href' => $page3Href, 'target' => $page3Target], $page3LabelTranslatedEscaped]
            )
            ->willReturnOnConsecutiveCalls($expected1, $expected2, $expected3, $expected4, $expected5);

        $helper = new Menu(
            $serviceLocator,
            $logger,
            $containerParser,
            $escapeHtmlAttr,
            $renderer,
            $escapeHtml,
            $htmlElement
        );

        $helper->setRole($role);
        $helper->setTranslator($translator);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        self::assertSame($expected, $helper->renderMenu($name, ['direction' => Menu::DROP_ORIENTATION_END, 'sublink' => Menu::STYLE_SUBLINK_BUTTON]));
    }

    /**
     * @throws Exception
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     */
    public function testRenderMenu6(): void
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

        $parentLabel                  = 'parent-label';
        $parentTranslatedLabel        = 'parent-label-translated';
        $parentTranslatedLabelEscaped = 'parent-label-translated-escaped';
        $parentTextDomain             = 'parent-text-domain';
        $parentTitle                  = 'parent-title';
        $parentTranslatedTitle        = 'parent-title-translated';

        $parentParentLabel                  = 'parent-parent-label';
        $parentParentTranslatedLabel        = 'parent-parent-label-translated';
        $parentParentTranslatedLabelEscaped = 'parent-parent-label-translated-escaped';
        $parentParentTextDomain             = 'parent-parent-text-domain';
        $parentParentTitle                  = 'parent-parent-title';
        $parentParentTranslatedTitle        = 'parent-parent-title-translated';

        $pageLabel                  = 'page-label';
        $pageLabelTranslated        = 'page-label-translated';
        $pageLabelTranslatedEscaped = 'page-label-translated-escaped';
        $pageTitle                  = 'page-title';
        $pageTitleTranslated        = 'page-title-translated';
        $pageTextDomain             = 'page-text-domain';
        $pageId                     = 'page-id';
        $pageHref                   = 'http://page';
        $pageTarget                 = 'page-target';

        $page2Label                  = 'page2-label';
        $page2LabelTranslated        = 'page2-label-translated';
        $page2LabelTranslatedEscaped = 'page2-label-translated-escaped';
        $page2Title                  = 'page2-title';
        $page2TitleTranslated        = 'page2-title-translated';
        $page2TextDomain             = 'page2-text-domain';
        $page2Id                     = 'page2-id';
        $page2Href                   = 'http://page2';
        $page2Target                 = 'page2-target';

        $page3Label                  = 'page3-label';
        $page3LabelTranslated        = 'page3-label-translated';
        $page3LabelTranslatedEscaped = 'page3-label-translated-escaped';
        $page3Title                  = 'page3-title';
        $page3TitleTranslated        = 'page3-title-translated';
        $page3TextDomain             = 'page3-text-domain';
        $page3Id                     = 'page3-id';
        $page3Href                   = 'http://page3';
        $page3Target                 = 'page3-target';

        $parentPage = new Uri();
        $parentPage->setVisible(true);
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);
        $parentPage->setId('parent-id');
        $parentPage->setClass('parent-class');
        $parentPage->setUri('##');
        $parentPage->setTarget('self');
        $parentPage->setLabel($parentLabel);
        $parentPage->setTitle($parentTitle);
        $parentPage->setTextDomain($parentTextDomain);

        $parentParentPage = new Uri();
        $parentParentPage->setVisible(true);
        $parentParentPage->setResource($resource);
        $parentParentPage->setPrivilege($privilege);
        $parentParentPage->setId('parent-parent-id');
        $parentParentPage->setClass('parent-parent-class');
        $parentParentPage->setUri('###');
        $parentParentPage->setTarget('self-parent');
        $parentParentPage->setLabel($parentParentLabel);
        $parentParentPage->setTitle($parentParentTitle);
        $parentParentPage->setTextDomain($parentParentTextDomain);

        $page = $this->getMockBuilder(AbstractPage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::once())
            ->method('isVisible')
            ->with(false)
            ->willReturn(true);
        $page->expects(self::never())
            ->method('getResource');
        $page->expects(self::never())
            ->method('getPrivilege');
        $page->expects(self::never())
            ->method('getParent');
        $page->expects(self::exactly(3))
            ->method('isActive')
            ->with(true)
            ->willReturn(true);
        $page->expects(self::once())
            ->method('getLabel')
            ->willReturn($pageLabel);
        $page->expects(self::once())
            ->method('getTextDomain')
            ->willReturn($pageTextDomain);
        $page->expects(self::once())
            ->method('getTitle')
            ->willReturn($pageTitle);
        $page->expects(self::once())
            ->method('getId')
            ->willReturn($pageId);
        $page->expects(self::exactly(2))
            ->method('getClass')
            ->willReturn('xxxx');
        $page->expects(self::exactly(2))
            ->method('getHref')
            ->willReturn($pageHref);
        $page->expects(self::once())
            ->method('getTarget')
            ->willReturn($pageTarget);
        $page->expects(self::never())
            ->method('hasPage');
        $page->expects(self::exactly(2))
            ->method('hasPages')
            ->withConsecutive([false], [true])
            ->willReturnOnConsecutiveCalls(false, false);
        $page->expects(self::once())
            ->method('getCustomProperties')
            ->willReturn([]);
        $page->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive(['li-active-class'], ['li-class'])
            ->willReturnOnConsecutiveCalls(null, null);

        $page2 = $this->getMockBuilder(AbstractPage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page2->expects(self::never())
            ->method('isVisible');
        $page2->expects(self::never())
            ->method('getResource');
        $page2->expects(self::never())
            ->method('getPrivilege');
        $page2->expects(self::never())
            ->method('getParent');
        $page2->expects(self::once())
            ->method('isActive')
            ->with(true)
            ->willReturn(true);
        $page2->expects(self::once())
            ->method('getLabel')
            ->willReturn($page2Label);
        $page2->expects(self::once())
            ->method('getTextDomain')
            ->willReturn($page2TextDomain);
        $page2->expects(self::once())
            ->method('getTitle')
            ->willReturn($page2Title);
        $page2->expects(self::once())
            ->method('getId')
            ->willReturn($page2Id);
        $page2->expects(self::exactly(2))
            ->method('getClass')
            ->willReturn('xxxx2');
        $page2->expects(self::exactly(2))
            ->method('getHref')
            ->willReturn($page2Href);
        $page2->expects(self::once())
            ->method('getTarget')
            ->willReturn($page2Target);
        $page2->expects(self::never())
            ->method('hasPage');
        $page2->expects(self::exactly(2))
            ->method('hasPages')
            ->withConsecutive([false], [true])
            ->willReturnOnConsecutiveCalls(false, false);
        $page2->expects(self::once())
            ->method('getCustomProperties')
            ->willReturn([]);
        $page2->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive(['li-active-class'], ['li-class'])
            ->willReturnOnConsecutiveCalls(null, null);

        $page3 = $this->getMockBuilder(AbstractPage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page3->expects(self::never())
            ->method('isVisible');
        $page3->expects(self::never())
            ->method('getResource');
        $page3->expects(self::never())
            ->method('getPrivilege');
        $page3->expects(self::never())
            ->method('getParent');
        $page3->expects(self::once())
            ->method('isActive')
            ->with(true)
            ->willReturn(true);
        $page3->expects(self::once())
            ->method('getLabel')
            ->willReturn($page3Label);
        $page3->expects(self::once())
            ->method('getTextDomain')
            ->willReturn($page3TextDomain);
        $page3->expects(self::once())
            ->method('getTitle')
            ->willReturn($page3Title);
        $page3->expects(self::once())
            ->method('getId')
            ->willReturn($page3Id);
        $page3->expects(self::exactly(2))
            ->method('getClass')
            ->willReturn('xxxx3');
        $page3->expects(self::exactly(2))
            ->method('getHref')
            ->willReturn($page3Href);
        $page3->expects(self::once())
            ->method('getTarget')
            ->willReturn($page3Target);
        $page3->expects(self::never())
            ->method('hasPage');
        $page3->expects(self::exactly(2))
            ->method('hasPages')
            ->withConsecutive([false], [true])
            ->willReturnOnConsecutiveCalls(false, false);
        $page3->expects(self::once())
            ->method('getCustomProperties')
            ->willReturn([]);
        $page3->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive(['li-active-class'], ['li-class'])
            ->willReturnOnConsecutiveCalls(null, null);

        $parentPage->addPage($page);
        $parentParentPage->addPage($parentPage);
        $parentParentPage->addPage($page2);
        $parentParentPage->addPage($page3);

        $container = new Navigation();
        $container->addPage($parentParentPage);

        $role = 'testRole';

        $findActiveHelper = $this->getMockBuilder(FindActiveInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, 0, null)
            ->willReturn(
                [
                    'page' => $page,
                    'depth' => 1,
                ]
            );

        $acceptHelper = $this->getMockBuilder(AcceptHelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $acceptHelper->expects(self::exactly(9))
            ->method('accept')
            ->withConsecutive([$parentParentPage], [$parentPage], [$page], [$page], [$parentPage], [$page], [$page], [$page2], [$page3])
            ->willReturnOnConsecutiveCalls(true, true, true, true, true, true, true, true, true);

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
        $serviceLocator->expects(self::exactly(10))
            ->method('build')
            ->withConsecutive(
                [
                    FindActiveInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ]
            )
            ->willReturnOnConsecutiveCalls(
                $findActiveHelper,
                $acceptHelper,
                $acceptHelper,
                $acceptHelper,
                $acceptHelper,
                $acceptHelper,
                $acceptHelper,
                $acceptHelper,
                $acceptHelper,
                $acceptHelper
            );

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::exactly(2))
            ->method('parseContainer')
            ->withConsecutive([$name], [$container])
            ->willReturnOnConsecutiveCalls($container, $container);

        $escapeHtmlAttr = $this->getMockBuilder(EscapeHtmlAttr::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtmlAttr->expects(self::exactly(10))
            ->method('__invoke')
            ->withConsecutive(['nav navigation'], ['nav-item dropup active'], ['dropdown-details-menu'], ['parent-parent-id'], ['dropup active'], ['dropdown-details-menu'], ['parent-id'], ['active'], ['active'], ['active'])
            ->willReturnOnConsecutiveCalls('nav-escaped navigation-escaped', 'nav-item-escaped dropdown-escaped active-escaped', 'dropdown-menu-escaped', 'parent-parent-id-escaped', 'dropdown-escaped active-escaped', 'dropdown-menu-escaped', 'parent-id-escaped', 'active-escaped', 'active-escaped', 'active-escaped');

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::exactly(5))
            ->method('__invoke')
            ->withConsecutive([$parentParentTranslatedLabel], [$parentTranslatedLabel], [$pageLabelTranslated], [$page2LabelTranslated], [$page3LabelTranslated])
            ->willReturnOnConsecutiveCalls($parentParentTranslatedLabelEscaped, $parentTranslatedLabelEscaped, $pageLabelTranslatedEscaped, $page2LabelTranslatedEscaped, $page3LabelTranslatedEscaped);

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
        $translator->expects(self::exactly(10))
            ->method('translate')
            ->withConsecutive([$parentParentLabel, $parentParentTextDomain], [$parentParentTitle, $parentParentTextDomain], [$parentLabel, $parentTextDomain], [$parentTitle, $parentTextDomain], [$pageLabel, $pageTextDomain], [$pageTitle, $pageTextDomain], [$page2Label, $page2TextDomain], [$page2Title, $page2TextDomain], [$page3Label, $page3TextDomain], [$page3Title, $page3TextDomain])
            ->willReturnOnConsecutiveCalls($parentParentTranslatedLabel, $parentParentTranslatedTitle, $parentTranslatedLabel, $parentTranslatedTitle, $pageLabelTranslated, $pageTitleTranslated, $page2LabelTranslated, $page2TitleTranslated, $page3LabelTranslated, $page3TitleTranslated);

        $expected = '<ul class="nav-escaped navigation-escaped">' . PHP_EOL . '    <li class="nav-item-escaped dropdown-escaped active-escaped">' . PHP_EOL . '        <details>' . PHP_EOL . '        <a parent-id-escaped="parent-id-escaped" parent-title-escaped="parent-title-escaped" parent-class-escaped="parent-class-escaped" parent-href-escaped="##-escaped" parent-target-escaped="self-escaped">parent-label-escaped</a>' . PHP_EOL . '        <ul class="dropdown-menu-escaped" aria-labelledby="parent-parent-id-escaped">' . PHP_EOL . '            <li class="dropdown-escaped active-escaped">' . PHP_EOL . '                <details>' . PHP_EOL . '                <a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>' . PHP_EOL . '                <ul class="dropdown-menu-escaped" aria-labelledby="parent-id-escaped">' . PHP_EOL . '                    <li class="active-escaped">' . PHP_EOL . '                        <a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>' . PHP_EOL . '                    </li>' . PHP_EOL . '                </ul>' . PHP_EOL . '                </details>' . PHP_EOL . '            </li>' . PHP_EOL . '            <li class="active-escaped">' . PHP_EOL . '                <a idEscaped="test2IdEscaped" titleEscaped="test2TitleTranslatedAndEscaped" classEscaped="test2ClassEscaped" hrefEscaped="#2Escaped">test2LabelTranslatedAndEscaped</a>' . PHP_EOL . '            </li>' . PHP_EOL . '            <li class="active-escaped">' . PHP_EOL . '                <a idEscaped="test3IdEscaped" titleEscaped="test3TitleTranslatedAndEscaped" classEscaped="test3ClassEscaped" hrefEscaped="#3Escaped">test3LabelTranslatedAndEscaped</a>' . PHP_EOL . '            </li>' . PHP_EOL . '        </ul>' . PHP_EOL . '        </details>' . PHP_EOL . '    </li>' . PHP_EOL . '</ul>';

        $expected1 = '<a parent-id-escaped="parent-id-escaped" parent-title-escaped="parent-title-escaped" parent-class-escaped="parent-class-escaped" parent-href-escaped="##-escaped" parent-target-escaped="self-escaped">parent-label-escaped</a>';
        $expected2 = '<a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>';
        $expected3 = '<a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>';
        $expected4 = '<a idEscaped="test2IdEscaped" titleEscaped="test2TitleTranslatedAndEscaped" classEscaped="test2ClassEscaped" hrefEscaped="#2Escaped">test2LabelTranslatedAndEscaped</a>';
        $expected5 = '<a idEscaped="test3IdEscaped" titleEscaped="test3TitleTranslatedAndEscaped" classEscaped="test3ClassEscaped" hrefEscaped="#3Escaped">test3LabelTranslatedAndEscaped</a>';

        $htmlElement = $this->getMockBuilder(HtmlElementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlElement->expects(self::exactly(5))
            ->method('toHtml')
            ->withConsecutive(
                ['summary', ['aria-current' => 'page', 'class' => 'nav-link btn parent-parent-class', 'id' => 'parent-parent-id', 'title' => $parentParentTranslatedTitle, 'aria-expanded' => 'false', 'role' => 'button'], $parentParentTranslatedLabelEscaped],
                ['summary', ['class' => 'dropdown-item btn parent-class', 'id' => 'parent-id', 'title' => $parentTranslatedTitle, 'aria-expanded' => 'false', 'role' => 'button'], $parentTranslatedLabelEscaped],
                ['a', ['class' => 'dropdown-item xxxx', 'id' => $pageId, 'title' => $pageTitleTranslated, 'href' => $pageHref, 'target' => $pageTarget], $pageLabelTranslatedEscaped],
                ['a', ['class' => 'dropdown-item xxxx2', 'id' => $page2Id, 'title' => $page2TitleTranslated, 'href' => $page2Href, 'target' => $page2Target], $page2LabelTranslatedEscaped],
                ['a', ['class' => 'dropdown-item xxxx3', 'id' => $page3Id, 'title' => $page3TitleTranslated, 'href' => $page3Href, 'target' => $page3Target], $page3LabelTranslatedEscaped]
            )
            ->willReturnOnConsecutiveCalls($expected1, $expected2, $expected3, $expected4, $expected5);

        $helper = new Menu(
            $serviceLocator,
            $logger,
            $containerParser,
            $escapeHtmlAttr,
            $renderer,
            $escapeHtml,
            $htmlElement
        );

        $helper->setRole($role);
        $helper->setTranslator($translator);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        self::assertSame($expected, $helper->renderMenu($name, ['direction' => Menu::DROP_ORIENTATION_UP, 'sublink' => Menu::STYLE_SUBLINK_DETAILS]));
    }

    /**
     * @throws Exception
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     */
    public function testRenderMenu7(): void
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

        $liActiveClass = 'li-active';
        $ulClass       = 'ul-class ul';

        $parentLabel                  = 'parent-label';
        $parentTranslatedLabel        = 'parent-label-translated';
        $parentTranslatedLabelEscaped = 'parent-label-translated-escaped';
        $parentTextDomain             = 'parent-text-domain';
        $parentTitle                  = 'parent-title';
        $parentTranslatedTitle        = 'parent-title-translated';

        $parentParentLabel                  = 'parent-parent-label';
        $parentParentTranslatedLabel        = 'parent-parent-label-translated';
        $parentParentTranslatedLabelEscaped = 'parent-parent-label-translated-escaped';
        $parentParentTextDomain             = 'parent-parent-text-domain';
        $parentParentTitle                  = 'parent-parent-title';
        $parentParentTranslatedTitle        = 'parent-parent-title-translated';

        $pageLabel                  = 'page-label';
        $pageLabelTranslated        = 'page-label-translated';
        $pageLabelTranslatedEscaped = 'page-label-translated-escaped';
        $pageTitle                  = 'page-title';
        $pageTitleTranslated        = 'page-title-translated';
        $pageTextDomain             = 'page-text-domain';
        $pageId                     = 'page-id';
        $pageHref                   = 'http://page';
        $pageTarget                 = 'page-target';

        $page2Label                  = 'page2-label';
        $page2LabelTranslated        = 'page2-label-translated';
        $page2LabelTranslatedEscaped = 'page2-label-translated-escaped';
        $page2Title                  = 'page2-title';
        $page2TitleTranslated        = 'page2-title-translated';
        $page2TextDomain             = 'page2-text-domain';
        $page2Id                     = 'page2-id';
        $page2Href                   = 'http://page2';
        $page2Target                 = 'page2-target';

        $page3Label                  = 'page3-label';
        $page3LabelTranslated        = 'page3-label-translated';
        $page3LabelTranslatedEscaped = 'page3-label-translated-escaped';
        $page3Title                  = 'page3-title';
        $page3TitleTranslated        = 'page3-title-translated';
        $page3TextDomain             = 'page3-text-domain';
        $page3Id                     = 'page3-id';
        $page3Href                   = 'http://page3';
        $page3Target                 = 'page3-target';

        $parentPage = new Uri();
        $parentPage->setVisible(true);
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);
        $parentPage->setId('parent-id');
        $parentPage->setClass('parent-class');
        $parentPage->setUri('##');
        $parentPage->setTarget('self');
        $parentPage->setLabel($parentLabel);
        $parentPage->setTitle($parentTitle);
        $parentPage->setTextDomain($parentTextDomain);

        $parentParentPage = new Uri();
        $parentParentPage->setVisible(true);
        $parentParentPage->setResource($resource);
        $parentParentPage->setPrivilege($privilege);
        $parentParentPage->setId('parent-parent-id');
        $parentParentPage->setClass('parent-parent-class');
        $parentParentPage->setUri('###');
        $parentParentPage->setTarget('self-parent');
        $parentParentPage->setLabel($parentParentLabel);
        $parentParentPage->setTitle($parentParentTitle);
        $parentParentPage->setTextDomain($parentParentTextDomain);

        $page = $this->getMockBuilder(AbstractPage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::once())
            ->method('isVisible')
            ->with(false)
            ->willReturn(true);
        $page->expects(self::never())
            ->method('getResource');
        $page->expects(self::never())
            ->method('getPrivilege');
        $page->expects(self::never())
            ->method('getParent');
        $page->expects(self::exactly(3))
            ->method('isActive')
            ->with(true)
            ->willReturn(true);
        $page->expects(self::once())
            ->method('getLabel')
            ->willReturn($pageLabel);
        $page->expects(self::once())
            ->method('getTextDomain')
            ->willReturn($pageTextDomain);
        $page->expects(self::once())
            ->method('getTitle')
            ->willReturn($pageTitle);
        $page->expects(self::once())
            ->method('getId')
            ->willReturn($pageId);
        $page->expects(self::exactly(2))
            ->method('getClass')
            ->willReturn('xxxx');
        $page->expects(self::exactly(2))
            ->method('getHref')
            ->willReturn($pageHref);
        $page->expects(self::once())
            ->method('getTarget')
            ->willReturn($pageTarget);
        $page->expects(self::never())
            ->method('hasPage');
        $page->expects(self::exactly(2))
            ->method('hasPages')
            ->withConsecutive([false], [true])
            ->willReturnOnConsecutiveCalls(false, false);
        $page->expects(self::once())
            ->method('getCustomProperties')
            ->willReturn([]);
        $page->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive(['li-active-class'], ['li-class'])
            ->willReturnOnConsecutiveCalls(null, null);

        $page2 = $this->getMockBuilder(AbstractPage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page2->expects(self::never())
            ->method('isVisible');
        $page2->expects(self::never())
            ->method('getResource');
        $page2->expects(self::never())
            ->method('getPrivilege');
        $page2->expects(self::never())
            ->method('getParent');
        $page2->expects(self::once())
            ->method('isActive')
            ->with(true)
            ->willReturn(true);
        $page2->expects(self::once())
            ->method('getLabel')
            ->willReturn($page2Label);
        $page2->expects(self::once())
            ->method('getTextDomain')
            ->willReturn($page2TextDomain);
        $page2->expects(self::once())
            ->method('getTitle')
            ->willReturn($page2Title);
        $page2->expects(self::once())
            ->method('getId')
            ->willReturn($page2Id);
        $page2->expects(self::exactly(2))
            ->method('getClass')
            ->willReturn('xxxx2');
        $page2->expects(self::exactly(2))
            ->method('getHref')
            ->willReturn($page2Href);
        $page2->expects(self::once())
            ->method('getTarget')
            ->willReturn($page2Target);
        $page2->expects(self::never())
            ->method('hasPage');
        $page2->expects(self::exactly(2))
            ->method('hasPages')
            ->withConsecutive([false], [true])
            ->willReturnOnConsecutiveCalls(false, false);
        $page2->expects(self::once())
            ->method('getCustomProperties')
            ->willReturn([]);
        $page2->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive(['li-active-class'], ['li-class'])
            ->willReturnOnConsecutiveCalls(null, null);

        $page3 = $this->getMockBuilder(AbstractPage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page3->expects(self::never())
            ->method('isVisible');
        $page3->expects(self::never())
            ->method('getResource');
        $page3->expects(self::never())
            ->method('getPrivilege');
        $page3->expects(self::never())
            ->method('getParent');
        $page3->expects(self::once())
            ->method('isActive')
            ->with(true)
            ->willReturn(true);
        $page3->expects(self::once())
            ->method('getLabel')
            ->willReturn($page3Label);
        $page3->expects(self::once())
            ->method('getTextDomain')
            ->willReturn($page3TextDomain);
        $page3->expects(self::once())
            ->method('getTitle')
            ->willReturn($page3Title);
        $page3->expects(self::once())
            ->method('getId')
            ->willReturn($page3Id);
        $page3->expects(self::exactly(2))
            ->method('getClass')
            ->willReturn('xxxx3');
        $page3->expects(self::exactly(2))
            ->method('getHref')
            ->willReturn($page3Href);
        $page3->expects(self::once())
            ->method('getTarget')
            ->willReturn($page3Target);
        $page3->expects(self::never())
            ->method('hasPage');
        $page3->expects(self::exactly(2))
            ->method('hasPages')
            ->withConsecutive([false], [true])
            ->willReturnOnConsecutiveCalls(false, false);
        $page3->expects(self::once())
            ->method('getCustomProperties')
            ->willReturn([]);
        $page3->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive(['li-active-class'], ['li-class'])
            ->willReturnOnConsecutiveCalls(null, null);

        $parentPage->addPage($page);
        $parentParentPage->addPage($parentPage);
        $parentParentPage->addPage($page2);
        $parentParentPage->addPage($page3);

        $container = new Navigation();
        $container->addPage($parentParentPage);

        $role = 'testRole';

        $findActiveHelper = $this->getMockBuilder(FindActiveInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, 0, null)
            ->willReturn(
                [
                    'page' => $page,
                    'depth' => 1,
                ]
            );

        $acceptHelper = $this->getMockBuilder(AcceptHelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $acceptHelper->expects(self::exactly(9))
            ->method('accept')
            ->withConsecutive([$parentParentPage], [$parentPage], [$page], [$page], [$parentPage], [$page], [$page], [$page2], [$page3])
            ->willReturnOnConsecutiveCalls(true, true, true, true, true, true, true, true, true);

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
        $serviceLocator->expects(self::exactly(10))
            ->method('build')
            ->withConsecutive(
                [
                    FindActiveInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ]
            )
            ->willReturnOnConsecutiveCalls(
                $findActiveHelper,
                $acceptHelper,
                $acceptHelper,
                $acceptHelper,
                $acceptHelper,
                $acceptHelper,
                $acceptHelper,
                $acceptHelper,
                $acceptHelper,
                $acceptHelper
            );

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::exactly(2))
            ->method('parseContainer')
            ->withConsecutive([$name], [$container])
            ->willReturnOnConsecutiveCalls($container, $container);

        $escapeHtmlAttr = $this->getMockBuilder(EscapeHtmlAttr::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtmlAttr->expects(self::exactly(10))
            ->method('__invoke')
            ->withConsecutive(['nav ul-class ul'], ['nav-item dropup li-active'], ['dropdown-details-menu'], ['parent-parent-id'], ['dropup li-active'], ['dropdown-details-menu'], ['parent-id'], ['li-active'], ['li-active'], ['li-active'])
            ->willReturnOnConsecutiveCalls('nav-escaped ul-class-escaped ul-escaped', 'nav-item-escaped dropdown-escaped li-active-escaped', 'dropdown-menu-escaped', 'parent-parent-id-escaped', 'dropdown-escaped li-active-escaped', 'dropdown-menu-escaped', 'parent-id-escaped', 'li-active-escaped', 'li-active-escaped', 'li-active-escaped');

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::exactly(5))
            ->method('__invoke')
            ->withConsecutive([$parentParentTranslatedLabel], [$parentTranslatedLabel], [$pageLabelTranslated], [$page2LabelTranslated], [$page3LabelTranslated])
            ->willReturnOnConsecutiveCalls($parentParentTranslatedLabelEscaped, $parentTranslatedLabelEscaped, $pageLabelTranslatedEscaped, $page2LabelTranslatedEscaped, $page3LabelTranslatedEscaped);

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
        $translator->expects(self::exactly(10))
            ->method('translate')
            ->withConsecutive([$parentParentLabel, $parentParentTextDomain], [$parentParentTitle, $parentParentTextDomain], [$parentLabel, $parentTextDomain], [$parentTitle, $parentTextDomain], [$pageLabel, $pageTextDomain], [$pageTitle, $pageTextDomain], [$page2Label, $page2TextDomain], [$page2Title, $page2TextDomain], [$page3Label, $page3TextDomain], [$page3Title, $page3TextDomain])
            ->willReturnOnConsecutiveCalls($parentParentTranslatedLabel, $parentParentTranslatedTitle, $parentTranslatedLabel, $parentTranslatedTitle, $pageLabelTranslated, $pageTitleTranslated, $page2LabelTranslated, $page2TitleTranslated, $page3LabelTranslated, $page3TitleTranslated);

        $expected = '<ul class="nav-escaped ul-class-escaped ul-escaped">' . PHP_EOL . '    <li class="nav-item-escaped dropdown-escaped li-active-escaped">' . PHP_EOL . '        <details>' . PHP_EOL . '        <a parent-id-escaped="parent-id-escaped" parent-title-escaped="parent-title-escaped" parent-class-escaped="parent-class-escaped" parent-href-escaped="##-escaped" parent-target-escaped="self-escaped">parent-label-escaped</a>' . PHP_EOL . '        <ul class="dropdown-menu-escaped" aria-labelledby="parent-parent-id-escaped">' . PHP_EOL . '            <li class="dropdown-escaped li-active-escaped">' . PHP_EOL . '                <details>' . PHP_EOL . '                <a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>' . PHP_EOL . '                <ul class="dropdown-menu-escaped" aria-labelledby="parent-id-escaped">' . PHP_EOL . '                    <li class="li-active-escaped">' . PHP_EOL . '                        <a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>' . PHP_EOL . '                    </li>' . PHP_EOL . '                </ul>' . PHP_EOL . '                </details>' . PHP_EOL . '            </li>' . PHP_EOL . '            <li class="li-active-escaped">' . PHP_EOL . '                <a idEscaped="test2IdEscaped" titleEscaped="test2TitleTranslatedAndEscaped" classEscaped="test2ClassEscaped" hrefEscaped="#2Escaped">test2LabelTranslatedAndEscaped</a>' . PHP_EOL . '            </li>' . PHP_EOL . '            <li class="li-active-escaped">' . PHP_EOL . '                <a idEscaped="test3IdEscaped" titleEscaped="test3TitleTranslatedAndEscaped" classEscaped="test3ClassEscaped" hrefEscaped="#3Escaped">test3LabelTranslatedAndEscaped</a>' . PHP_EOL . '            </li>' . PHP_EOL . '        </ul>' . PHP_EOL . '        </details>' . PHP_EOL . '    </li>' . PHP_EOL . '</ul>';

        $expected1 = '<a parent-id-escaped="parent-id-escaped" parent-title-escaped="parent-title-escaped" parent-class-escaped="parent-class-escaped" parent-href-escaped="##-escaped" parent-target-escaped="self-escaped">parent-label-escaped</a>';
        $expected2 = '<a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>';
        $expected3 = '<a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>';
        $expected4 = '<a idEscaped="test2IdEscaped" titleEscaped="test2TitleTranslatedAndEscaped" classEscaped="test2ClassEscaped" hrefEscaped="#2Escaped">test2LabelTranslatedAndEscaped</a>';
        $expected5 = '<a idEscaped="test3IdEscaped" titleEscaped="test3TitleTranslatedAndEscaped" classEscaped="test3ClassEscaped" hrefEscaped="#3Escaped">test3LabelTranslatedAndEscaped</a>';

        $htmlElement = $this->getMockBuilder(HtmlElementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlElement->expects(self::exactly(5))
            ->method('toHtml')
            ->withConsecutive(
                ['summary', ['aria-current' => 'page', 'class' => 'nav-link btn parent-parent-class', 'id' => 'parent-parent-id', 'title' => $parentParentTranslatedTitle, 'aria-expanded' => 'false', 'role' => 'button'], $parentParentTranslatedLabelEscaped],
                ['summary', ['class' => 'dropdown-item btn parent-class', 'id' => 'parent-id', 'title' => $parentTranslatedTitle, 'aria-expanded' => 'false', 'role' => 'button'], $parentTranslatedLabelEscaped],
                ['a', ['class' => 'dropdown-item xxxx', 'id' => $pageId, 'title' => $pageTitleTranslated, 'href' => $pageHref, 'target' => $pageTarget], $pageLabelTranslatedEscaped],
                ['a', ['class' => 'dropdown-item xxxx2', 'id' => $page2Id, 'title' => $page2TitleTranslated, 'href' => $page2Href, 'target' => $page2Target], $page2LabelTranslatedEscaped],
                ['a', ['class' => 'dropdown-item xxxx3', 'id' => $page3Id, 'title' => $page3TitleTranslated, 'href' => $page3Href, 'target' => $page3Target], $page3LabelTranslatedEscaped]
            )
            ->willReturnOnConsecutiveCalls($expected1, $expected2, $expected3, $expected4, $expected5);

        $helper = new Menu(
            $serviceLocator,
            $logger,
            $containerParser,
            $escapeHtmlAttr,
            $renderer,
            $escapeHtml,
            $htmlElement
        );

        $helper->setRole($role);
        $helper->setTranslator($translator);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        self::assertSame($expected, $helper->renderMenu($name, ['direction' => Menu::DROP_ORIENTATION_UP, 'sublink' => Menu::STYLE_SUBLINK_DETAILS, 'ulClass' => $ulClass, 'liActiveClass' => $liActiveClass]));
    }
}
