<?php

namespace Shopware\Tests;

class FooTest extends WebTestCase
{
    public function testBar()
    {
        $client = self::createClient();

        $crawler = $client->request('GET', '/account');

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Shopware Demo")')->count()
        );
    }

    public function testUserCanLogin()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/account');

        $form = $crawler->selectButton('Anmelden')->form(); // case sensitive for the input e.g. value="LOGIN"

        $crawler = $client->submit(
            $form,
            array(
                'email' => 'test@example.com', // case sensitive for the input e.g. name="_username"
                'password' => 'shopware' // case sensitive for the input e.g. name="_password"
            )
        );

        $heading = $crawler->filter('body')->eq(0)->text();
        echo $heading;

        var_dump($client->getResponse()->getStatusCode());

        var_dump(self::$kernel->getContainer()->get('sessionId'));

//        $this->assertTrue($crawler->filter('html:contains("Willkommen, Max Mustermann")')->count() > 0, 'Failed to log in with a good username and password.');
        $this->assertTrue($crawler->filter('html:contains("Ihre Zugangsdaten konnten keinem Benutzer zugeordnet werden")')->count() > 0, 'Failed to log in with a good username and password.');


        $crawler = $client->followRedirect(); // every time you redirect you must follow the redirect.

        $this->assertTrue($crawler->filter('html:contains("Welcome")')->count() > 0, 'Failed to log in with a good username and password.');
    }

    public function testSOme()
    {
        $client = self::createClient();

        $crawler = $client->request('GET', '/account');

        $this->assertTrue($client->getResponse()->isSuccessful());

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Ich bin bereits Kunde")')->count()
        );
    }

    private function logIn()
    {
        $session = $this->client->getContainer()->get('session');

        $firewall = 'secured_area';
        $token = new UsernamePasswordToken('admin', null, $firewall, array('ROLE_ADMIN'));
        $session->set('_security_'.$firewall, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }

}
