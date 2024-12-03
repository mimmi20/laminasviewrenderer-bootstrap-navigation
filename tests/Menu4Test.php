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
use Laminas\View\Exception\ExceptionInterface;
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
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;

use function assert;

use const PHP_EOL;

final class Menu4Test extends TestCase
{
    /** @throws InvalidArgumentException */
    protected function tearDown(): void
    {
        Menu::setDefaultAcl(null);
        Menu::setDefaultRole(null);
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
        $matcher         = self::exactly(2);
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
            $containerParser,
            $escapeHtmlAttr,
            $renderer,
            $escapeHtml,
            $htmlElement,
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
     * @throws InvalidArgumentException
     * @throws ContainerExceptionInterface
     */
    public function testDoNotRenderMenuIfNoPageIsActive(): void
    {
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
                ],
            )
            ->willReturn($findActiveHelper);

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
            $containerParser,
            $escapeHtmlAttr,
            $renderer,
            $escapeHtml,
            $htmlElement,
        );

        $helper->setContainer($container);

        self::assertSame('', $helper->renderMenu());
    }

    /**
     * @throws Exception
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     * @throws ContainerExceptionInterface
     */
    public function testRenderMenuNoActivePage(): void
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
                            'role' => $role,
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
            $containerParser,
            $escapeHtmlAttr,
            $renderer,
            $escapeHtml,
            $htmlElement,
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
     * @throws ContainerExceptionInterface
     */
    public function testRenderMenu(): void
    {
        $name = 'Mezzio\Navigation\Top';

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
        $matcher = self::exactly(2);
        $page->expects($matcher)
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
        $matcher = self::exactly(2);
        $page->expects($matcher)
            ->method('hasPages')
            ->willReturnCallback(
                static function (bool $onlyVisible = false) use ($matcher): bool {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertFalse($onlyVisible),
                        default => self::assertTrue($onlyVisible),
                    };

                    return false;
                },
            );
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
            ->with($container, 0, null)
            ->willReturn(
                [
                    'page' => $page,
                    'depth' => 1,
                ],
            );

        $acceptHelper = $this->getMockBuilder(AcceptHelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $matcher      = self::exactly(2);
        $acceptHelper->expects($matcher)
            ->method('accept')
            ->willReturnCallback(
                static function (AbstractPage $pageInput, bool $recursive = true) use ($matcher, $parentPage, $page): bool {
                    $invocation = $matcher->numberOfInvocations();

                    match ($invocation) {
                        1 => self::assertSame($parentPage, $pageInput, (string) $invocation),
                        default => self::assertSame($page, $pageInput, (string) $invocation),
                    };

                    self::assertTrue($recursive, (string) $invocation);

                    return true;
                },
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
        $matcher = self::exactly(3);
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
                            'role' => $role,
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

        $expected1 = '<a parent-id-escaped="parent-id-escaped" parent-title-escaped="parent-title-escaped" parent-class-escaped="parent-class-escaped" parent-href-escaped="##-escaped" parent-target-escaped="self-escaped">parent-label-escaped</a>';
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
        $matcher        = self::exactly(5);
        $escapeHtmlAttr->expects($matcher)
            ->method('__invoke')
            ->willReturnCallback(
                static function (string $value, int $recurse = AbstractHelper::RECURSE_NONE) use ($matcher): string {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame('nav navigation', $value),
                        2 => self::assertSame('nav-item active', $value),
                        3 => self::assertSame('dropdown-menu', $value),
                        4 => self::assertSame('parent-id', $value),
                        default => self::assertSame('active', $value),
                    };

                    self::assertSame(AbstractHelper::RECURSE_NONE, $recurse);

                    return match ($matcher->numberOfInvocations()) {
                        1 => 'nav-escaped navigation-escaped',
                        2 => 'nav-item-escaped active-escaped',
                        3 => 'dropdown-menu-escaped',
                        4 => 'parent-id-escaped',
                        default => 'active-escaped',
                    };
                },
            );

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $matcher    = self::exactly(2);
        $escapeHtml->expects($matcher)
            ->method('__invoke')
            ->willReturnCallback(
                static function (string $value, int $recurse = AbstractHelper::RECURSE_NONE) use ($matcher, $parentTranslatedLabel, $pageLabelTranslated, $parentTranslatedLabelEscaped, $pageLabelTranslatedEscaped): string {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($parentTranslatedLabel, $value),
                        default => self::assertSame($pageLabelTranslated, $value),
                    };

                    self::assertSame(AbstractHelper::RECURSE_NONE, $recurse);

                    return match ($matcher->numberOfInvocations()) {
                        1 => $parentTranslatedLabelEscaped,
                        default => $pageLabelTranslatedEscaped,
                    };
                },
            );

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
        $matcher    = self::exactly(4);
        $translator->expects($matcher)
            ->method('translate')
            ->willReturnCallback(
                static function (string $message, string $textDomain = 'default', string | null $locale = null) use ($matcher, $pageTextDomain, $parentLabel, $parentTitle, $pageLabel, $pageTitle, $pageLabelTranslated, $pageTitleTranslated, $parentTextDomain, $parentTranslatedLabel, $parentTranslatedTitle): string {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($parentLabel, $message),
                        2 => self::assertSame($parentTitle, $message),
                        3 => self::assertSame($pageLabel, $message),
                        default => self::assertSame($pageTitle, $message),
                    };

                    match ($matcher->numberOfInvocations()) {
                        1, 2 => self::assertSame($parentTextDomain, $textDomain),
                        default => self::assertSame($pageTextDomain, $textDomain),
                    };

                    self::assertNull($locale);

                    return match ($matcher->numberOfInvocations()) {
                        1 => $parentTranslatedLabel,
                        2 => $parentTranslatedTitle,
                        3 => $pageLabelTranslated,
                        default => $pageTitleTranslated,
                    };
                },
            );

        $expected = '<ul class="nav-escaped navigation-escaped">' . PHP_EOL . '    <li class="nav-item-escaped active-escaped">' . PHP_EOL . '        <a parent-id-escaped="parent-id-escaped" parent-title-escaped="parent-title-escaped" parent-class-escaped="parent-class-escaped" parent-href-escaped="##-escaped" parent-target-escaped="self-escaped">parent-label-escaped</a>' . PHP_EOL . '        <ul class="dropdown-menu-escaped" aria-labelledby="parent-id-escaped">' . PHP_EOL . '            <li class="active-escaped">' . PHP_EOL . '                <a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>' . PHP_EOL . '            </li>' . PHP_EOL . '        </ul>' . PHP_EOL . '    </li>' . PHP_EOL . '</ul>';

        $htmlElement = $this->getMockBuilder(HtmlElementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $matcher     = self::exactly(2);
        $htmlElement->expects($matcher)
            ->method('toHtml')
            ->willReturnCallback(
                static function (string $element, array $attribs, string $content) use ($matcher, $parentTranslatedTitle, $pageId, $pageTitleTranslated, $pageHref, $pageTarget, $parentTranslatedLabelEscaped, $pageLabelTranslatedEscaped, $expected1, $expected2): string {
                    self::assertSame('a', $element);

                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame(
                            ['aria-current' => 'page', 'class' => 'nav-link parent-class', 'id' => 'parent-id', 'title' => $parentTranslatedTitle, 'href' => '##', 'target' => 'self'],
                            $attribs,
                        ),
                        default => self::assertSame(
                            ['class' => 'dropdown-item xxxx', 'id' => $pageId, 'title' => $pageTitleTranslated, 'href' => $pageHref, 'target' => $pageTarget],
                            $attribs,
                        ),
                    };

                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($parentTranslatedLabelEscaped, $content),
                        default => self::assertSame($pageLabelTranslatedEscaped, $content),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        1 => $expected1,
                        default => $expected2,
                    };
                },
            );

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

        self::assertSame($expected, $helper->renderMenu($name));
    }

    /**
     * @throws Exception
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     * @throws ContainerExceptionInterface
     */
    public function testRenderMenuWithIndent(): void
    {
        $indent = '    ';

        $name = 'Mezzio\Navigation\Top';

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
        $matcher = self::exactly(2);
        $page->expects($matcher)
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
        $matcher = self::exactly(2);
        $page->expects($matcher)
            ->method('hasPages')
            ->willReturnCallback(
                static function (bool $onlyVisible = false) use ($matcher): bool {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertFalse($onlyVisible),
                        default => self::assertTrue($onlyVisible),
                    };

                    return false;
                },
            );
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
            ->with($container, 0, null)
            ->willReturn(
                [
                    'page' => $page,
                    'depth' => 1,
                ],
            );

        $acceptHelper = $this->getMockBuilder(AcceptHelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $matcher      = self::exactly(2);
        $acceptHelper->expects($matcher)
            ->method('accept')
            ->willReturnCallback(
                static function (AbstractPage $pageInput, bool $recursive = true) use ($matcher, $parentPage, $page): bool {
                    $invocation = $matcher->numberOfInvocations();

                    match ($invocation) {
                        1 => self::assertSame($parentPage, $pageInput, (string) $invocation),
                        default => self::assertSame($page, $pageInput, (string) $invocation),
                    };

                    self::assertTrue($recursive, (string) $invocation);

                    return true;
                },
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
        $matcher = self::exactly(3);
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
                            'role' => $role,
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

        $expected1 = '<a parent-id-escaped="parent-id-escaped" parent-title-escaped="parent-title-escaped" parent-class-escaped="parent-class-escaped" parent-href-escaped="##-escaped" parent-target-escaped="self-escaped">parent-label-escaped</a>';
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
        $matcher        = self::exactly(5);
        $escapeHtmlAttr->expects($matcher)
            ->method('__invoke')
            ->willReturnCallback(
                static function (string $value, int $recurse = AbstractHelper::RECURSE_NONE) use ($matcher): string {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame('nav navigation', $value),
                        2 => self::assertSame('nav-item active', $value),
                        3 => self::assertSame('dropdown-menu', $value),
                        4 => self::assertSame('parent-id', $value),
                        default => self::assertSame('active', $value),
                    };

                    self::assertSame(AbstractHelper::RECURSE_NONE, $recurse);

                    return match ($matcher->numberOfInvocations()) {
                        1 => 'nav-escaped navigation-escaped',
                        2 => 'nav-item-escaped active-escaped',
                        3 => 'dropdown-menu-escaped',
                        4 => 'parent-id-escaped',
                        default => 'active-escaped',
                    };
                },
            );

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $matcher    = self::exactly(2);
        $escapeHtml->expects($matcher)
            ->method('__invoke')
            ->willReturnCallback(
                static function (string $value, int $recurse = AbstractHelper::RECURSE_NONE) use ($matcher, $parentTranslatedLabel, $pageLabelTranslated, $parentTranslatedLabelEscaped, $pageLabelTranslatedEscaped): string {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($parentTranslatedLabel, $value),
                        default => self::assertSame($pageLabelTranslated, $value),
                    };

                    self::assertSame(AbstractHelper::RECURSE_NONE, $recurse);

                    return match ($matcher->numberOfInvocations()) {
                        1 => $parentTranslatedLabelEscaped,
                        default => $pageLabelTranslatedEscaped,
                    };
                },
            );

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
        $matcher    = self::exactly(4);
        $translator->expects($matcher)
            ->method('translate')
            ->willReturnCallback(
                static function (string $message, string $textDomain = 'default', string | null $locale = null) use ($matcher, $pageTextDomain, $parentLabel, $parentTitle, $pageLabel, $pageTitle, $pageLabelTranslated, $pageTitleTranslated, $parentTextDomain, $parentTranslatedLabel, $parentTranslatedTitle): string {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($parentLabel, $message),
                        2 => self::assertSame($parentTitle, $message),
                        3 => self::assertSame($pageLabel, $message),
                        default => self::assertSame($pageTitle, $message),
                    };

                    match ($matcher->numberOfInvocations()) {
                        1, 2 => self::assertSame($parentTextDomain, $textDomain),
                        default => self::assertSame($pageTextDomain, $textDomain),
                    };

                    self::assertNull($locale);

                    return match ($matcher->numberOfInvocations()) {
                        1 => $parentTranslatedLabel,
                        2 => $parentTranslatedTitle,
                        3 => $pageLabelTranslated,
                        default => $pageTitleTranslated,
                    };
                },
            );

        $expected = $indent . '<ul class="nav-escaped navigation-escaped">' . PHP_EOL . $indent . '    <li class="nav-item-escaped active-escaped">' . PHP_EOL . $indent . '        <a parent-id-escaped="parent-id-escaped" parent-title-escaped="parent-title-escaped" parent-class-escaped="parent-class-escaped" parent-href-escaped="##-escaped" parent-target-escaped="self-escaped">parent-label-escaped</a>' . PHP_EOL . $indent . '        <ul class="dropdown-menu-escaped" aria-labelledby="parent-id-escaped">' . PHP_EOL . $indent . '            <li class="active-escaped">' . PHP_EOL . $indent . '                <a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>' . PHP_EOL . $indent . '            </li>' . PHP_EOL . $indent . '        </ul>' . PHP_EOL . $indent . '    </li>' . PHP_EOL . $indent . '</ul>';

        $htmlElement = $this->getMockBuilder(HtmlElementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $matcher     = self::exactly(2);
        $htmlElement->expects($matcher)
            ->method('toHtml')
            ->willReturnCallback(
                static function (string $element, array $attribs, string $content) use ($matcher, $parentTranslatedTitle, $pageId, $pageTitleTranslated, $pageHref, $pageTarget, $parentTranslatedLabelEscaped, $pageLabelTranslatedEscaped, $expected1, $expected2): string {
                    self::assertSame('a', $element);

                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame(
                            ['aria-current' => 'page', 'class' => 'nav-link parent-class', 'id' => 'parent-id', 'title' => $parentTranslatedTitle, 'href' => '##', 'target' => 'self'],
                            $attribs,
                        ),
                        default => self::assertSame(
                            ['class' => 'dropdown-item xxxx', 'id' => $pageId, 'title' => $pageTitleTranslated, 'href' => $pageHref, 'target' => $pageTarget],
                            $attribs,
                        ),
                    };

                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($parentTranslatedLabelEscaped, $content),
                        default => self::assertSame($pageLabelTranslatedEscaped, $content),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        1 => $expected1,
                        default => $expected2,
                    };
                },
            );

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

        self::assertSame($expected, $helper->renderMenu($name));
    }
}
