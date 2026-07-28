<?php

declare(strict_types=1);

namespace Mautic\LeadBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<mixed>
 */
class CompanyBatchType extends AbstractType
{
    use SegmentFormBuilderTrait;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->addBatchFields($builder, $options['items']);

        if (is_string($options['action']) && '' !== $options['action']) {
            $builder->setAction($options['action']);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('items');
        $resolver->setAllowedTypes('items', 'array');
    }

    public function getBlockPrefix(): string
    {
        return 'company_batch';
    }
}
