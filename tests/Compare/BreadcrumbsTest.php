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

namespace Mimmi20Test\LaminasView\BootstrapNavigation\Compare;

use Laminas\Config\Exception\RuntimeException;
use Laminas\Log\Logger;
use Laminas\Navigation\Navigation;
use Laminas\Navigation\Page\AbstractPage;
use Laminas\Permissions\Acl\AclInterface;
use Laminas\View\Exception\ExceptionInterface;
use Laminas\View\Helper\EscapeHtml;
use Laminas\View\HelperPluginManager as ViewHelperPluginManager;
use Laminas\View\Renderer\PhpRenderer;
use Mimmi20\LaminasView\BootstrapNavigation\Breadcrumbs;
use Mimmi20\NavigationHelper\ContainerParser\ContainerParserInterface;
use Mimmi20\NavigationHelper\Htmlify\HtmlifyInterface;
use PHPUnit\Framework\Exception;
use Psr\Container\ContainerExceptionInterface;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

use function assert;
use function is_string;
use function mb_strlen;
use function mb_substr;
use function str_replace;
use function trim;

use const PHP_EOL;

/**
 * Tests Mezzio\Navigation\LaminasView\View\Helper\Navigation\Breadcrumbs.
 *
 * @group Laminas_View
 * @group Laminas_View_Helper
 * @group Compare
 */
final class BreadcrumbsTest extends AbstractTest
{
    /**
     * Class name for view helper to test.
     */
    protected string $helperName = Breadcrumbs::class;

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws \Laminas\Config\Exception\InvalidArgumentException
     * @throws RuntimeException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $plugin = $this->serviceManager->get(ViewHelperPluginManager::class);
        assert($plugin instanceof ViewHelperPluginManager);

        $logger          = $this->serviceManager->get(Logger::class);
        $htmlify         = $this->serviceManager->get(HtmlifyInterface::class);
        $containerParser = $this->serviceManager->get(ContainerParserInterface::class);
        $renderer        = $this->serviceManager->get(PhpRenderer::class);
        $escapeHtml      = $plugin->get(EscapeHtml::class);

        assert($logger instanceof Logger);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        assert($renderer instanceof PhpRenderer);
        assert($escapeHtml instanceof EscapeHtml);

        // create helper
        $this->helper = new Breadcrumbs(
            $this->serviceManager,
            $logger,
            $htmlify,
            $containerParser,
            $renderer,
            $escapeHtml
        );

        // set nav1 in helper as default
        $this->helper->setContainer($this->nav1);
        $this->helper->setSeparator('&gt;');
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testHelperEntryPointWithoutAnyParams(): void
    {
        $returned = $this->helper->__invoke();
        self::assertSame($this->helper, $returned);
        self::assertSame($this->nav1, $returned->getContainer());
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testHelperEntryPointWithContainerParam(): void
    {
        $returned = $this->helper->__invoke($this->nav2);

        self::assertSame($this->helper, $returned);
        self::assertSame($this->nav2, $returned->getContainer());
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testNullOutContainer(): void
    {
        $old = $this->helper->getContainer();
        $this->helper->setContainer();
        $new = $this->helper->getContainer();

        self::assertNotSame($old, $new);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testSetSeparator(): void
    {
        $this->helper->setSeparator('foo');

        $expected = $this->getExpected('bc/separator.html');
        $actual   = $this->helper->render();

        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testSetMaxDepth(): void
    {
        $this->helper->setMaxDepth(1);

        $expected = $this->getExpected('bc/maxdepth.html');
        $actual   = $this->helper->render();

        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testSetMinDepth(): void
    {
        $this->helper->setMinDepth(1);

        $expected = '';
        $actual   = $this->helper->render($this->nav2);

        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testLinkLastElement(): void
    {
        $this->helper->setLinkLast(true);

        $expected = $this->getExpected('bc/linklast.html');
        $actual   = $this->helper->render();

        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testSetIndent(): void
    {
        $this->helper->setIndent(8);

        $expected = '        <n';
        $actual   = mb_substr($this->helper->render(), 0, mb_strlen($expected));

        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testRenderSuppliedContainerWithoutInterfering(): void
    {
        $this->helper->setMinDepth(0);
        self::assertSame(0, $this->helper->getMinDepth());

        $rendered1 = $this->getExpected('bc/default.html');
        $rendered2 = $this->getExpected('bc/default2.html');

        $expected = [
            'registered' => $rendered1,
            'supplied' => $rendered2,
            'registered_again' => $rendered1,
        ];

        $actual = [
            'registered' => $this->helper->render(),
            'supplied' => $this->helper->render($this->nav2),
            'registered_again' => $this->helper->render(),
        ];

        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ExceptionInterface
     * @throws \Laminas\Permissions\Acl\Exception\InvalidArgumentException
     */
    public function testUseAclResourceFromPages(): void
    {
        $acl = $this->getAcl();
        assert($acl['acl'] instanceof AclInterface);
        $this->helper->setAcl($acl['acl']);
        assert(is_string($acl['role']));
        $this->helper->setRole($acl['role']);

        $expected = $this->getExpected('bc/acl.html');
        self::assertSame($expected, $this->helper->render());
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testRenderingPartial(): void
    {
        $this->helper->setPartial('bc.phtml');

        $expected = $this->getExpected('bc/partial.html');
        self::assertSame($expected, $this->helper->render());
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testRenderingPartialWithSeparator(): void
    {
        $this->helper->setPartial('bc_separator.phtml')->setSeparator(' / ');

        $expected = trim($this->getExpected('bc/partialwithseparator.html'));
        self::assertSame($expected, $this->helper->render());
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testRenderingPartialBySpecifyingAnArrayAsPartial(): void
    {
        $this->helper->setPartial(['bc.phtml', 'application']);

        $expected = $this->getExpected('bc/partial.html');
        self::assertSame($expected, $this->helper->render());
    }

    /**
     * @throws Exception
     */
    public function testRenderingPartialShouldFailOnInvalidPartialArray(): void
    {
        $this->helper->setPartial(['bc.phtml']);

        try {
            $this->helper->render();

            self::fail(
                '$partial was invalid, but no Laminas\View\Exception\ExceptionInterface was thrown'
            );
        } catch (ExceptionInterface $e) {
        }
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testRenderingPartialWithParams(): void
    {
        $this->helper->setPartial('bc_with_partial_params.phtml')->setSeparator(' / ');

        $expected = $this->getExpected('bc/partial_with_params.html');
        $actual   = $this->helper->renderPartialWithParams(['variable' => 'test value']);

        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ExceptionInterface
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     */
    public function testLastBreadcrumbShouldBeEscaped(): void
    {
        $container = new Navigation();

        $page = AbstractPage::factory(
            [
                'label' => 'Live & Learn',
                'uri' => '#',
                'active' => true,
            ]
        );

        $container->addPage($page);

        $expected = $this->getExpected('bc/escaped.html');
        $actual   = $this->helper->setMinDepth(0)->render($container);

        self::assertSame($expected, $actual);
    }

    /**
     * Returns the contens of the expected $file, normalizes newlines.
     */
    protected function getExpected(string $file): string
    {
        return str_replace(["\r\n", "\n", "\r", '##lb##'], ['##lb##', '##lb##', '##lb##', PHP_EOL], parent::getExpected($file));
    }
}
