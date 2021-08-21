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

namespace Mimmi20Test\LaminasView\BootstrapNavigation\Compare;

use Laminas\Config\Config;
use Laminas\Config\Exception\RuntimeException;
use Laminas\Config\Factory as ConfigFactory;
use Laminas\I18n\Translator\Translator;
use Laminas\Log\Logger;
use Laminas\Navigation\Navigation;
use Laminas\Navigation\Service\ConstructedNavigationFactory;
use Laminas\Navigation\Service\DefaultNavigationFactory;
use Laminas\Permissions\Acl\Acl;
use Laminas\Permissions\Acl\Resource\GenericResource;
use Laminas\Permissions\Acl\Role\GenericRole;
use Laminas\ServiceManager\Exception\ContainerModificationsNotAllowedException;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\Helper\Navigation\AbstractHelper;
use Laminas\View\HelperPluginManager as ViewHelperPluginManager;
use Mimmi20\LaminasView\BootstrapNavigation\ViewHelperInterface;
use Mimmi20\LaminasView\Helper\HtmlElement\Helper\HtmlElementFactory;
use Mimmi20\LaminasView\Helper\HtmlElement\Helper\HtmlElementInterface;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

use function file_get_contents;
use function sprintf;

/**
 * Base class for navigation view helper tests
 */
abstract class AbstractTest extends TestCase
{
    protected ServiceManager $serviceManager;

    /**
     * Path to files needed for test
     */
    protected string $files;

    /**
     * Class name for view helper to test
     */
    protected string $helperName;

    /**
     * View helper
     */
    protected AbstractHelper $helper;

    /**
     * The first container in the config file (files/navigation.xml)
     */
    protected Navigation $nav1;

    /**
     * The second container in the config file (files/navigation.xml)
     */
    protected Navigation $nav2;

    /**
     * The third container in the config file (files/navigation.xml)
     */
    protected Navigation $nav3;

    /**
     * Prepares the environment before running a test
     *
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ContainerExceptionInterface
     * @throws \Laminas\Config\Exception\InvalidArgumentException
     * @throws RuntimeException
     */
    protected function setUp(): void
    {
        $cwd = __DIR__;

        // read navigation config
        $this->files = $cwd . '/_files';
        $config      = ConfigFactory::fromFile($this->files . '/navigation.xml', true);

        static::assertInstanceOf(Config::class, $config);

        $sm = $this->serviceManager = new ServiceManager();
        $sm->setAllowOverride(true);

        $sm->setFactory('Navigation', DefaultNavigationFactory::class);
        $sm->setFactory('navigation', DefaultNavigationFactory::class);
        $sm->setFactory('default', DefaultNavigationFactory::class);
        $sm->setFactory('nav_test1', new ConstructedNavigationFactory('nav_test1'));
        $sm->setFactory('nav_test2', new ConstructedNavigationFactory('nav_test2'));
        $sm->setFactory('nav_test3', new ConstructedNavigationFactory('nav_test3'));
        $sm->setFactory(PageFactory::class, InvokableFactory::class);
        $sm->setAlias(PageFactoryInterface::class, PageFactory::class);
        $sm->setFactory(HelperPluginManager::class, PluginManagerFactory::class);
        $sm->setFactory(PartialRendererInterface::class, PartialRendererFactory::class);
        $sm->setFactory(HtmlElementInterface::class, HtmlElementFactory::class);
        $sm->setFactory(
            'config',
            static fn (): array => [
                'navigation' => [
                    'default' => $config->get('nav_test1'),
                ],
                'view_helpers' => [
                    'aliases' => [
                        'navigation' => Navigation::class,
                        'Navigation' => Navigation::class,
                    ],
                    'factories' => [
                        Navigation::class => NavigationFactory::class,
                        UrlHelper::class => UrlHelperFactory::class,
                        ServerUrlHelper::class => ServerUrlHelperFactory::class,
                    ],
                ],
                'templates' => [
                    'map' => [
                        'test::menu' => __DIR__ . '/_files/mvc/views/menu.phtml',
                        'test::menu-with-partials' => __DIR__ . '/_files/mvc/views/menu_with_partial_params.phtml',
                        'test::bc' => __DIR__ . '/_files/mvc/views/bc.phtml',
                        'test::bc-separator' => __DIR__ . '/_files/mvc/views/bc_separator.phtml',
                        'test::bc-with-partials' => __DIR__ . '/_files/mvc/views/bc_with_partial_params.phtml',
                    ],
                ],
            ]
        );
        $sm->setFactory(ViewHelperPluginManager::class, HelperPluginManagerFactory::class);
        $sm->setFactory(LaminasViewRenderer::class, LaminasViewRendererFactory::class);
        $sm->setFactory(BaseServerUrlHelper::class, InvokableFactory::class);

        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(static::never())
            ->method('emerg');
        $logger->expects(static::never())
            ->method('alert');
        $logger->expects(static::never())
            ->method('crit');
        $logger->expects(static::never())
            ->method('err');
        $logger->expects(static::never())
            ->method('warn');
        $logger->expects(static::never())
            ->method('notice');
        $logger->expects(static::never())
            ->method('info');
        $logger->expects(static::never())
            ->method('debug');

        $sm->setService(Logger::class, $logger);

        // setup containers from config
        $this->nav1 = $sm->get('nav_test1');
        $this->nav2 = $sm->get('nav_test2');
        $this->nav3 = $sm->get('nav_test3');

        $sm->setService('nav1', $this->nav1);
        $sm->setService('nav2', $this->nav2);

        $sm->setAllowOverride(false);
    }

    /**
     * Returns the contens of the expected $file
     *
     * @throws Exception
     * @throws InvalidArgumentException
     */
    protected function getExpected(string $file): string
    {
        $content = file_get_contents($this->files . '/expected/' . $file);

        static::assertIsString($content, sprintf('could not load file %s', $this->files . '/expected/' . $file));

        return $content;
    }

    /**
     * Sets up ACL
     *
     * @return array<string, LaminasAcl|string>
     *
     * @throws \Laminas\Permissions\Acl\Exception\InvalidArgumentException
     */
    protected function getAcl(): array
    {
        $acl = new Acl();

        $acl->addRole(new GenericRole('guest'));
        $acl->addRole(new GenericRole('member'), 'guest');
        $acl->addRole(new GenericRole('admin'), 'member');
        $acl->addRole(new GenericRole('special'), 'member');

        $acl->addResource(new GenericResource('guest_foo'));
        $acl->addResource(new GenericResource('member_foo'), 'guest_foo');
        $acl->addResource(new GenericResource('admin_foo'));
        $acl->addResource(new GenericResource('special_foo'), 'member_foo');

        $acl->allow('guest', 'guest_foo');
        $acl->allow('member', 'member_foo');
        $acl->allow('admin', 'admin_foo');
        $acl->allow('special', 'special_foo');
        $acl->allow('special', 'admin_foo', 'read');

        return ['acl' => new LaminasAcl($acl), 'role' => 'special'];
    }

    /**
     * Returns translator
     *
     * @throws ContainerModificationsNotAllowedException
     */
    protected function getTranslator(): Translator
    {
        $loader               = new TestAsset\ArrayTranslator();
        $loader->translations = [
            'Page 1' => 'Side 1',
            'Page 1.1' => 'Side 1.1',
            'Page 2' => 'Side 2',
            'Page 2.3' => 'Side 2.3',
            'Page 2.3.3.1' => 'Side 2.3.3.1',
            'Home' => 'Hjem',
            'Go home' => 'Gå hjem',
        ];

        $translator = new Translator();
        $translator->getPluginManager()->setService('default', $loader);
        $translator->addTranslationFile('default', null);

        return $translator;
    }

    /**
     * Returns translator with text domain
     *
     * @throws ContainerModificationsNotAllowedException
     */
    protected function getTranslatorWithTextDomain(): Translator
    {
        $loader1               = new TestAsset\ArrayTranslator();
        $loader1->translations = [
            'Page 1' => 'TextDomain1 1',
            'Page 1.1' => 'TextDomain1 1.1',
            'Page 2' => 'TextDomain1 2',
            'Page 2.3' => 'TextDomain1 2.3',
            'Page 2.3.3' => 'TextDomain1 2.3.3',
            'Page 2.3.3.1' => 'TextDomain1 2.3.3.1',
        ];

        $loader2               = new TestAsset\ArrayTranslator();
        $loader2->translations = [
            'Page 1' => 'TextDomain2 1',
            'Page 1.1' => 'TextDomain2 1.1',
            'Page 2' => 'TextDomain2 2',
            'Page 2.3' => 'TextDomain2 2.3',
            'Page 2.3.3' => 'TextDomain2 2.3.3',
            'Page 2.3.3.1' => 'TextDomain2 2.3.3.1',
        ];

        $translator = new Translator();
        $translator->getPluginManager()->setService('default1', $loader1);
        $translator->getPluginManager()->setService('default2', $loader2);
        $translator->addTranslationFile('default1', null, 'LaminasTest_1');
        $translator->addTranslationFile('default2', null, 'LaminasTest_2');

        return $translator;
    }
}
