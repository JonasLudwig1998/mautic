<?php

namespace Mautic\LeadBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<mixed>
 */
final class BatchType extends AbstractType
{
    use SegmentFormBuilderTrait;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->addBatchFields($builder, $options['items']);

        if (!empty($options['action'])) {
            $builder->setAction($options['action']);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(
            [
                'items',
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return 'lead_batch';
    }
}
