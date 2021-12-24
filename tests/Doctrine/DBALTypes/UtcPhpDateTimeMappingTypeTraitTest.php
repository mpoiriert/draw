<?php

namespace App\Tests\Doctrine\DBALTypes;

use App\Entity\User;
use App\Tests\TestCase;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @covers \Draw\Bundle\SonataExtraBundle\Doctrine\DBALTypes\UtcPhpDateTimeMappingTypeTraitTest
 */
class UtcPhpDateTimeMappingTypeTraitTest extends TestCase
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var User
     */
    private static $entity;

    public function setUp(): void
    {
        $this->entityManager = static::getService(EntityManagerInterface::class);
    }

    public function tearDown(): void
    {
        date_default_timezone_set('UTC');
        if (self::$entity) {
            $this->entityManager->remove(self::$entity);
            $this->entityManager->flush();
        }
    }

    public function testConvert(): void
    {
        $format = 'Y-m-d H:i:s';
        date_default_timezone_set('America/New_York');
        $date = new DateTimeImmutable('2021-07-07 10:00:00');

        $entity = self::$entity = new User();
        $entity->setEmail('test-timezone@example.com');
        $entity->setPlainPassword('test');
        $entity->setDateOfBirth($date);
        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $this->assertSame('2021-07-07 10:00:00', $entity->getDateOfBirth()->format($format));
        $this->entityManager->refresh($entity);
        $this->assertSame('2021-07-07 10:00:00', $entity->getDateOfBirth()->format($format));
        $this->assertSame('America/New_York', $entity->getDateOfBirth()->getTimezone()->getName());

        $statement = $this->entityManager
            ->getConnection()
            ->executeQuery("SELECT * FROM draw_acme__user WHERE email='test-timezone@example.com'");

        $this->assertSame(
            '2021-07-07 14:00:00',
            $statement->fetchAssociative()['date_of_birth'],
            'All dates should be stored in UTC timezone'
        );

        date_default_timezone_set('Europe/London');
        $this->entityManager->refresh($entity);
        $this->assertSame('2021-07-07 15:00:00', $entity->getDateOfBirth()->format($format));
        $this->assertSame('Europe/London', $entity->getDateOfBirth()->getTimezone()->getName());
    }
}
