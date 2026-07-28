<?php

declare(strict_types=1);

namespace Mautic\LeadBundle\Segment;

use Mautic\LeadBundle\Segment\Query\QueryBuilder;

trait SegmentQueryExecutionTrait
{
    private function formatPeriod(float $inputSeconds): string
    {
        $now = \DateTime::createFromFormat('U.u', number_format($inputSeconds, 6, '.', ''));
        \assert(false !== $now);

        return $now->format('H:i:s.u');
    }

    /**
     * @throws \Exception
     */
    private function timedFetch(QueryBuilder $qb, int $segmentId, string $logPrefix): mixed
    {
        try {
            $start = microtime(true);

            $result = $qb->executeQuery()->fetchAssociative();

            $end = microtime(true) - $start;

            $this->logger->debug($logPrefix.': Query took: '.$this->formatPeriod($end).', Result count: '.count($result), ['segmentId' => $segmentId]);
        } catch (\Exception $e) {
            $this->logger->error(
                $logPrefix.': Query Exception: '.$e->getMessage(),
                [
                    'query'      => $qb->getSQL(),
                    'parameters' => $qb->getParameters(),
                ]
            );
            throw $e;
        }

        return $result;
    }

    /**
     * @return array<mixed>
     *
     * @throws \Exception
     */
    private function timedFetchAll(QueryBuilder $qb, int $segmentId, string $logPrefix): array
    {
        try {
            $start  = microtime(true);
            $result = $qb->executeQuery()->fetchAllAssociative();

            $end = microtime(true) - $start;

            $this->logger->debug(
                $logPrefix.': Query took: '.$this->formatPeriod($end).'ms. Result count: '.count($result),
                ['segmentId' => $segmentId]
            );
        } catch (\Exception $e) {
            $this->logger->error(
                $logPrefix.': Query Exception: '.$e->getMessage(),
                [
                    'query'      => $qb->getSQL(),
                    'parameters' => $qb->getParameters(),
                ]
            );
            throw $e;
        }

        return $result;
    }
}
