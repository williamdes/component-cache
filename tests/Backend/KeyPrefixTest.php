<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL v3 or later
 */

namespace Tests\Matomo\Cache\Backend;

use Matomo\Cache\Backend\KeyPrefixDecorated;
use Matomo\Cache\Backend\NullCache;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Matomo\Cache\Backend\KeyPrefixDecorated
 */
class KeyPrefixTest extends TestCase
{
    /**
     * @var KeyPrefixDecorated
     */
    private $cache;

    /**
     * @var NullCache
     */
    private $backendMock;


    private $keyPrefix = 'somePrefix';

    public function setUp(): void
    {
        $this->backendMock = $this->getMockBuilder(NullCache::class)->getMock();


        $opts = ['keyPrefix'=>$this->keyPrefix];

        $this->cache = new KeyPrefixDecorated($this->backendMock, $opts);
    }

    public function doFetch_shouldCallDecoratedWithKeyPrefixTest()
    {
        $this->backendMock
            ->expects($this->once())
            ->method('doFetch')
            ->with($this->stringStartsWith($this->keyPrefix));

        $this->cache->doFetch('randomid');
    }

    public function doContains_shouldCallDecoratedWithKeyPrefixTest()
    {
        $this->backendMock
            ->expects($this->once())
            ->method('doContains')
            ->with($this->stringStartsWith($this->keyPrefix));

        $this->cache->doContains('randomid');
    }

    public function doSave_shouldCallDecoratedWithKeyPrefixTest()
    {
        $this->backendMock
            ->expects($this->once())
            ->method('doSave')
            ->with($this->stringStartsWith($this->keyPrefix),
                   $this->anything(),
                   $this->anything());

        $this->cache->doSave('randomid', 'anyvalue');
    }

    public function doDelete_shouldCallDecoratedWithKeyPrefixTest()
    {
        $this->backendMock
            ->expects($this->once())
            ->method('doDelete')
            ->with($this->stringStartsWith($this->keyPrefix));

        $this->cache->doDelete('randomid');
    }
}
