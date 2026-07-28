<?php

declare(strict_types=1);

namespace Mautic\LeadBundle\Tests\Form\Type;

use Mautic\CoreBundle\Form\Type\FormButtonsType;
use Mautic\LeadBundle\Form\Type\BatchType;
use Mautic\LeadBundle\Form\Type\CompanyBatchType;
use Mautic\LeadBundle\Form\Type\CompanySegmentActionType;
use Mautic\LeadBundle\Form\Type\CompanySegmentListType;
use Mautic\LeadBundle\Form\Type\LeadListType;
use Mautic\LeadBundle\Form\Type\ListActionType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class SegmentFormTypeTest extends TestCase
{
    public function testBatchFormsAddExpectedFieldsAndAction(): void
    {
        $items  = ['Segment A' => 1];
        $action = '/s/segments/batch';

        foreach ([new BatchType(), new CompanyBatchType()] as $type) {
            $this->assertSame($type instanceof BatchType ? 'lead_batch' : 'company_batch', $type->getBlockPrefix());
            $this->assertFormFields($type, ['items' => $items, 'action' => $action], $this->getBatchFields($items), $action);
        }
    }

    public function testContactBatchFormDoesNotSetAnEmptyAction(): void
    {
        $type = new BatchType();

        $this->assertFormFields($type, ['items' => [], 'action' => ''], $this->getBatchFields([]));
    }

    public function testContactBatchFormRetainsUnrestrictedItemsOption(): void
    {
        $type  = new BatchType();
        $items = new \ArrayObject(['Segment A' => 1]);

        $this->assertFormFields($type, ['items' => $items, 'action' => ''], $this->getBatchFields($items));
    }

    public function testCompanyBatchFormValidatesItemsAsAnArray(): void
    {
        $resolver = new OptionsResolver();
        (new CompanyBatchType())->configureOptions($resolver);

        $this->expectException(InvalidOptionsException::class);
        $resolver->resolve(['items' => 'not-an-array']);
    }

    public function testActionFormsAddExpectedFields(): void
    {
        $forms = [
            [
                new ListActionType(),
                'leadlist_action',
                LeadListType::class,
                'mautic.lead.lead.events.addtolists',
                'mautic.lead.lead.events.removefromlists',
            ],
            [
                new CompanySegmentActionType(),
                'companysegment_action',
                CompanySegmentListType::class,
                'mautic.company_segments.campaign.events.addtolists',
                'mautic.company_segments.campaign.events.removefromlists',
            ],
        ];

        foreach ($forms as [$type, $blockPrefix, $fieldType, $addLabel, $removeLabel]) {
            $this->assertSame($blockPrefix, $type->getBlockPrefix());
            $this->assertFormFields($type, [], [
                [
                    'addToLists',
                    $fieldType,
                    [
                        'label'      => $addLabel,
                        'label_attr' => ['class' => 'control-label'],
                        'attr'       => ['class' => 'form-control'],
                        'multiple'   => true,
                        'expanded'   => false,
                    ],
                ],
                [
                    'removeFromLists',
                    $fieldType,
                    [
                        'label'      => $removeLabel,
                        'label_attr' => ['class' => 'control-label'],
                        'attr'       => ['class' => 'form-control'],
                        'multiple'   => true,
                        'expanded'   => false,
                    ],
                ],
            ]);
        }
    }

    /**
     * @return array<array<mixed>>
     */
    private function getBatchFields(mixed $items): array
    {
        return [
            [
                'add',
                ChoiceType::class,
                [
                    'label'      => 'mautic.lead.batch.add_to',
                    'multiple'   => true,
                    'choices'    => $items,
                    'required'   => false,
                    'label_attr' => ['class' => 'control-label'],
                    'attr'       => ['class' => 'form-control'],
                ],
            ],
            [
                'remove',
                ChoiceType::class,
                [
                    'label'      => 'mautic.lead.batch.remove_from',
                    'multiple'   => true,
                    'choices'    => $items,
                    'required'   => false,
                    'label_attr' => ['class' => 'control-label'],
                    'attr'       => ['class' => 'form-control'],
                ],
            ],
            ['ids', HiddenType::class, []],
            [
                'buttons',
                FormButtonsType::class,
                [
                    'apply_text'     => false,
                    'save_text'      => 'mautic.core.form.save',
                    'cancel_onclick' => 'javascript:void(0);',
                    'cancel_attr'    => ['data-dismiss' => 'modal'],
                ],
            ],
        ];
    }

    /**
     * @param array<string, mixed> $options
     * @param array<array<mixed>>  $fields
     */
    private function assertFormFields(AbstractType $type, array $options, array $fields, ?string $action = null): void
    {
        /** @var MockObject&FormBuilderInterface $builder */
        $builder = $this->createMock(FormBuilderInterface::class);
        $matcher = $this->exactly(count($fields));

        $builder->expects($matcher)
            ->method('add')
            ->willReturnCallback(function (...$parameters) use ($fields, $matcher, $builder): FormBuilderInterface {
                $this->assertSame($fields[$matcher->numberOfInvocations() - 1], $parameters);

                return $builder;
            });

        if (null === $action) {
            $builder->expects($this->never())->method('setAction');
        } else {
            $builder->expects($this->once())->method('setAction')->with($action)->willReturn($builder);
        }

        $type->buildForm($builder, $options);
    }
}
