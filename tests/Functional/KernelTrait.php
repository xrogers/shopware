<?php

namespace Shopware\Tests;

use Symfony\Component\DependencyInjection\ResettableContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

trait KernelTrait
{
    protected $kernel;
    protected $container;

    /**
     * @before
     */
    protected function setupKernel()
    {
        $this->kernel = $this->createKernel();
        $this->kernel->boot();

        $this->container = $this->kernel->getContainer();
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
     * @return KernelInterface A KernelInterface instance
     */
    protected function createKernel(array $options = array())
    {
        return new \TestKernel(
            isset($options['environment']) ? $options['environment'] : 'test',
            isset($options['debug']) ? $options['debug'] : true
        );
    }

    /**
     * @after
     */
    protected function tearDownSymfonyKernel()
    {
        if (null !== $this->kernel) {
            $container = $this->kernel->getContainer();
            $this->kernel->shutdown();
            if ($container instanceof ResettableContainerInterface) {
                $container->reset();
            }
        }
    }
}
