<?php

declare(strict_types=1);

namespace Mautic\LeadBundle\Tests\Entity;

use Mautic\LeadBundle\Entity\CompanySegment;
use PHPUnit\Framework\TestCase;

final class CompanySegmentTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('provideEmptyFields')]
    public function testEntitySetsPublicNameAndAliasIfNameIsSet(?string $alias, ?string $publicName): void
    {
        $name   = 'The name';
        $entity = new CompanySegment();
        $entity->setName($name);
        $entity->setAlias($alias);
        $entity->setPublicName($publicName);

        $this->assertSame($name, $entity->getPublicName());
        $this->assertSame($name, $entity->getAlias());
    }

    public static function provideEmptyFields(): \Generator
    {
        yield 'null alias, null publicName' => [null, null];
        yield 'empty string alias, null publicName' => ['', null];
        yield 'null alias, empty string publicName' => [null, ''];
        yield 'empty string alias, empty string publicName' => ['', ''];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideNullOrEmptyString')]
    public function testSettingAliasNullOrEmptyStringFetchesFromName(?string $value): void
    {
        $name   = 'The name';
        $entity = new CompanySegment();
        $entity->setName($name);
        $entity->setAlias($value);

        $this->assertSame($name, $entity->getAlias());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideNullOrEmptyString')]
    public function testSettingPublicNullOrEmptyStringFetchesFromName(?string $value): void
    {
        $name   = 'The name';
        $entity = new CompanySegment();
        $entity->setName($name);
        $entity->setPublicName($value);

        $this->assertSame($name, $entity->getPublicName());
    }

    public static function provideNullOrEmptyString(): \Generator
    {
        yield 'null value' => [null];
        yield 'empty string' => [''];
    }

    public function testCloneResetsId(): void
    {
        $entity = new CompanySegment();
        $entity->setName('Test Segment');
        // Simulate a persisted entity by using reflection to set ID
        $reflection = new \ReflectionClass($entity);
        $property   = $reflection->getProperty('id');
        $property->setValue($entity, 123);

        $clonedEntity = clone $entity;

        $this->assertNull($clonedEntity->getId());
        $this->assertSame('Test Segment', $clonedEntity->getName());
    }

    public function testCloneResetsIsPublished(): void
    {
        $entity = new CompanySegment();
        $entity->setIsPublished(true);

        $clonedEntity = clone $entity;

        $this->assertFalse($clonedEntity->isPublished());
    }

    public function testCloneResetsAlias(): void
    {
        $entity = new CompanySegment();
        $entity->setName('Test');
        $entity->setAlias('test-alias');

        $clonedEntity = clone $entity;

        // After cloning, setAlias('') is called which falls back to the name
        $this->assertSame('Test', $clonedEntity->getAlias());
        $this->assertNotSame('test-alias', $clonedEntity->getAlias());
    }

    public function testCloneResetsLastBuiltDate(): void
    {
        $entity = new CompanySegment();
        $entity->setLastBuiltDate(new \DateTime());

        $clonedEntity = clone $entity;

        $this->assertNotInstanceOf(\DateTimeInterface::class, $clonedEntity->getLastBuiltDate());
    }

    public function testCloneResetsSegmentCompanies(): void
    {
        $entity = new CompanySegment();
        // segmentCompanies should be a new empty collection after cloning

        $clonedEntity = clone $entity;

        $this->assertCount(0, $clonedEntity->getSegmentCompanies());
        $this->assertNotSame($entity->getSegmentCompanies(), $clonedEntity->getSegmentCompanies());
    }
}
