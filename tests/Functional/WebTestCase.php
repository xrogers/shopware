<?php

namespace Shopware\Tests;

use Symfony\Component\HttpKernel\Client;

abstract class WebTestCase extends KernelTestCase
{
    /**
     * Creates a Client.
     *
     * @param array $options An array of options to pass to the createKernel class
     * @param array $server  An array of server parameters
     *
     * @return Client A Client instance
     */
    protected static function createClient(array $options = array(), array $server = array())
    {
        static::bootKernel($options);

        $client = new Client(static::$kernel, $server);

        return $client;
    }
}
