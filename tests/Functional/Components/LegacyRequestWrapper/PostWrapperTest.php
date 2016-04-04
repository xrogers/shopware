<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */
/**
 * @covers \Shopware\Components\LegacyRequestWrapper\PostWrapper
 */
class Shopware_Tests_Components_LegacyRequestWrapper_PostWrapperTest extends \Shopware\Tests\KernelTestCase
{
    private static $resources = array(
        'Admin',
        'Articles',
        'Basket',
        'Categories',
        'cms',
        'Core',
        'Export',
        'Marketing',
        'Order',
        'RewriteTable'
    );

    protected function setUp()
    {
        parent::setUp();
        parent::bootKernel();

        Shopware()->Front()->setRequest(
            new Enlight_Controller_Request_RequestTestCase()
        );

        /** @var $repository \Shopware\Models\Shop\Repository */
        $repository = Shopware()->Container()->get('models')->getRepository('Shopware\Models\Shop\Shop');

        $shop = $repository->getActiveDefault();
        $shop->registerResources();
    }

    /**
     * Tests that setting a value inside any core class is equivalent to setting it in the
     * global $_POST
     *
     * @return mixed
     */
    public function testSetPost()
    {
        $previousGetData = Shopware()->Front()->Request()->getPost();

        foreach (self::$resources as $name) {
            Shopware()->Front()->Request()->setPost($name, $name.'Value');
        }

        $getData = Shopware()->Front()->Request()->getPost();
        $this->assertNotEquals($previousGetData, $getData);

        foreach (self::$resources as $name) {
            if (property_exists($name, 'sSYSTEM')) {
                $this->assertEquals($getData, Shopware()->Modules()->getModule($name)->sSYSTEM->_POST->toArray());
            }
        }

        return $getData;
    }

    /**
     * Tests that resetting POST data inside any core class is equivalent to resetting it in the
     * global $_POST
     *
     * @param $getData
     * @return mixed
     * @depends testSetPost
     */
    public function testOverwriteAndClearPost($getData)
    {
        $this->assertNotEquals($getData, Shopware()->Front()->Request()->getPost());

        foreach (self::$resources as $name) {
            if (property_exists($name, 'sSYSTEM')) {
                $this->assertEquals($getData, Shopware()->Modules()->getModule($name)->sSYSTEM->_POST->toArray());
                Shopware()->Modules()->getModule($name)->sSYSTEM->_POST = array();
                Shopware()->Front()->Request()->setPost($getData);
                $this->assertNotEquals($getData, Shopware()->Modules()->getModule($name)->sSYSTEM->_POST->toArray());
            }
        }

        return $getData;
    }

    /**
     * Tests that getting POST data inside any core class is equivalent to getting it from the
     * global $_POST
     *
     * @depends testSetPost
     */
    public function testGetPost()
    {
        $previousGetData = Shopware()->Front()->Request()->getPost();

        foreach (self::$resources as $name) {
            Shopware()->Modules()->getModule($name)->sSYSTEM->_POST[$name] = $name.'Value';
        }

        $getData = Shopware()->Front()->Request()->getPost();
        $this->assertNotEquals($previousGetData, $getData);

        foreach (self::$resources as $name) {
            if (property_exists($name, 'sSYSTEM')) {
                $this->assertEquals($getData, Shopware()->Modules()->getModule($name)->sSYSTEM->_POST->toArray());
            }
        }
    }
}
