<?php

declare(strict_types=1);

namespace Mautic\LeadBundle\Event;

use Mautic\LeadBundle\Entity\CompanySegment;
use Mautic\LeadBundle\Segment\Query\QueryBuilder;
use Symfony\Contracts\EventDispatcher\Event;

final class CompanySegmentQueryBuilderGeneratedEvent extends Event
{
    public function __construct(
        private readonly CompanySegment $companySegment,
        private readonly QueryBuilder $queryBuilder,
    ) {
    }

    public function getCompanySegment(): CompanySegment
    {
        return $this->companySegment;
    }

    public function getQueryBuilder(): QueryBuilder
    {
        return $this->queryBuilder;
    }
}
