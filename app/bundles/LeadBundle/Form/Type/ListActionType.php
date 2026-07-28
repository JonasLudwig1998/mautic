<?php

namespace Mautic\LeadBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @extends AbstractType<mixed>
 */
final class ListActionType extends AbstractType
{
    use SegmentFormBuilderTrait;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->addActionFields(
            $builder,
            LeadListType::class,
            'mautic.lead.lead.events.addtolists',
            'mautic.lead.lead.events.removefromlists'
        );
    }

    public function getBlockPrefix(): string
    {
        return 'leadlist_action';
    }
}
