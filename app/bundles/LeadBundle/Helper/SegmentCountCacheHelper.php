<?php

declare(strict_types=1);

namespace Mautic\LeadBundle\Helper;

use Mautic\CacheBundle\Cache\CacheProviderInterface;
use Mautic\CoreBundle\Helper\CoreParametersHelper;
use Psr\Cache\InvalidArgumentException;

class SegmentCountCacheHelper
{
    use SegmentCountCacheOperationsTrait;

    public function __construct(
        private readonly CacheProviderInterface $cacheProvider,
        private readonly CoreParametersHelper $coreParametersHelper,
    ) {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getSegmentContactCount(int $segmentId): int
    {
        return $this->getCount($this->generateCacheKey($segmentId));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setSegmentContactCount(int $segmentId, int $count): void
    {
        $this->setCount($this->generateCacheKey($segmentId), $count, $this->getTtl());

        if ($this->hasSegmentIdForReCount($segmentId)) {
            $this->cacheProvider->deleteItem($this->generateCacheKeyForRecount($segmentId));
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    public function hasSegmentContactCount(int $segmentId): bool
    {
        return $this->hasCount($this->generateCacheKey($segmentId));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function hasSegmentIdForReCount(int $segmentId): bool
    {
        return $this->cacheProvider->hasItem($this->generateCacheKeyForRecount($segmentId));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function invalidateSegmentContactCount(int $segmentId): void
    {
        $item = $this->cacheProvider->getItem($this->generateCacheKeyForRecount($segmentId));
        $item->set(true);
        $this->cacheProvider->save($item);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function incrementSegmentContactCount(int $segmentId): void
    {
        $this->incrementCount($this->generateCacheKey($segmentId), $this->getTtl());
        $this->clearRecountMarker($segmentId);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function deleteSegmentContactCount(int $segmentId): void
    {
        $this->deleteCount($this->generateCacheKey($segmentId));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function decrementSegmentContactCount(int $segmentId): void
    {
        if ($this->decrementCount($this->generateCacheKey($segmentId), $this->getTtl())) {
            $this->clearRecountMarker($segmentId);
        }
    }

    private function getTtl(): mixed
    {
        return $this->coreParametersHelper->get('segment_api_count_cache_ttl', 43200);
    }

    private function clearRecountMarker(int $segmentId): void
    {
        if ($this->hasSegmentIdForReCount($segmentId)) {
            $this->cacheProvider->deleteItem($this->generateCacheKeyForRecount($segmentId));
        }
    }

    private function generateCacheKey(int $segmentId): string
    {
        return sprintf('%s.%s.%s', 'segment', $segmentId, 'lead');
    }

    private function generateCacheKeyForRecount(int $segmentId): string
    {
        return sprintf('%s.%s', $this->generateCacheKey($segmentId), 'recount');
    }
}
