<?php

declare(strict_types=1);

namespace Mautic\LeadBundle\Tests\Model;

use Mautic\CoreBundle\Entity\FormEntity;
use Mautic\LeadBundle\Entity\CompanySegment;
use Mautic\LeadBundle\Entity\CompanySegmentRepository;
use Mautic\LeadBundle\Event\CompanySegmentEvent;
use Mautic\LeadBundle\Event\CompanySegmentPostDelete;
use Mautic\LeadBundle\Event\CompanySegmentPostSave;
use Mautic\LeadBundle\Event\CompanySegmentPreDelete;
use Mautic\LeadBundle\Event\CompanySegmentPreSave;
use Mautic\LeadBundle\Model\CompanySegmentModel;
use Mautic\LeadBundle\Tests\Fixtures\CompanySegmentModelStub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

final class CompanySegmentModelTest extends TestCase
{
    public function testAliasIsUnique(): void
    {
        $id    = null;
        $alias = 'alias';
        $model = $this->getMockBuilder(CompanySegmentModel::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->onlyMethods(['getRepository', 'setTimestamps', 'cleanAlias', 'dispatchEvent'])
            ->getMock();

        $companySegment = $this->createMock(CompanySegment::class);
        $companySegment->method('getId')
            ->willReturn($id); // test $isNew parameter is properly passed
        $companySegment->method('getAlias')
            ->willReturn($alias);
        $companySegment->expects($this->once())
            ->method('setAlias')
            ->with($alias);

        $companySegmentRepository = $this->createMock(CompanySegmentRepository::class);
        $companySegmentRepository->expects($this->once())
            ->method('getSegments')
            ->with(null, $alias, $id)
            ->willReturn([]);

        $model->expects($this->once())
            ->method('setTimestamps')
            ->with($companySegment, true, true);
        $model->method('cleanAlias')
            ->willReturn($alias);
        $model->method('getRepository')
            ->willReturn($companySegmentRepository);
        $model->expects($this->exactly(2))
            ->method('dispatchEvent')
            ->willReturnMap([
                ['pre_save', $companySegment, true, null, null],
                ['post_save', $companySegment, true, null, null],
            ]);

        $model->saveEntity($companySegment);
    }

    public function testAliasIsNotUnique(): void
    {
        $id    = 12345745745;
        $alias = 'alias';
        $model = $this->getMockBuilder(CompanySegmentModel::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->onlyMethods(['getRepository', 'setTimestamps', 'cleanAlias', 'dispatchEvent'])
            ->getMock();

        $companySegment = $this->createMock(CompanySegment::class);
        $companySegment->method('getId')
            ->willReturn($id); // test $isNew parameter is properly passed
        $companySegment->method('getAlias')
            ->willReturn($alias);
        $companySegment->expects($this->once())
            ->method('setAlias')
            ->with($alias.'1');

        $companySegmentRepository = $this->createMock(CompanySegmentRepository::class);
        $companySegmentRepository->expects($this->exactly(2))
            ->method('getSegments')
            ->willReturnMap([
                [null, $alias, $id, [['id' => 1, 'name' => 'the name', 'alias' => 'the alias']]],
                [null, $alias.'1', $id, []],
            ]);

        $model->expects($this->once())
            ->method('setTimestamps')
            ->with($companySegment, false, true);
        $model->method('cleanAlias')
            ->willReturn($alias);
        $model->method('getRepository')
            ->willReturn($companySegmentRepository);
        $model->expects($this->exactly(2))
            ->method('dispatchEvent')
            ->willReturnMap([
                ['pre_save', $companySegment, false, null, null],
                ['post_save', $companySegment, false, null, null],
            ]);

        $model->saveEntity($companySegment);
    }

    public function testEventsRequireCompanySegments(): void
    {
        $model = $this->getMockBuilder(CompanySegmentModelStub::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->onlyMethods([])
            ->getMock();

        $this->expectException(MethodNotAllowedHttpException::class);
        $this->expectExceptionMessage('Entity must be of class CompanySegment()');

        $model->testDispatchEvent('any', $this->createMock(FormEntity::class));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideActionAndEventClass')]
    public function testDoesNotHaveAnEvent(string $action, string $eventClass): void
    {
        $model = $this->getMockBuilder(CompanySegmentModelStub::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->onlyMethods([])
            ->getMock();

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->expects($this->once())
            ->method('hasListeners')
            ->with($eventClass)
            ->willReturn(false);

        $model->setDispatcher($dispatcher);
        $result = $model->testDispatchEvent($action, $this->createMock(CompanySegment::class));
        $this->assertNotInstanceOf(\Symfony\Contracts\EventDispatcher\Event::class, $result);
    }

    public static function provideActionAndEventClass(): \Generator
    {
        yield 'pre save' => [
            'pre_save',
            CompanySegmentPreSave::class,
        ];

        yield 'post save' => [
            'post_save',
            CompanySegmentPostSave::class,
        ];

        yield 'pre delete' => [
            'pre_delete',
            CompanySegmentPreDelete::class,
        ];

        yield 'post delete' => [
            'post_delete',
            CompanySegmentPostDelete::class,
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideExistingEvents')]
    public function testEventsCallsEventFromAction(string $action, string $eventClass, ?bool $isNew, ?bool $expectedIsNew): void
    {
        $model = $this->getMockBuilder(CompanySegmentModelStub::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->onlyMethods([])
            ->getMock();

        $companySegment = $this->createStub(CompanySegment::class);

        if (null !== $isNew) {
            $providedEvent = new $eventClass($companySegment, $isNew);
            $expectedEvent = new $eventClass($companySegment, $expectedIsNew);
        } else {
            $providedEvent = new $eventClass($companySegment);
            $expectedEvent = new $eventClass($companySegment);
            $isNew         = false; // To prevent type error. The class is anyway tested.
        }

        $this->assertInstanceOf(CompanySegmentEvent::class, $providedEvent);
        $this->assertInstanceOf(CompanySegmentEvent::class, $expectedEvent);

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($providedEvent)
            ->willReturnArgument(0);
        $dispatcher->expects($this->once())
            ->method('hasListeners')
            ->with($eventClass)
            ->willReturn(true);

        $model->setDispatcher($dispatcher);
        $returnedEvent = $model->testDispatchEvent($action, $companySegment, $isNew, null);
        $this->assertEquals($expectedEvent, $returnedEvent);
        $this->assertSame($companySegment, $providedEvent->getCompanySegment());
    }

    public static function provideExistingEvents(): \Generator
    {
        yield 'pre_save_is_new' => ['pre_save', CompanySegmentPreSave::class, true, true];
        yield 'pre_save_not_new' => ['pre_save', CompanySegmentPreSave::class, false, false];
        yield 'post_save_is_new' => ['post_save', CompanySegmentPostSave::class, true, true];
        yield 'post_save_not_new' => ['post_save', CompanySegmentPostSave::class, false, false];
        yield 'pre_delete' => ['pre_delete', CompanySegmentPreDelete::class, null, null];
        yield 'post_delete' => ['post_delete', CompanySegmentPostDelete::class, null, null];
    }

    public function testEventsCallsEventNotProvidedAndClassDoesNotExist(): void
    {
        $action = 'whatever';
        $model  = $this->getMockBuilder(CompanySegmentModelStub::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->onlyMethods([])
            ->getMock();

        $companySegment = $this->createStub(CompanySegment::class);

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->expects($this->never())
            ->method('dispatch');
        $dispatcher->expects($this->never())
            ->method('hasListeners');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Either the Event or proper action should be provided.');

        $model->setDispatcher($dispatcher);
        $model->testDispatchEvent($action, $companySegment, true, null);
        self::fail('After exception.');
    }

    public function testEventsCallsWithProvidedEvent(): void
    {
        $action = 'whatever';
        $model  = $this->getMockBuilder(CompanySegmentModelStub::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->onlyMethods([])
            ->getMock();

        $companySegment = $this->createStub(CompanySegment::class);
        $providedEvent  = $this->createStub(CompanySegmentEvent::class);

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($providedEvent)
            ->willReturnArgument(0);
        $dispatcher->expects($this->once())
            ->method('hasListeners')
            ->with($providedEvent::class)
            ->willReturn(true);

        $model->setDispatcher($dispatcher);
        $returnedEvent = $model->testDispatchEvent($action, $companySegment, true, $providedEvent);
        $this->assertSame($providedEvent, $returnedEvent);
    }
}
