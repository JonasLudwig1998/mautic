<?php

declare(strict_types=1);

namespace Mautic\LeadBundle\Tests\Segment;

use Doctrine\DBAL\Result;
use Mautic\LeadBundle\Segment\Query\QueryBuilder;
use Mautic\LeadBundle\Segment\SegmentQueryExecutionTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class SegmentQueryExecutionTraitTest extends TestCase
{
    private LoggerInterface&MockObject $logger;

    private object $service;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->service = new class($this->logger) {
            use SegmentQueryExecutionTrait;

            public function __construct(
                private LoggerInterface $logger,
            ) {
            }

            public function fetch(QueryBuilder $queryBuilder, int $segmentId, string $logPrefix): mixed
            {
                return $this->timedFetch($queryBuilder, $segmentId, $logPrefix);
            }

            /**
             * @return array<mixed>
             */
            public function fetchAll(QueryBuilder $queryBuilder, int $segmentId, string $logPrefix): array
            {
                return $this->timedFetchAll($queryBuilder, $segmentId, $logPrefix);
            }
        };
    }

    public function testFetchReturnsSingleRowAndLogsTiming(): void
    {
        $result = $this->createMock(Result::class);
        $result->expects($this->once())->method('fetchAssociative')->willReturn(['count' => '1']);
        $queryBuilder = $this->createQueryBuilder($result);

        $this->logger
            ->expects($this->once())
            ->method('debug')
            ->with(
                $this->stringStartsWith('Company Segment QB: Query took: '),
                ['segmentId' => 12]
            );

        $this->assertSame(['count' => '1'], $this->service->fetch($queryBuilder, 12, 'Company Segment QB'));
    }

    public function testFetchAllReturnsRowsAndLogsTiming(): void
    {
        $result = $this->createMock(Result::class);
        $result->expects($this->once())->method('fetchAllAssociative')->willReturn([['id' => 1], ['id' => 2]]);
        $queryBuilder = $this->createQueryBuilder($result);

        $this->logger
            ->expects($this->once())
            ->method('debug')
            ->with(
                $this->stringStartsWith('Segment QB: Query took: '),
                ['segmentId' => 12]
            );

        $this->assertSame([['id' => 1], ['id' => 2]], $this->service->fetchAll($queryBuilder, 12, 'Segment QB'));
    }

    public function testFetchRethrowsTheSameExceptionAndLogsQueryContext(): void
    {
        $exception    = new \RuntimeException('Broken query');
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->expects($this->once())->method('executeQuery')->willThrowException($exception);
        $queryBuilder->expects($this->once())->method('getSQL')->willReturn('SELECT id FROM companies');
        $queryBuilder->expects($this->once())->method('getParameters')->willReturn(['segment' => 12]);

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with(
                'Company Segment QB: Query Exception: Broken query',
                [
                    'query'      => 'SELECT id FROM companies',
                    'parameters' => ['segment' => 12],
                ]
            );

        try {
            $this->service->fetch($queryBuilder, 12, 'Company Segment QB');
            self::fail('Expected the query exception to be rethrown.');
        } catch (\RuntimeException $caught) {
            $this->assertSame($exception, $caught);
        }
    }

    private function createQueryBuilder(Result $result): QueryBuilder&MockObject
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->expects($this->once())->method('executeQuery')->willReturn($result);

        return $queryBuilder;
    }
}
