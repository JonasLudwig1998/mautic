<?php

declare(strict_types=1);

namespace Mautic\LeadBundle\Event;

use Mautic\LeadBundle\Entity\Company;
use Mautic\LeadBundle\Entity\CompanySegment;
use Symfony\Contracts\EventDispatcher\Event;

final class CompanySegmentChangeEvent extends Event
{
    public function __construct(
        private readonly Company $company,
        private readonly CompanySegment $companySegment,
        private readonly bool $added = true,
        private readonly ?\DateTimeInterface $date = null,
    ) {
    }

    public function getCompany(): Company
    {
        return $this->company;
    }

    public function getCompanySegment(): CompanySegment
    {
        return $this->companySegment;
    }

    public function wasAdded(): bool
    {
        return $this->added;
    }

    public function wasRemoved(): bool
    {
        return !$this->added;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }
}
