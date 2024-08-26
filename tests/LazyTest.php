<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL v3 or later
 */

namespace Tests\Matomo\Cache;

use Matomo\Cache\Backend\ArrayCache;
use Matomo\Cache\Lazy;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Matomo\Cache\Lazy
 */
 #[\PHPUnit\Framework\Attributes\CoversClass(Lazy::class)]
class LazyTest extends TestCase
{
    /**
     * @var Lazy
     */
    private $cache;

    private $cacheId = 'testid';
    private $cacheValue = 'exampleValue';

    protected function setUp(): void
    {
        $backend = new ArrayCache();
        $this->cache = new Lazy($backend);
        $this->cache->save($this->cacheId, $this->cacheValue);
    }

    public function test_fetch_shouldFail_IfCacheIdIsEmpty()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Empty cache id');

        $this->cache->fetch('');
    }

    /**
     * @dataProvider getInvalidCacheIds
     */
     #[\PHPUnit\Framework\Attributes\DataProvider('getInvalidCacheIds')]
    public function test_shouldFail_IfCacheIdIsInvalid($method, $id)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid cache id');
        $this->executeMethodOnCache($method, $id);
    }

    /**
     * @dataProvider getValidCacheIds
     */
     #[\PHPUnit\Framework\Attributes\DataProvider('getValidCacheIds')]
    public function test_shouldNotFail_IfCacheIdIsValid($method, $id)
    {
        $this->executeMethodOnCache($method, $id);
        $this->assertTrue(true);
    }

    private function executeMethodOnCache($method, $id)
    {
        if ('save' === $method) {
            $this->cache->$method($id, 'val');
        } else {
            $this->cache->$method($id);
        }
    }

    public function test_fetch_shouldReturnFalse_IfNoSuchCacheIdExists()
    {
        $this->assertFalse($this->cache->fetch('randomid'));
    }

    public function test_fetch_shouldReturnTheCachedValue_IfCacheIdExists()
    {
        $this->assertEquals($this->cacheValue, $this->cache->fetch($this->cacheId));
    }

    public function test_contains_shouldReturnFalse_IfNoSuchCacheIdExists()
    {
        $this->assertFalse($this->cache->contains('randomid'));
    }

    public function test_contains_shouldReturnTrue_IfCacheIdExists()
    {
        $this->assertTrue($this->cache->contains($this->cacheId));
    }

    public function test_delete_shouldReturnTrue_OnSuccess()
    {
        $this->assertTrue($this->cache->delete($this->cacheId));
    }

    public function test_delete_shouldActuallyDeleteCacheId()
    {
        $this->assertHasCacheEntry($this->cacheId);

        $this->cache->delete($this->cacheId);

        $this->assertHasNotCacheEntry($this->cacheId);
    }

    public function test_delete_shouldNotDeleteAnyOtherCacheIds()
    {
        $this->cache->save('anyother', 'myvalue');
        $this->assertHasCacheEntry($this->cacheId);

        $this->cache->delete($this->cacheId);

        $this->assertHasCacheEntry('anyother');
    }

    public function test_save_shouldOverwriteAnyValue_IfCacheIdAlreadyExists()
    {
        $this->assertHasCacheEntry($this->cacheId);

        $value = 'anyotherValuE';
        $this->cache->save($this->cacheId, $value);

        $this->assertSame($value, $this->cache->fetch($this->cacheId));
    }

    public function test_save_shouldBeAbleToSetArrays()
    {
        $value = array('anyotherE' => 'anyOtherValUE', 1 => array(2));
        $this->cache->save($this->cacheId, $value);

        $this->assertSame($value, $this->cache->fetch($this->cacheId));
    }

    public function test_save_shouldFail_IfTryingToSetAnObject()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('cannot use this cache to cache an object');

        $value = (object) array('anyotherE' => 'anyOtherValUE', 1 => array(2));
        $this->cache->save($this->cacheId, $value);

        $this->assertSame($value, $this->cache->fetch($this->cacheId));
    }

    public function test_save_shouldBeAbleToSetNumbers()
    {
        $value = 5.4;
        $this->cache->save($this->cacheId, $value);

        $this->assertSame($value, $this->cache->fetch($this->cacheId));
    }

    public function test_flush_shouldRemoveAllCacheIds()
    {
        $this->assertHasCacheEntry($this->cacheId);
        $this->cache->save('mykey', 'myvalue');
        $this->assertHasCacheEntry('mykey');

        $this->cache->flushAll();

        $this->assertHasNotCacheEntry($this->cacheId);
        $this->assertHasNotCacheEntry('mykey');
    }

    private function assertHasCacheEntry($cacheId)
    {
        $this->assertTrue($this->cache->contains($cacheId));
    }

    private function assertHasNotCacheEntry($cacheId)
    {
        $this->assertFalse($this->cache->contains($cacheId));
    }

    public static function getInvalidCacheIds()
    {
        $ids = array();
        $methods = array('fetch', 'save', 'contains', 'delete');

        foreach ($methods as $method) {
            $ids[] = array($method, 'eteer#');
            $ids[] = array($method, '-test');
            $ids[] = array($method, '_test');
            $ids[] = array($method, '.test');
            $ids[] = array($method, 'test/test');
            $ids[] = array($method, '../test/');
            $ids[] = array($method, 'test0*');
            $ids[] = array($method, 'test\\test');
        }

        return $ids;
    }

    public static function getValidCacheIds()
    {
        $ids = array();
        $methods = array('fetch', 'save', 'contains', 'delete');

        foreach ($methods as $method) {
            $ids[] = array($method, '012test');
            $ids[] = array($method, 'test012test');
            $ids[] = array($method, 't.est.012test');
            $ids[] = array($method, 't-est-test');
            $ids[] = array($method, 't_est_tes4t');
            $ids[] = array($method, 't_est.te-s2t');
            $ids[] = array($method, 't_est...te-s2t');
        }

        return $ids;
    }
}
