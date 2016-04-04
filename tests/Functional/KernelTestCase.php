<?php

namespace Shopware\Tests;

use Symfony\Component\DependencyInjection\ResettableContainerInterface;

abstract class KernelTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Shopware\Kernel
     */
    protected static $kernel;

    /**
     * Boots the Kernel for this test.
     *
     * @param array $options
     */
    protected static function bootKernel(array $options = array())
    {
        static::ensureKernelShutdown();

        static::$kernel = static::createKernel($options);
        static::$kernel->boot();

        static::$kernel->getContainer()->load('plugins');
        static::$kernel->getContainer()->get('plugins')->Core()->ErrorHandler()->registerErrorHandler(E_ALL | E_STRICT);
    }

    /**
     * Creates a Kernel.
     *
     * Available options:
     *
     *  * environment
     *  * debug
     *
     * @param array $options An array of options
     *
     * @return \Shopware\Kernel
     */
    protected static function createKernel(array $options = array())
    {
        return new TestKernel(
            isset($options['environment']) ? $options['environment'] : 'test',
            isset($options['debug']) ? $options['debug'] : true
        );
    }

    /**
     * Shuts the kernel down if it was used in the test.
     */
    protected static function ensureKernelShutdown()
    {
        if (null !== static::$kernel) {
            $container = static::$kernel->getContainer();
            static::$kernel->shutdown();
            if ($container instanceof ResettableContainerInterface) {
                $container->get('models')->getConnection()->close();
                $container->reset();
            }
        }
    }

    /**
     * Clean up Kernel usage in this test.
     */
    protected function tearDown()
    {
        static::ensureKernelShutdown();
    }
}
