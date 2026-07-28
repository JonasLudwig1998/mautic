<?php

declare(strict_types=1);

namespace Mautic\LeadBundle\Tests\Helper;

use Mautic\CacheBundle\Cache\CacheProviderInterface;
use Mautic\CoreBundle\Helper\CoreParametersHelper;
use Mautic\CoreBundle\Test\ReflectionHelper;
use Mautic\LeadBundle\Helper\CompanySegmentCountCacheHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\CacheItem;

final class CompanySegmentCountCacheHelperTest extends TestCase
{
    private MockObject&CacheProviderInterface $cacheProvider;

    private MockObject&CoreParametersHelper $coreParametersHelper;

    private CompanySegmentCountCacheHelper $helper;

    protected function setUp(): void
    {
        $this->cacheProvider        = $this->createMock(CacheProviderInterface::class);
        $this->coreParametersHelper = $this->createMock(CoreParametersHelper::class);
        $this->helper               = new CompanySegmentCountCacheHelper($this->cacheProvider, $this->coreParametersHelper);
    }

    public function testSetUsesCompanyKeyAndTtl(): void
    {
        $cacheItem = $this->createCacheItem('segment.12.company');

        $this->cacheProvider->expects($this->once())->method('getItem')->with('segment.12.company')->willReturn($cacheItem);
        $this->cacheProvider->expects($this->once())->method('save')->with($cacheItem);
        $this->coreParametersHelper->expects($this->once())->method('get')->with('segment_api_count_cache_ttl', 43200)->willReturn(60);

        $before = microtime(true);
        $this->helper->setSegmentCompanyCount(12, 7);

        $expiry = (new \ReflectionProperty(CacheItem::class, 'expiry'))->getValue($cacheItem);
        $this->assertSame(7, $cacheItem->get());
        $this->assertGreaterThanOrEqual($before + 60, $expiry);
    }

    public function testSetDoesNotAddExpiryWhenTtlIsDisabled(): void
    {
        $cacheItem = $this->createCacheItem('segment.12.company');

        $this->cacheProvider->method('getItem')->willReturn($cacheItem);
        $this->cacheProvider->method('save')->willReturn(true);
        $this->coreParametersHelper->method('get')->willReturn(0);

        $this->helper->setSegmentCompanyCount(12, 7);

        $this->assertNull((new \ReflectionProperty(CacheItem::class, 'expiry'))->getValue($cacheItem));
    }

    public function testIncrementAndDecrementDoNotGoBelowZero(): void
    {
        $cacheItem = $this->createCacheItem('segment.12.company', 0);

        $this->cacheProvider->method('hasItem')->with('segment.12.company')->willReturn(true);
        $this->cacheProvider->method('getItem')->with('segment.12.company')->willReturn($cacheItem);
        $this->cacheProvider->method('save')->willReturn(true);
        $this->coreParametersHelper->method('get')->willReturn(60);

        $this->helper->incrementSegmentCompanyCount(12);
        $this->assertSame(1, $cacheItem->get());

        $this->helper->decrementSegmentCompanyCount(12);
        $this->assertSame(0, $cacheItem->get());

        $this->helper->decrementSegmentCompanyCount(12);
        $this->assertSame(0, $cacheItem->get());
    }

    public function testInvalidateDeletesExistingCompanyCount(): void
    {
        $this->cacheProvider->expects($this->once())->method('hasItem')->with('segment.12.company')->willReturn(true);
        $this->cacheProvider->expects($this->once())->method('deleteItem')->with('segment.12.company')->willReturn(true);

        $this->helper->invalidateSegmentCompanyCount(12);
    }

    public function testMissingCompanyCountOperationsAreNoOps(): void
    {
        $this->cacheProvider->expects($this->exactly(2))->method('hasItem')->with('segment.12.company')->willReturn(false);
        $this->cacheProvider->expects($this->never())->method('getItem');
        $this->cacheProvider->expects($this->never())->method('save');
        $this->cacheProvider->expects($this->never())->method('deleteItem');

        $this->helper->decrementSegmentCompanyCount(12);
        $this->helper->invalidateSegmentCompanyCount(12);
    }

    private function createCacheItem(string $key, mixed $value = null): CacheItem
    {
        $item = (new \ReflectionClass(CacheItem::class))->newInstanceWithoutConstructor();

        ReflectionHelper::setValue($item, 'key', $key);
        ReflectionHelper::setValue($item, 'value', $value);

        return $item;
    }
}
