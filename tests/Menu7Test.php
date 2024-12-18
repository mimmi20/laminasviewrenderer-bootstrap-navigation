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
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\Exception\InvalidArgumentException;
use Laminas\View\Helper\EscapeHtml;
use Laminas\View\Helper\EscapeHtmlAttr;
use Laminas\View\Helper\Escaper\AbstractHelper;
use Laminas\View\Renderer\PhpRenderer;
use Mimmi20\LaminasView\BootstrapNavigation\Menu;
use Mimmi20\LaminasView\Helper\HtmlElement\Helper\HtmlElementInterface;
use Mimmi20\NavigationHelper\Accept\AcceptHelperInterface;
use Mimmi20\NavigationHelper\ContainerParser\ContainerParserInterface;
use Mimmi20\NavigationHelper\FindActive\FindActiveInterface;
use Override;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;

use function assert;

use const PHP_EOL;

final class Menu7Test extends TestCase
{
    /** @throws InvalidArgumentException */
    #[Override]
    protected function tearDown(): void
    {
        Menu::setDefaultAcl(null);
        Menu::setDefaultRole(null);
    }

    /**
     * @throws Exception
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     * @throws ContainerExceptionInterface
     */
    public function testRenderMenuWithTabsOnlyActiveBranchWithoutParents(): void
    {
        $name = 'Mezzio\Navigation\Top';

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
        $matcher = self::exactly(2);
        $page->expects($matcher)
            ->method('getClass')
            ->willReturn('xxxx');
        $matcher = self::exactly(2);
        $page->expects($matcher)
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
        $matcher = self::exactly(2);
        $page->expects($matcher)
            ->method('get')
            ->willReturnCallback(
                static function (string $property) use ($matcher): mixed {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame('li-active-class', $property),
                        default => self::assertSame('li-class', $property),
                    };

                    return null;
                },
            );

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
                ],
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
        $matcher = self::exactly(2);
        $serviceLocator->expects($matcher)
            ->method('build')
            ->willReturnCallback(
                static function (string $name, array | null $options = null) use ($matcher, $auth, $role, $findActiveHelper, $acceptHelper): mixed {
                    $invocation = $matcher->numberOfInvocations();

                    match ($invocation) {
                        1 => self::assertSame(FindActiveInterface::class, $name, (string) $invocation),
                        default => self::assertSame(
                            AcceptHelperInterface::class,
                            $name,
                            (string) $invocation,
                        ),
                    };

                    self::assertSame(
                        [
                            'authorization' => $auth,
                            'renderInvisible' => false,
                            'roles' => [$role],
                        ],
                        $options,
                        (string) $invocation,
                    );

                    return match ($invocation) {
                        1 => $findActiveHelper,
                        default => $acceptHelper,
                    };
                },
            );

        $expected2 = '<a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>';

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

        $escapeHtmlAttr = $this->getMockBuilder(EscapeHtmlAttr::class)
            ->disableOriginalConstructor()
            ->getMock();
        $matcher        = self::exactly(4);
        $escapeHtmlAttr->expects($matcher)
            ->method('__invoke')
            ->willReturnCallback(
                static function (string $value, int $recurse = AbstractHelper::RECURSE_NONE) use ($matcher): string {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame('nav-item active', $value),
                        2 => self::assertSame('presentation', $value),
                        3 => self::assertSame('navbar-nav navigation nav-tabs', $value),
                        default => self::assertSame('tablist', $value),
                    };

                    self::assertSame(AbstractHelper::RECURSE_NONE, $recurse);

                    return match ($matcher->numberOfInvocations()) {
                        1 => 'nav-item-escaped active-escaped',
                        2 => 'presentation-escaped',
                        3 => 'navbar-nav-escaped navigation-escaped nav-tabs-escaped',
                        default => 'tablist-escaped',
                    };
                },
            );

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
        $matcher    = self::exactly(2);
        $translator->expects($matcher)
            ->method('translate')
            ->willReturnCallback(
                static function (string $message, string $textDomain = 'default', string | null $locale = null) use ($matcher, $pageTextDomain, $pageLabel, $pageTitle, $pageLabelTranslated, $pageTitleTranslated): string {
                    self::assertSame($pageTextDomain, $textDomain);

                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($pageLabel, $message),
                        default => self::assertSame($pageTitle, $message),
                    };

                    self::assertNull($locale);

                    return match ($matcher->numberOfInvocations()) {
                        1 => $pageLabelTranslated,
                        default => $pageTitleTranslated,
                    };
                },
            );

        $expected = '<ul class="navbar-nav-escaped navigation-escaped nav-tabs-escaped" role="tablist-escaped">' . PHP_EOL . '    <li class="nav-item-escaped active-escaped" role="presentation-escaped">' . PHP_EOL . '        <a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>' . PHP_EOL . '    </li>' . PHP_EOL . '</ul>';

        $htmlElement = $this->getMockBuilder(HtmlElementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlElement->expects(self::once())
            ->method('toHtml')
            ->with(
                'a',
                ['aria-current' => 'page', 'class' => 'nav-link xxxx', 'id' => $pageId, 'title' => $pageTitleTranslated, 'href' => $pageHref, 'target' => $pageTarget, 'role' => 'tab'],
                $pageLabelTranslatedEscaped,
            )
            ->willReturn($expected2);

        $helper = new Menu(
            $serviceLocator,
            $containerParser,
            $escapeHtmlAttr,
            $renderer,
            $escapeHtml,
            $htmlElement,
        );

        $helper->setRole($role);
        $helper->setTranslator($translator);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        self::assertSame(
            $expected,
            $helper->renderMenu(
                $name,
                ['tabs' => true, 'dark' => true, 'in-navbar' => true, 'onlyActiveBranch' => true, 'renderParents' => false],
            ),
        );
    }

    /**
     * @throws Exception
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     * @throws ContainerExceptionInterface
     */
    public function testRenderMenuWithTabsOnlyActiveBranchWithoutParentsWithIndent(): void
    {
        $indent = '    ';
        $name   = 'Mezzio\Navigation\Top';

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
        $matcher = self::exactly(2);
        $page->expects($matcher)
            ->method('getClass')
            ->willReturn('xxxx');
        $matcher = self::exactly(2);
        $page->expects($matcher)
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
        $matcher = self::exactly(2);
        $page->expects($matcher)
            ->method('get')
            ->willReturnCallback(
                static function (string $property) use ($matcher): mixed {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame('li-active-class', $property),
                        default => self::assertSame('li-class', $property),
                    };

                    return null;
                },
            );

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
                ],
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
        $matcher = self::exactly(2);
        $serviceLocator->expects($matcher)
            ->method('build')
            ->willReturnCallback(
                static function (string $name, array | null $options = null) use ($matcher, $auth, $role, $findActiveHelper, $acceptHelper): mixed {
                    $invocation = $matcher->numberOfInvocations();

                    match ($invocation) {
                        1 => self::assertSame(FindActiveInterface::class, $name, (string) $invocation),
                        default => self::assertSame(
                            AcceptHelperInterface::class,
                            $name,
                            (string) $invocation,
                        ),
                    };

                    self::assertSame(
                        [
                            'authorization' => $auth,
                            'renderInvisible' => false,
                            'roles' => [$role],
                        ],
                        $options,
                        (string) $invocation,
                    );

                    return match ($invocation) {
                        1 => $findActiveHelper,
                        default => $acceptHelper,
                    };
                },
            );

        $expected2 = '<a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>';

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

        $escapeHtmlAttr = $this->getMockBuilder(EscapeHtmlAttr::class)
            ->disableOriginalConstructor()
            ->getMock();
        $matcher        = self::exactly(4);
        $escapeHtmlAttr->expects($matcher)
            ->method('__invoke')
            ->willReturnCallback(
                static function (string $value, int $recurse = AbstractHelper::RECURSE_NONE) use ($matcher): string {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame('nav-item active', $value),
                        2 => self::assertSame('presentation', $value),
                        3 => self::assertSame('navbar-nav navigation nav-tabs', $value),
                        default => self::assertSame('tablist', $value),
                    };

                    self::assertSame(AbstractHelper::RECURSE_NONE, $recurse);

                    return match ($matcher->numberOfInvocations()) {
                        1 => 'nav-item-escaped active-escaped',
                        2 => 'presentation-escaped',
                        3 => 'navbar-nav-escaped navigation-escaped nav-tabs-escaped',
                        default => 'tablist-escaped',
                    };
                },
            );

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
        $matcher    = self::exactly(2);
        $translator->expects($matcher)
            ->method('translate')
            ->willReturnCallback(
                static function (string $message, string $textDomain = 'default', string | null $locale = null) use ($matcher, $pageTextDomain, $pageLabel, $pageTitle, $pageLabelTranslated, $pageTitleTranslated): string {
                    self::assertSame($pageTextDomain, $textDomain);

                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($pageLabel, $message),
                        default => self::assertSame($pageTitle, $message),
                    };

                    self::assertNull($locale);

                    return match ($matcher->numberOfInvocations()) {
                        1 => $pageLabelTranslated,
                        default => $pageTitleTranslated,
                    };
                },
            );

        $expected = $indent . '<ul class="navbar-nav-escaped navigation-escaped nav-tabs-escaped" role="tablist-escaped">' . PHP_EOL . $indent . '    <li class="nav-item-escaped active-escaped" role="presentation-escaped">' . PHP_EOL . $indent . '        <a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>' . PHP_EOL . $indent . '    </li>' . PHP_EOL . $indent . '</ul>';

        $htmlElement = $this->getMockBuilder(HtmlElementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlElement->expects(self::once())
            ->method('toHtml')
            ->with(
                'a',
                ['aria-current' => 'page', 'class' => 'nav-link xxxx', 'id' => $pageId, 'title' => $pageTitleTranslated, 'href' => $pageHref, 'target' => $pageTarget, 'role' => 'tab'],
                $pageLabelTranslatedEscaped,
            )
            ->willReturn($expected2);

        $helper = new Menu(
            $serviceLocator,
            $containerParser,
            $escapeHtmlAttr,
            $renderer,
            $escapeHtml,
            $htmlElement,
        );

        $helper->setRole($role);
        $helper->setTranslator($translator);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);
        $helper->setIndent($indent);

        self::assertSame(
            $expected,
            $helper->renderMenu(
                $name,
                ['tabs' => true, 'dark' => true, 'in-navbar' => true, 'onlyActiveBranch' => true, 'renderParents' => false],
            ),
        );
    }

    /**
     * @throws Exception
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     * @throws ContainerExceptionInterface
     */
    public function testRenderSubMenuWithTabsOnlyActiveBranchWithoutParents(): void
    {
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
        $matcher = self::exactly(2);
        $page->expects($matcher)
            ->method('getClass')
            ->willReturn('xxxx');
        $matcher = self::exactly(2);
        $page->expects($matcher)
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
        $matcher = self::exactly(2);
        $page->expects($matcher)
            ->method('get')
            ->willReturnCallback(
                static function (string $property) use ($matcher): mixed {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame('li-active-class', $property),
                        default => self::assertSame('li-class', $property),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        1 => 'li-class',
                        default => null,
                    };
                },
            );

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
                ],
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
        $matcher = self::exactly(2);
        $serviceLocator->expects($matcher)
            ->method('build')
            ->willReturnCallback(
                static function (string $name, array | null $options = null) use ($matcher, $auth, $role, $findActiveHelper, $acceptHelper): mixed {
                    $invocation = $matcher->numberOfInvocations();

                    match ($invocation) {
                        1 => self::assertSame(FindActiveInterface::class, $name, (string) $invocation),
                        default => self::assertSame(
                            AcceptHelperInterface::class,
                            $name,
                            (string) $invocation,
                        ),
                    };

                    self::assertSame(
                        [
                            'authorization' => $auth,
                            'renderInvisible' => false,
                            'roles' => [$role],
                        ],
                        $options,
                        (string) $invocation,
                    );

                    return match ($invocation) {
                        1 => $findActiveHelper,
                        default => $acceptHelper,
                    };
                },
            );

        $expected2 = '<a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>';

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $matcher         = self::exactly(2);
        $containerParser->expects($matcher)
            ->method('parseContainer')
            ->with($container)
            ->willReturn($container);

        $escapeHtmlAttr = $this->getMockBuilder(EscapeHtmlAttr::class)
            ->disableOriginalConstructor()
            ->getMock();
        $matcher        = self::exactly(2);
        $escapeHtmlAttr->expects($matcher)
            ->method('__invoke')
            ->willReturnCallback(
                static function (string $value, int $recurse = AbstractHelper::RECURSE_NONE) use ($matcher): string {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame('nav-item active li-class', $value),
                        default => self::assertSame('nav navigation', $value),
                    };

                    self::assertSame(AbstractHelper::RECURSE_NONE, $recurse);

                    return match ($matcher->numberOfInvocations()) {
                        1 => 'nav-item-escaped active-escaped li-class-escaped',
                        default => 'nav-escaped navigation-escaped',
                    };
                },
            );

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
        $matcher    = self::exactly(2);
        $translator->expects($matcher)
            ->method('translate')
            ->willReturnCallback(
                static function (string $message, string $textDomain = 'default', string | null $locale = null) use ($matcher, $pageTextDomain, $pageLabel, $pageTitle, $pageLabelTranslated, $pageTitleTranslated): string {
                    self::assertSame($pageTextDomain, $textDomain);

                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($pageLabel, $message),
                        default => self::assertSame($pageTitle, $message),
                    };

                    self::assertNull($locale);

                    return match ($matcher->numberOfInvocations()) {
                        1 => $pageLabelTranslated,
                        default => $pageTitleTranslated,
                    };
                },
            );

        $expected = '<ul class="nav-escaped navigation-escaped">' . PHP_EOL . '    <li class="nav-item-escaped active-escaped li-class-escaped">' . PHP_EOL . '        <a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>' . PHP_EOL . '    </li>' . PHP_EOL . '</ul>';

        $htmlElement = $this->getMockBuilder(HtmlElementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlElement->expects(self::once())
            ->method('toHtml')
            ->with(
                'a',
                ['aria-current' => 'page', 'class' => 'nav-link xxxx', 'id' => $pageId, 'title' => $pageTitleTranslated, 'href' => $pageHref, 'target' => $pageTarget],
                $pageLabelTranslatedEscaped,
            )
            ->willReturn($expected2);

        $helper = new Menu(
            $serviceLocator,
            $containerParser,
            $escapeHtmlAttr,
            $renderer,
            $escapeHtml,
            $htmlElement,
        );

        $helper->setRole($role);
        $helper->setTranslator($translator);

        assert($auth instanceof Acl);
        $helper->setAcl($auth);

        self::assertSame($expected, $helper->renderSubMenu($container));
    }
}
