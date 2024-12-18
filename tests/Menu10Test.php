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

final class Menu10Test extends TestCase
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
    public function testRenderMenu3(): void
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
        $page->expects(self::once())
            ->method('hasPage')
            ->with($page3)
            ->willReturn(true);
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
        $matcher = self::exactly(2);
        $page3->expects($matcher)
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
                ],
            );

        $acceptHelper = $this->getMockBuilder(AcceptHelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $matcher      = self::exactly(9);
        $acceptHelper->expects($matcher)
            ->method('accept')
            ->willReturnCallback(
                static function (AbstractPage $pageInput, bool $recursive = true) use ($matcher, $parentParentPage, $parentPage, $page, $page2, $page3): bool {
                    $invocation = $matcher->numberOfInvocations();

                    match ($invocation) {
                        1 => self::assertSame($parentParentPage, $pageInput, (string) $invocation),
                        2, 5 => self::assertSame($parentPage, $pageInput, (string) $invocation),
                        8 => self::assertSame($page2, $pageInput, (string) $invocation),
                        9 => self::assertSame($page3, $pageInput, (string) $invocation),
                        default => self::assertEquals($page, $pageInput, (string) $invocation),
                    };

                    match ($invocation) {
                        2, 3, 4, 6 => self::assertFalse($recursive, (string) $invocation),
                        default => self::assertTrue($recursive, (string) $invocation),
                    };

                    return match ($invocation) {
                        8 => false,
                        default => true,
                    };
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
        $matcher = self::exactly(10);
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
        $matcher        = self::exactly(8);
        $escapeHtmlAttr->expects($matcher)
            ->method('__invoke')
            ->willReturnCallback(
                static function (string $value, int $recurse = AbstractHelper::RECURSE_NONE) use ($matcher): string {
                    $invocation = $matcher->numberOfInvocations();

                    match ($invocation) {
                        1 => self::assertSame('nav navigation', $value, (string) $invocation),
                        2 => self::assertSame(
                            'nav-item dropstart active',
                            $value,
                            (string) $invocation,
                        ),
                        3, 6 => self::assertSame('dropdown-menu', $value, (string) $invocation),
                        4 => self::assertSame('parent-parent-id', $value, (string) $invocation),
                        5 => self::assertSame('dropstart active', $value, (string) $invocation),
                        7 => self::assertSame('parent-id', $value, (string) $invocation),
                        default => self::assertSame('active', $value, (string) $invocation),
                    };

                    self::assertSame(AbstractHelper::RECURSE_NONE, $recurse, (string) $invocation);

                    return match ($invocation) {
                        1 => 'nav-escaped navigation-escaped',
                        2 => 'nav-item-escaped dropstart-escaped active-escaped',
                        3, 6 => 'dropdown-menu-escaped',
                        4 => 'parent-parent-id-escaped',
                        5 => 'dropstart-escaped active-escaped',
                        7 => 'parent-id-escaped',
                        default => 'active-escaped',
                    };
                },
            );

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $matcher    = self::exactly(4);
        $escapeHtml->expects($matcher)
            ->method('__invoke')
            ->willReturnCallback(
                static function (string $value, int $recurse = AbstractHelper::RECURSE_NONE) use ($matcher, $parentParentTranslatedLabel, $parentTranslatedLabel, $page3LabelTranslated, $pageLabelTranslated, $parentTranslatedLabelEscaped, $pageLabelTranslatedEscaped, $parentParentTranslatedLabelEscaped, $page3LabelTranslatedEscaped): string {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($parentParentTranslatedLabel, $value),
                        2 => self::assertSame($parentTranslatedLabel, $value),
                        3 => self::assertSame($pageLabelTranslated, $value),
                        default => self::assertSame($page3LabelTranslated, $value),
                    };

                    self::assertSame(AbstractHelper::RECURSE_NONE, $recurse);

                    return match ($matcher->numberOfInvocations()) {
                        1 => $parentParentTranslatedLabelEscaped,
                        2 => $parentTranslatedLabelEscaped,
                        3 => $pageLabelTranslatedEscaped,
                        default => $page3LabelTranslatedEscaped,
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
        $matcher    = self::exactly(8);
        $translator->expects($matcher)
            ->method('translate')
            ->willReturnCallback(
                static function (string $message, string $textDomain = 'default', string | null $locale = null) use ($matcher, $parentParentTranslatedLabel, $parentParentTranslatedTitle, $parentParentLabel, $parentParentTitle, $parentParentTextDomain, $pageTextDomain, $parentLabel, $parentTitle, $pageLabel, $pageTitle, $page3Label, $page3Title, $pageLabelTranslated, $pageTitleTranslated, $parentTextDomain, $parentTranslatedLabel, $parentTranslatedTitle, $page3TextDomain, $page3LabelTranslated, $page3TitleTranslated): string {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($parentParentLabel, $message),
                        2 => self::assertSame($parentParentTitle, $message),
                        3 => self::assertSame($parentLabel, $message),
                        4 => self::assertSame($parentTitle, $message),
                        5 => self::assertSame($pageLabel, $message),
                        7 => self::assertSame($page3Label, $message),
                        8 => self::assertSame($page3Title, $message),
                        default => self::assertSame($pageTitle, $message),
                    };

                    match ($matcher->numberOfInvocations()) {
                        1, 2 => self::assertSame($parentParentTextDomain, $textDomain),
                        3, 4 => self::assertSame($parentTextDomain, $textDomain),
                        7, 8 => self::assertSame($page3TextDomain, $textDomain),
                        default => self::assertSame($pageTextDomain, $textDomain),
                    };

                    self::assertNull($locale);

                    return match ($matcher->numberOfInvocations()) {
                        1 => $parentParentTranslatedLabel,
                        2 => $parentParentTranslatedTitle,
                        3 => $parentTranslatedLabel,
                        4 => $parentTranslatedTitle,
                        5 => $pageLabelTranslated,
                        7 => $page3LabelTranslated,
                        8 => $page3TitleTranslated,
                        default => $pageTitleTranslated,
                    };
                },
            );

        $expected = '<ul class="nav-escaped navigation-escaped">' . PHP_EOL . '    <li class="nav-item-escaped dropstart-escaped active-escaped">' . PHP_EOL . '        <a parent-id-escaped="parent-id-escaped" parent-title-escaped="parent-title-escaped" parent-class-escaped="parent-class-escaped" parent-href-escaped="##-escaped" parent-target-escaped="self-escaped">parent-label-escaped</a>' . PHP_EOL . '        <ul class="dropdown-menu-escaped" aria-labelledby="parent-parent-id-escaped">' . PHP_EOL . '            <li class="dropstart-escaped active-escaped">' . PHP_EOL . '                <a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>' . PHP_EOL . '                <ul class="dropdown-menu-escaped" aria-labelledby="parent-id-escaped">' . PHP_EOL . '                    <li class="active-escaped">' . PHP_EOL . '                        <a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>' . PHP_EOL . '                    </li>' . PHP_EOL . '                </ul>' . PHP_EOL . '            </li>' . PHP_EOL . '            <li>' . PHP_EOL . '                <a idEscaped="test3IdEscaped" titleEscaped="test3TitleTranslatedAndEscaped" classEscaped="test3ClassEscaped" hrefEscaped="#3Escaped">test3LabelTranslatedAndEscaped</a>' . PHP_EOL . '            </li>' . PHP_EOL . '        </ul>' . PHP_EOL . '    </li>' . PHP_EOL . '</ul>';

        $expected1 = '<a parent-id-escaped="parent-id-escaped" parent-title-escaped="parent-title-escaped" parent-class-escaped="parent-class-escaped" parent-href-escaped="##-escaped" parent-target-escaped="self-escaped">parent-label-escaped</a>';
        $expected2 = '<a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>';
        $expected3 = '<a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>';
        $expected5 = '<a idEscaped="test3IdEscaped" titleEscaped="test3TitleTranslatedAndEscaped" classEscaped="test3ClassEscaped" hrefEscaped="#3Escaped">test3LabelTranslatedAndEscaped</a>';

        $htmlElement = $this->getMockBuilder(HtmlElementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $matcher     = self::exactly(4);
        $htmlElement->expects($matcher)
            ->method('toHtml')
            ->willReturnCallback(
                static function (string $element, array $attribs, string $content) use ($matcher, $parentTranslatedTitle, $pageId, $pageTitleTranslated, $pageHref, $pageTarget, $parentTranslatedLabelEscaped, $pageLabelTranslatedEscaped, $expected1, $expected2, $parentParentTranslatedTitle, $page3Id, $page3TitleTranslated, $page3Href, $page3Target, $parentParentTranslatedLabelEscaped, $page3LabelTranslatedEscaped, $expected3, $expected5): string {
                    match ($matcher->numberOfInvocations()) {
                        1, 2 => self::assertSame('span', $element),
                        default => self::assertSame('a', $element),
                    };

                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame(
                            ['data-bs-toggle' => 'dropdown', 'aria-expanded' => 'false', 'role' => 'button', 'aria-current' => 'page', 'class' => 'nav-link dropdown-toggle parent-parent-class', 'id' => 'parent-parent-id', 'title' => $parentParentTranslatedTitle],
                            $attribs,
                        ),
                        2 => self::assertSame(
                            ['data-bs-toggle' => 'dropdown', 'aria-expanded' => 'false', 'role' => 'button', 'class' => 'dropdown-item dropdown-toggle parent-class', 'id' => 'parent-id', 'title' => $parentTranslatedTitle],
                            $attribs,
                        ),
                        3 => self::assertSame(
                            ['class' => 'dropdown-item xxxx', 'id' => $pageId, 'title' => $pageTitleTranslated, 'href' => $pageHref, 'target' => $pageTarget],
                            $attribs,
                        ),
                        default => self::assertSame(
                            ['class' => 'dropdown-item xxxx3', 'id' => $page3Id, 'title' => $page3TitleTranslated, 'href' => $page3Href, 'target' => $page3Target],
                            $attribs,
                        ),
                    };

                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($parentParentTranslatedLabelEscaped, $content),
                        2 => self::assertSame($parentTranslatedLabelEscaped, $content),
                        3 => self::assertSame($pageLabelTranslatedEscaped, $content),
                        default => self::assertSame($page3LabelTranslatedEscaped, $content),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        1 => $expected1,
                        2 => $expected2,
                        3 => $expected3,
                        default => $expected5,
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

        self::assertSame(
            $expected,
            $helper->renderMenu(
                $name,
                ['onlyActiveBranch' => true, 'direction' => Menu::DROP_ORIENTATION_START, 'sublink' => Menu::STYLE_SUBLINK_SPAN],
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
    public function testRenderMenu4(): void
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
        $page->expects(self::once())
            ->method('hasPage')
            ->with($page3)
            ->willReturn(true);
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
        $matcher = self::exactly(2);
        $page3->expects($matcher)
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
                ],
            );

        $acceptHelper = $this->getMockBuilder(AcceptHelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $matcher      = self::exactly(9);
        $acceptHelper->expects($matcher)
            ->method('accept')
            ->willReturnCallback(
                static function (AbstractPage $pageInput, bool $recursive = true) use ($matcher, $parentParentPage, $parentPage, $page, $page2, $page3): bool {
                    $invocation = $matcher->numberOfInvocations();

                    match ($invocation) {
                        1 => self::assertSame($parentParentPage, $pageInput, (string) $invocation),
                        2, 5 => self::assertSame($parentPage, $pageInput, (string) $invocation),
                        8 => self::assertSame($page2, $pageInput, (string) $invocation),
                        9 => self::assertSame($page3, $pageInput, (string) $invocation),
                        default => self::assertEquals($page, $pageInput, (string) $invocation),
                    };

                    match ($invocation) {
                        2, 3, 4, 6 => self::assertFalse($recursive, (string) $invocation),
                        default => self::assertTrue($recursive, (string) $invocation),
                    };

                    return match ($invocation) {
                        8 => false,
                        default => true,
                    };
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
        $matcher = self::exactly(10);
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
        $matcher        = self::exactly(8);
        $escapeHtmlAttr->expects($matcher)
            ->method('__invoke')
            ->willReturnCallback(
                static function (string $value, int $recurse = AbstractHelper::RECURSE_NONE) use ($matcher): string {
                    $invocation = $matcher->numberOfInvocations();

                    match ($invocation) {
                        1 => self::assertSame('nav navigation', $value, (string) $invocation),
                        2 => self::assertSame('nav-item dropend active', $value, (string) $invocation),
                        3, 6 => self::assertSame('dropdown-menu', $value, (string) $invocation),
                        4 => self::assertSame('parent-parent-id', $value, (string) $invocation),
                        5 => self::assertSame('dropend active', $value, (string) $invocation),
                        7 => self::assertSame('parent-id', $value, (string) $invocation),
                        default => self::assertSame('active', $value, (string) $invocation),
                    };

                    self::assertSame(AbstractHelper::RECURSE_NONE, $recurse, (string) $invocation);

                    return match ($invocation) {
                        1 => 'nav-escaped navigation-escaped',
                        2 => 'nav-item-escaped dropend-escaped active-escaped',
                        3, 6 => 'dropdown-menu-escaped',
                        4 => 'parent-parent-id-escaped',
                        5 => 'dropend-escaped active-escaped',
                        7 => 'parent-id-escaped',
                        default => 'active-escaped',
                    };
                },
            );

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $matcher    = self::exactly(4);
        $escapeHtml->expects($matcher)
            ->method('__invoke')
            ->willReturnCallback(
                static function (string $value, int $recurse = AbstractHelper::RECURSE_NONE) use ($matcher, $parentParentTranslatedLabel, $parentTranslatedLabel, $page3LabelTranslated, $pageLabelTranslated, $parentTranslatedLabelEscaped, $pageLabelTranslatedEscaped, $parentParentTranslatedLabelEscaped, $page3LabelTranslatedEscaped): string {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($parentParentTranslatedLabel, $value),
                        2 => self::assertSame($parentTranslatedLabel, $value),
                        3 => self::assertSame($pageLabelTranslated, $value),
                        default => self::assertSame($page3LabelTranslated, $value),
                    };

                    self::assertSame(AbstractHelper::RECURSE_NONE, $recurse);

                    return match ($matcher->numberOfInvocations()) {
                        1 => $parentParentTranslatedLabelEscaped,
                        2 => $parentTranslatedLabelEscaped,
                        3 => $pageLabelTranslatedEscaped,
                        default => $page3LabelTranslatedEscaped,
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
        $matcher    = self::exactly(8);
        $translator->expects($matcher)
            ->method('translate')
            ->willReturnCallback(
                static function (string $message, string $textDomain = 'default', string | null $locale = null) use ($matcher, $parentParentTranslatedLabel, $parentParentTranslatedTitle, $parentParentLabel, $parentParentTitle, $parentParentTextDomain, $pageTextDomain, $parentLabel, $parentTitle, $pageLabel, $pageTitle, $page3Label, $page3Title, $pageLabelTranslated, $pageTitleTranslated, $parentTextDomain, $parentTranslatedLabel, $parentTranslatedTitle, $page3TextDomain, $page3LabelTranslated, $page3TitleTranslated): string {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($parentParentLabel, $message),
                        2 => self::assertSame($parentParentTitle, $message),
                        3 => self::assertSame($parentLabel, $message),
                        4 => self::assertSame($parentTitle, $message),
                        5 => self::assertSame($pageLabel, $message),
                        7 => self::assertSame($page3Label, $message),
                        8 => self::assertSame($page3Title, $message),
                        default => self::assertSame($pageTitle, $message),
                    };

                    match ($matcher->numberOfInvocations()) {
                        1, 2 => self::assertSame($parentParentTextDomain, $textDomain),
                        3, 4 => self::assertSame($parentTextDomain, $textDomain),
                        7, 8 => self::assertSame($page3TextDomain, $textDomain),
                        default => self::assertSame($pageTextDomain, $textDomain),
                    };

                    self::assertNull($locale);

                    return match ($matcher->numberOfInvocations()) {
                        1 => $parentParentTranslatedLabel,
                        2 => $parentParentTranslatedTitle,
                        3 => $parentTranslatedLabel,
                        4 => $parentTranslatedTitle,
                        5 => $pageLabelTranslated,
                        7 => $page3LabelTranslated,
                        8 => $page3TitleTranslated,
                        default => $pageTitleTranslated,
                    };
                },
            );

        $expected = '<ul class="nav-escaped navigation-escaped">' . PHP_EOL . '    <li class="nav-item-escaped dropend-escaped active-escaped">' . PHP_EOL . '        <a parent-id-escaped="parent-id-escaped" parent-title-escaped="parent-title-escaped" parent-class-escaped="parent-class-escaped" parent-href-escaped="##-escaped" parent-target-escaped="self-escaped">parent-label-escaped</a>' . PHP_EOL . '        <ul class="dropdown-menu-escaped" aria-labelledby="parent-parent-id-escaped">' . PHP_EOL . '            <li class="dropend-escaped active-escaped">' . PHP_EOL . '                <a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>' . PHP_EOL . '                <ul class="dropdown-menu-escaped" aria-labelledby="parent-id-escaped">' . PHP_EOL . '                    <li class="active-escaped">' . PHP_EOL . '                        <a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>' . PHP_EOL . '                    </li>' . PHP_EOL . '                </ul>' . PHP_EOL . '            </li>' . PHP_EOL . '            <li>' . PHP_EOL . '                <a idEscaped="test3IdEscaped" titleEscaped="test3TitleTranslatedAndEscaped" classEscaped="test3ClassEscaped" hrefEscaped="#3Escaped">test3LabelTranslatedAndEscaped</a>' . PHP_EOL . '            </li>' . PHP_EOL . '        </ul>' . PHP_EOL . '    </li>' . PHP_EOL . '</ul>';

        $expected1 = '<a parent-id-escaped="parent-id-escaped" parent-title-escaped="parent-title-escaped" parent-class-escaped="parent-class-escaped" parent-href-escaped="##-escaped" parent-target-escaped="self-escaped">parent-label-escaped</a>';
        $expected2 = '<a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>';
        $expected3 = '<a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>';
        $expected5 = '<a idEscaped="test3IdEscaped" titleEscaped="test3TitleTranslatedAndEscaped" classEscaped="test3ClassEscaped" hrefEscaped="#3Escaped">test3LabelTranslatedAndEscaped</a>';

        $htmlElement = $this->getMockBuilder(HtmlElementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $matcher     = self::exactly(4);
        $htmlElement->expects($matcher)
            ->method('toHtml')
            ->willReturnCallback(
                static function (string $element, array $attribs, string $content) use ($matcher, $parentTranslatedTitle, $pageId, $pageTitleTranslated, $pageHref, $pageTarget, $parentTranslatedLabelEscaped, $pageLabelTranslatedEscaped, $expected1, $expected2, $parentParentTranslatedTitle, $page3Id, $page3TitleTranslated, $page3Href, $page3Target, $parentParentTranslatedLabelEscaped, $page3LabelTranslatedEscaped, $expected3, $expected5): string {
                    match ($matcher->numberOfInvocations()) {
                        1, 2 => self::assertSame('button', $element),
                        default => self::assertSame('a', $element),
                    };

                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame(
                            ['data-bs-toggle' => 'dropdown', 'aria-expanded' => 'false', 'role' => 'button', 'aria-current' => 'page', 'class' => 'nav-link btn dropdown-toggle parent-parent-class', 'id' => 'parent-parent-id', 'title' => $parentParentTranslatedTitle, 'type' => 'button'],
                            $attribs,
                        ),
                        2 => self::assertSame(
                            ['data-bs-toggle' => 'dropdown', 'aria-expanded' => 'false', 'role' => 'button', 'class' => 'dropdown-item btn dropdown-toggle parent-class', 'id' => 'parent-id', 'title' => $parentTranslatedTitle, 'type' => 'button'],
                            $attribs,
                        ),
                        3 => self::assertSame(
                            ['class' => 'dropdown-item xxxx', 'id' => $pageId, 'title' => $pageTitleTranslated, 'href' => $pageHref, 'target' => $pageTarget],
                            $attribs,
                        ),
                        default => self::assertSame(
                            ['class' => 'dropdown-item xxxx3', 'id' => $page3Id, 'title' => $page3TitleTranslated, 'href' => $page3Href, 'target' => $page3Target],
                            $attribs,
                        ),
                    };

                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($parentParentTranslatedLabelEscaped, $content),
                        2 => self::assertSame($parentTranslatedLabelEscaped, $content),
                        3 => self::assertSame($pageLabelTranslatedEscaped, $content),
                        default => self::assertSame($page3LabelTranslatedEscaped, $content),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        1 => $expected1,
                        2 => $expected2,
                        3 => $expected3,
                        default => $expected5,
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

        self::assertSame(
            $expected,
            $helper->renderMenu(
                $name,
                ['onlyActiveBranch' => true, 'direction' => Menu::DROP_ORIENTATION_END, 'sublink' => Menu::STYLE_SUBLINK_BUTTON],
            ),
        );
    }
}
