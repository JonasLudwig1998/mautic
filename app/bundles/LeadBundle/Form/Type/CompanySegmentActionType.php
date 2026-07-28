<?php

declare(strict_types=1);

namespace Mautic\LeadBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class CompanySegmentActionType extends AbstractType
{
    use SegmentFormBuilderTrait;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->addActionFields(
            $builder,
            CompanySegmentListType::class,
            'mautic.company_segments.campaign.events.addtolists',
            'mautic.company_segments.campaign.events.removefromlists'
        );
    }

    public function getBlockPrefix(): string
    {
        return 'companysegment_action';
    }
}
