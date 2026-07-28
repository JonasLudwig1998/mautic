<?php

declare(strict_types=1);

namespace Mautic\LeadBundle\Form\Type;

use Mautic\CoreBundle\Form\Type\FormButtonsType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

trait SegmentFormBuilderTrait
{
    private function addBatchFields(FormBuilderInterface $builder, mixed $items): void
    {
        $builder->add(
            'add',
            ChoiceType::class,
            [
                'label'      => 'mautic.lead.batch.add_to',
                'multiple'   => true,
                'choices'    => $items,
                'required'   => false,
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => 'form-control'],
            ]
        );

        $builder->add(
            'remove',
            ChoiceType::class,
            [
                'label'      => 'mautic.lead.batch.remove_from',
                'multiple'   => true,
                'choices'    => $items,
                'required'   => false,
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => 'form-control'],
            ]
        );

        $builder->add('ids', HiddenType::class);

        $builder->add(
            'buttons',
            FormButtonsType::class,
            [
                'apply_text'     => false,
                'save_text'      => 'mautic.core.form.save',
                'cancel_onclick' => 'javascript:void(0);',
                'cancel_attr'    => [
                    'data-dismiss' => 'modal',
                ],
            ]
        );
    }

    /**
     * @param class-string $fieldType
     */
    private function addActionFields(FormBuilderInterface $builder, string $fieldType, string $addLabel, string $removeLabel): void
    {
        $builder->add(
            'addToLists',
            $fieldType,
            [
                'label'      => $addLabel,
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class' => 'form-control',
                ],
                'multiple' => true,
                'expanded' => false,
            ]
        );

        $builder->add(
            'removeFromLists',
            $fieldType,
            [
                'label'      => $removeLabel,
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class' => 'form-control',
                ],
                'multiple' => true,
                'expanded' => false,
            ]
        );
    }
}
