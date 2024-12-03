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

namespace Mimmi20Test\LaminasView\BootstrapNavigation\Compare;

use Laminas\Config\Exception\InvalidArgumentException;
use Laminas\Config\Exception\RuntimeException;
use Laminas\Config\Factory as ConfigFactory;
use Laminas\I18n\Translator\Translator;
use Laminas\ModuleManager\ModuleManager;
use Laminas\Mvc\Application;
use Laminas\Mvc\Service\ServiceManagerConfig;
use Laminas\Navigation\Navigation;
use Laminas\Navigation\Page\AbstractPage;
use Laminas\Navigation\Service\ConstructedNavigationFactory;
use Laminas\Navigation\Service\DefaultNavigationFactory;
use Laminas\Permissions\Acl\Acl;
use Laminas\Permissions\Acl\Resource\GenericResource;
use Laminas\Permissions\Acl\Role\GenericRole;
use Laminas\Router\ConfigProvider;
use Laminas\Router\RouteMatch as V3RouteMatch;
use Laminas\ServiceManager\Config;
use Laminas\ServiceManager\Exception\ContainerModificationsNotAllowedException;
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\Helper\Navigation\AbstractHelper;
use Laminas\View\HelperPluginManager;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Resolver\TemplatePathStack;
use Mimmi20\LaminasView\Helper\HtmlElement\Helper\HtmlElementFactory;
use Mimmi20\LaminasView\Helper\HtmlElement\Helper\HtmlElementInterface;
use Mimmi20\NavigationHelper\Accept\AcceptHelperFactory;
use Mimmi20\NavigationHelper\Accept\AcceptHelperInterface;
use Mimmi20\NavigationHelper\ContainerParser\ContainerParserFactory;
use Mimmi20\NavigationHelper\ContainerParser\ContainerParserInterface;
use Mimmi20\NavigationHelper\FindActive\FindActiveFactory;
use Mimmi20\NavigationHelper\FindActive\FindActiveInterface;
use Mimmi20\NavigationHelper\Htmlify\HtmlifyFactory;
use Mimmi20\NavigationHelper\Htmlify\HtmlifyInterface;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;

use function assert;
use function class_exists;
use function file_get_contents;
use function sprintf;

/**
 * Base class for navigation view helper tests
 *
 * @template T of AbstractHelper
 */
abstract class AbstractTestCase extends TestCase
{
    protected ServiceManager $serviceManager;

    /**
     * Path to files needed for test
     */
    protected string $files;

    /**
     * The first container in the config file (files/navigation.xml)
     *
     * @var Navigation<AbstractPage>
     */
    protected Navigation $nav1;

    /**
     * The second container in the config file (files/navigation.xml)
     *
     * @var Navigation<AbstractPage>
     */
    protected Navigation $nav2;

    /**
     * The third container in the config file (files/navigation.xml)
     *
     * @var Navigation<AbstractPage>
     */
    protected Navigation $nav3;

    /**
     * Prepares the environment before running a test
     *
     * @throws Exception
     * @throws ContainerExceptionInterface
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Laminas\Navigation\Exception\InvalidArgumentException
     */
    protected function setUp(): void
    {
        $cwd = __DIR__;

        // read navigation config
        $this->files = $cwd . '/_files';
        $config      = ConfigFactory::fromFile($this->files . '/navigation.xml', true);

        assert($config instanceof \Laminas\Config\Config);

        $sm = $this->serviceManager = new ServiceManager();
        $sm->setAllowOverride(true);

        // setup containers from config
        $nav1 = $config->get('nav_test1');
        $nav2 = $config->get('nav_test2');
        $nav3 = $config->get('nav_test3');

        assert($nav1 instanceof \Laminas\Config\Config);
        assert($nav2 instanceof \Laminas\Config\Config);
        assert($nav3 instanceof \Laminas\Config\Config);

        $this->nav1 = new Navigation($nav1);
        $this->nav2 = new Navigation($nav2);
        $this->nav3 = new Navigation($nav3);

        // setup view
        $view     = new PhpRenderer();
        $resolver = $view->resolver();

        assert($resolver instanceof TemplatePathStack);

        $resolver->addPath($cwd . '/_files/mvc/views');

        // setup service manager
        $smConfig = [
            'modules' => [],
            'module_listener_options' => [
                'config_cache_enabled' => false,
                'cache_dir' => 'data/cache',
                'module_paths' => [],
                'extra_config' => [
                    'service_manager' => [
                        'factories' => [
                            'config' => static fn () => [
                                'navigation' => ['default' => $nav1],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        (new ServiceManagerConfig())->configureServiceManager($sm);

        if (class_exists(ConfigProvider::class)) {
            $routerConfig = new Config((new ConfigProvider())->getDependencyConfig());
            $routerConfig->configureServiceManager($sm);
        }

        $sm->setFactory('Navigation', DefaultNavigationFactory::class);
        $sm->setFactory('navigation', DefaultNavigationFactory::class);
        $sm->setFactory('default', DefaultNavigationFactory::class);
        $sm->setFactory('nav_test1', new ConstructedNavigationFactory('nav_test1'));
        $sm->setFactory('nav_test2', new ConstructedNavigationFactory('nav_test2'));
        $sm->setFactory('nav_test3', new ConstructedNavigationFactory('nav_test3'));
        $sm->setFactory(HtmlElementInterface::class, HtmlElementFactory::class);
        $sm->setFactory(HtmlifyInterface::class, HtmlifyFactory::class);
        $sm->setFactory(ContainerParserInterface::class, ContainerParserFactory::class);
        $sm->setFactory(FindActiveInterface::class, FindActiveFactory::class);
        $sm->setFactory(AcceptHelperInterface::class, AcceptHelperFactory::class);
        $sm->setService(
            'config',
            [
                'navigation' => ['default' => $nav1],
                'view_helpers' => [
                    'aliases' => [
                        'navigation' => Navigation::class,
                        'Navigation' => Navigation::class,
                    ],
                ],
            ],
        );

        $sm->setFactory(
            HelperPluginManager::class,
            static fn (): HelperPluginManager => new HelperPluginManager($sm),
        );

        $sm->setService(PhpRenderer::class, $view);
        $sm->setService('ApplicationConfig', $smConfig);

        $moduleManager = $sm->get('ModuleManager');
        assert($moduleManager instanceof ModuleManager);
        $moduleManager->loadModules();

        $application = $sm->get('Application');
        assert($application instanceof Application);
        $application->bootstrap();
        $sm->setFactory('Navigation', DefaultNavigationFactory::class);

        $sm->setService('nav1', $this->nav1);
        $sm->setService('nav2', $this->nav2);

        $sm->setAllowOverride(false);

        $application->getMvcEvent()->setRouteMatch(new V3RouteMatch([
            'controller' => 'post',
            'action' => 'view',
            'id' => '1337',
        ]));
    }

    /**
     * Returns the contens of the expected $file
     *
     * @throws Exception
     */
    protected function getExpected(string $file): string
    {
        $content = file_get_contents($this->files . '/expected/' . $file);

        static::assertIsString(
            $content,
            sprintf('could not load file %s', $this->files . '/expected/' . $file),
        );

        return $content;
    }

    /**
     * Sets up ACL
     *
     * @return array<string, Acl|string>
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

        return ['acl' => $acl, 'role' => 'special'];
    }

    /**
     * Returns translator
     *
     * @throws ContainerModificationsNotAllowedException
     */
    protected function getTranslator(): Translator
    {
        $loader = new TestAsset\ArrayTranslator(
            [
                'Page 1' => 'Side 1',
                'Page 1.1' => 'Side 1.1',
                'Page 2' => 'Side 2',
                'Page 2.3' => 'Side 2.3',
                'Page 2.3.3.1' => 'Side 2.3.3.1',
                'Home' => 'Hjem',
                'Go home' => 'Gå hjem',
            ],
        );

        $translator = new Translator();
        $translator->getPluginManager()->setService('default', $loader);
        $translator->addTranslationFile('default', '');

        return $translator;
    }

    /**
     * Returns translator with text domain
     *
     * @throws ContainerModificationsNotAllowedException
     */
    protected function getTranslatorWithTextDomain(): Translator
    {
        $loader1 = new TestAsset\ArrayTranslator(
            [
                'Page 1' => 'TextDomain1 1',
                'Page 1.1' => 'TextDomain1 1.1',
                'Page 2' => 'TextDomain1 2',
                'Page 2.3' => 'TextDomain1 2.3',
                'Page 2.3.3' => 'TextDomain1 2.3.3',
                'Page 2.3.3.1' => 'TextDomain1 2.3.3.1',
            ],
        );

        $loader2 = new TestAsset\ArrayTranslator(
            [
                'Page 1' => 'TextDomain2 1',
                'Page 1.1' => 'TextDomain2 1.1',
                'Page 2' => 'TextDomain2 2',
                'Page 2.3' => 'TextDomain2 2.3',
                'Page 2.3.3' => 'TextDomain2 2.3.3',
                'Page 2.3.3.1' => 'TextDomain2 2.3.3.1',
            ],
        );

        $translator = new Translator();
        $translator->getPluginManager()->setService('default1', $loader1);
        $translator->getPluginManager()->setService('default2', $loader2);
        $translator->addTranslationFile('default1', '', 'LaminasTest_1');
        $translator->addTranslationFile('default2', '', 'LaminasTest_2');

        return $translator;
    }
}
