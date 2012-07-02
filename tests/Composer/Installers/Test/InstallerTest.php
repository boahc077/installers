<?php
namespace Composer\Installers\Test;

use Composer\Installers\Installer;
use Composer\Util\Filesystem;
use Composer\Package\MemoryPackage;
use Composer\Composer;
use Composer\Config;

class InstallerTest extends TestCase
{
    private $composer;
    private $config;
    private $vendorDir;
    private $binDir;
    private $dm;
    private $repository;
    private $io;
    private $fs;

    /**
     * setUp
     *
     * @return void
     */
    public function setUp()
    {
        $this->fs = new Filesystem;

        $this->composer = new Composer();
        $this->config = new Config();
        $this->composer->setConfig($this->config);

        $this->vendorDir = realpath(sys_get_temp_dir()) . DIRECTORY_SEPARATOR . 'baton-test-vendor';
        $this->ensureDirectoryExistsAndClear($this->vendorDir);

        $this->binDir = realpath(sys_get_temp_dir()) . DIRECTORY_SEPARATOR . 'baton-test-bin';
        $this->ensureDirectoryExistsAndClear($this->binDir);

        $this->config->merge(array(
            'config' => array(
                'vendor-dir' => $this->vendorDir,
                'bin-dir' => $this->binDir,
            ),
        ));

        $this->dm = $this->getMockBuilder('Composer\Downloader\DownloadManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->composer->setDownloadManager($this->dm);

        $this->repository = $this->getMock('Composer\Repository\InstalledRepositoryInterface');
        $this->io = $this->getMock('Composer\IO\IOInterface');
    }

    /**
     * tearDown
     *
     * @return void
     */
    public function tearDown()
    {
        $this->fs->removeDirectory($this->vendorDir);
        $this->fs->removeDirectory($this->binDir);
    }

    /**
     * testSupports
     *
     * @return void
     *
     * @dataProvider dataForTestSupport
     */
    public function testSupports($type, $expected)
    {
        $Installer = new Installer($this->io, $this->composer);
        $this->assertSame($expected, $Installer->supports($type), sprintf('Failed to show support for %s', $type));
    }

    /**
     * dataForTestSupport
     */
    public function dataForTestSupport()
    {
        return array(
            array('cakephp', false),
            array('cakephp-', false),
            array('cakephp-app', true),
            array('codeigniter-app', true),
            array('drupal-module', true),
            array('fuelphp-module', true),
            array('joomla-library', true),
            array('laravel-library', true),
            array('lithium-library', true),
            array('magento-library', true),
            array('phpbb-extension', true),
            array('ppi-module', true),
            array('symfony1-plugin', true),
            array('wordpress-plugin', true),
            array('zend-library', true),
        );
    }

    /**
     * testInstallPath
     *
     * @dataProvider dataForTestInstallPath
     */
    public function testInstallPath($type, $path, $name)
    {
        $Installer = new Installer($this->io, $this->composer);
        $Package = new MemoryPackage($name, '1.0.0', '1.0.0');

        $Package->setType($type);
        $result = $Installer->getInstallPath($Package);
        $this->assertEquals($path, $result);
    }

    /**
     * dataFormTestInstallPath
     */
    public function dataForTestInstallPath()
    {
        return array(
            array('cakephp-plugin', 'Plugin/Ftp/', 'shama/ftp'),
            array('codeigniter-library', 'libraries/my_package/', 'shama/my_package'),
            array('drupal-module', 'modules/my_module/', 'shama/my_module'),
            array('fuelphp-module', 'modules/my_package/', 'shama/my_package'),
            array('joomla-plugin', 'plugins/my_plugin/', 'shama/my_plugin'),
            array('laravel-library', 'libraries/my_package/', 'shama/my_package'),
            array('lithium-library', 'libraries/li3_test/', 'user/li3_test'),
            array('magento-library', 'lib/foo/', 'test/foo'),
            array('phpbb-extension', 'ext/test/foo/', 'test/foo'),
            array('ppi-module', 'modules/foo/', 'test/foo'),
            array('symfony1-plugin', 'plugins/sfShamaPlugin/', 'shama/sfShamaPlugin'),
            array('wordpress-plugin', 'wp-content/plugins/my_plugin/', 'shama/my_plugin'),
            array('zend-extra', 'extras/library/', 'shama/zend_test'),
        );
    }

    /**
     * testGetCakePHPInstallPathException
     *
     * @return void
     *
     * @expectedException \InvalidArgumentException
     */
    public function testGetCakePHPInstallPathException()
    {
        $Installer = new Installer($this->io, $this->composer);
        $Package = new MemoryPackage('shama/ftp', '1.0.0', '1.0.0');

        $Package->setType('cakephp-whoops');
        $result = $Installer->getInstallPath($Package);
    }

}