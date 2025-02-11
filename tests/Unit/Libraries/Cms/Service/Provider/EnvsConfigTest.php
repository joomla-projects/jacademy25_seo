<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Service
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Service\Provider;

use Joomla\CMS\Service\Provider\EnvsConfig;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for EnvsConfig.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Service
 * @since       __DEPLOY_VERSION__
 *
 * @backupGlobals enabled
 */
class EnvsConfigTest extends UnitTestCase
{
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function setUp(): void
    {
        parent::setUp();

        EnvsConfig::reload();
    }

    /**
     * @testdox Returns config value loaded from environment
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @covers  EnvsConfig::get
     */
    public function testGet()
    {
        $_SERVER['JOOMLA_DEBUG']    = '1';
        $_SERVER['JOOMLA_ASSET_ID'] = '42';
        $_SERVER['JOOMLA_HOST']     = '127.0.0.1';

        $this->assertSame(EnvsConfig::get('debug'), true);
        $this->assertSame(EnvsConfig::get('asset_id'), 42);
        $this->assertSame(EnvsConfig::get('host'), '127.0.0.1');
    }

    /**
     * @testdox Allows to return default value if environment variable is not set
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @covers  EnvsConfig::get
     */
    public function testGetDefault()
    {
        $this->assertSame(EnvsConfig::get('host', '127.0.0.1'), '127.0.0.1');
    }

    /**
     * @testdox Uses aliases for some inconsistent envs names
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @covers  EnvsConfig::get
     */
    public function testGetAliases()
    {
        $_SERVER['JOOMLA_DBHOST']     = 'dbhost';
        $_SERVER['JOOMLA_DBNAME']     = 'dbname';
        $_SERVER['JOOMLA_DBUSER']     = 'dbuser';
        $_SERVER['JOOMLA_DBPASSWORD'] = 'dbpassword';

        $_SERVER['JOOMLA_META_AUTHOR']  = '1';
        $_SERVER['JOOMLA_META_DESC']    = 'metadesc';
        $_SERVER['JOOMLA_META_RIGHTS']  = 'metarights';
        $_SERVER['JOOMLA_META_VERSION'] = '0';

        $_SERVER['JOOMLA_SESSION_LIFETIME'] = '1000';

        $_SERVER['JOOMLA_LOCALE_OFFSET'] = 'UTC';

        $this->assertSame(EnvsConfig::get('host'), 'dbhost');
        $this->assertSame(EnvsConfig::get('db'), 'dbname');
        $this->assertSame(EnvsConfig::get('user'), 'dbuser');
        $this->assertSame(EnvsConfig::get('password'), 'dbpassword');

        $this->assertSame(EnvsConfig::get('MetaAuthor'), true);
        $this->assertSame(EnvsConfig::get('MetaDesc'), 'metadesc');
        $this->assertSame(EnvsConfig::get('MetaRights'), 'metarights');
        $this->assertSame(EnvsConfig::get('MetaVersion'), false);

        $this->assertSame(EnvsConfig::get('offset'), 'UTC');
    }

    /**
     * @testdox Allows to check if config key exists
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @covers  EnvsConfig::has
     */
    public function testHas()
    {
        $_SERVER['JOOMLA_DEBUG'] = '1';

        $this->assertTrue(EnvsConfig::has('debug'));
        $this->assertFalse(EnvsConfig::has('error_reporting'));
    }
}
