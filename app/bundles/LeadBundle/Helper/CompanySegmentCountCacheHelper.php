<?php

declare(strict_types=1);

namespace Mautic\LeadBundle\Helper;

use Mautic\CacheBundle\Cache\CacheProviderInterface;
use Mautic\CoreBundle\Helper\CoreParametersHelper;
use Psr\Cache\InvalidArgumentException;

final readonly class CompanySegmentCountCacheHelper
{
    use SegmentCountCacheOperationsTrait;

    public function __construct(
        private CacheProviderInterface $cacheProvider,
        private CoreParametersHelper $coreParametersHelper,
    ) {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getSegmentCompanyCount(int $segmentId): int
    {
        return $this->getCount($this->generateCacheKey($segmentId));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setSegmentCompanyCount(int $segmentId, int $count): void
    {
        $this->setCount($this->generateCacheKey($segmentId), $count, $this->getTtl());
    }

    /**
     * @throws InvalidArgumentException
     */
    public function hasSegmentCompanyCount(int $segmentId): bool
    {
        return $this->hasCount($this->generateCacheKey($segmentId));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function invalidateSegmentCompanyCount(int $segmentId): void
    {
        $this->deleteCount($this->generateCacheKey($segmentId));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function incrementSegmentCompanyCount(int $segmentId): void
    {
        $this->incrementCount($this->generateCacheKey($segmentId), $this->getTtl());
    }

    /**
     * @throws InvalidArgumentException
     */
    public function decrementSegmentCompanyCount(int $segmentId): void
    {
        $this->decrementCount($this->generateCacheKey($segmentId), $this->getTtl());
    }

    private function getTtl(): mixed
    {
        return $this->coreParametersHelper->get('segment_api_count_cache_ttl', 43200);
    }

    private function generateCacheKey(int $segmentId): string
    {
        return sprintf('%s.%s.%s', 'segment', $segmentId, 'company');
    }
}
