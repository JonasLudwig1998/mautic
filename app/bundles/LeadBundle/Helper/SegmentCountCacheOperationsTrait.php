<?php

declare(strict_types=1);

namespace Mautic\LeadBundle\Helper;

use Psr\Cache\InvalidArgumentException;

trait SegmentCountCacheOperationsTrait
{
    /**
     * @throws InvalidArgumentException
     */
    private function getCount(string $cacheKey): int
    {
        return (int) $this->cacheProvider->getItem($cacheKey)->get();
    }

    /**
     * @throws InvalidArgumentException
     */
    private function setCount(string $cacheKey, int $count, mixed $ttl): void
    {
        $item = $this->cacheProvider->getItem($cacheKey);
        $item->set($count);

        if ($ttl) {
            $item->expiresAfter($ttl);
        }

        $this->cacheProvider->save($item);
    }

    /**
     * @throws InvalidArgumentException
     */
    private function hasCount(string $cacheKey): bool
    {
        return $this->cacheProvider->hasItem($cacheKey);
    }

    /**
     * @throws InvalidArgumentException
     */
    private function incrementCount(string $cacheKey, mixed $ttl): void
    {
        $count = $this->hasCount($cacheKey) ? $this->getCount($cacheKey) : 0;
        $this->setCount($cacheKey, ++$count, $ttl);
    }

    /**
     * @throws InvalidArgumentException
     */
    private function decrementCount(string $cacheKey, mixed $ttl): bool
    {
        if (!$this->hasCount($cacheKey)) {
            return false;
        }

        $count = $this->getCount($cacheKey);
        $this->setCount($cacheKey, max(0, $count - 1), $ttl);

        return true;
    }

    /**
     * @throws InvalidArgumentException
     */
    private function deleteCount(string $cacheKey): void
    {
        if ($this->hasCount($cacheKey)) {
            $this->cacheProvider->deleteItem($cacheKey);
        }
    }
}
