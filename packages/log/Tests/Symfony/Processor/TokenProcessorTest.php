<?php

namespace Draw\Component\Log\Tests\Symfony\Processor;

use Draw\Bundle\UserBundle\Entity\SecurityUserInterface;
use Draw\Bundle\UserBundle\Entity\SecurityUserTrait;
use Draw\Component\Log\Symfony\Processor\TokenProcessor;
use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\NullToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class TokenProcessorTest extends TestCase
{
    private TokenProcessor $service;

    private TokenStorageInterface&MockObject $tokenStorage;

    private string $key;

    protected function setUp(): void
    {
        $this->service = new TokenProcessor(
            $this->tokenStorage = $this->createMock(TokenStorageInterface::class),
            $this->key = uniqid()
        );
    }

    public function testInvokeNoToken(): void
    {
        static::assertSame(
            [$this->key => null],
            $this->service->__invoke(
                new LogRecord(
                    new \DateTimeImmutable(),
                    'test',
                    Level::Info,
                    'message',
                )
            )->toArray()['extra']
        );
    }

    public function testInvokeNotIdentifiedToken(): void
    {
        $this->tokenStorage->expects(static::once())
            ->method('getToken')
            ->willReturn(
                new NullToken()
            );

        static::assertSame(
            [
                $this->key => [
                    'authenticated' => false,
                    'roles' => [],
                    'user_identifier' => '',
                ],
            ],
            $this->service->__invoke(
                new LogRecord(
                    new \DateTimeImmutable(),
                    'test',
                    Level::Info,
                    'message',
                )
            )->toArray()['extra']
        );
    }

    public function testInvokeIdentifiedToken(): void
    {
        $user = new class implements SecurityUserInterface {
            use SecurityUserTrait;

            private int $id;
            private string $userIdentifier;

            public function getId(): int
            {
                return $this->id;
            }

            public function setId(int $id): self
            {
                $this->id = $id;

                return $this;
            }

            public function getUserIdentifier(): string
            {
                return $this->userIdentifier;
            }

            public function setUserIdentifier(string $userIdentifier): self
            {
                $this->userIdentifier = $userIdentifier;

                return $this;
            }
        };

        $this->tokenStorage->expects(static::once())
            ->method('getToken')
            ->willReturn(
                new UsernamePasswordToken(
                    $user
                        ->setId(random_int(\PHP_INT_MIN, \PHP_INT_MAX))
                        ->setUserIdentifier(uniqid()),
                    uniqid(),
                    $roles = [uniqid()]
                )
            );

        static::assertSame(
            [
                $this->key => [
                    'authenticated' => true,
                    'roles' => $roles,
                    'user_identifier' => $user->getUserIdentifier(),
                    'user_id' => (string) $user->getId(),
                ],
            ],
            $this->service->__invoke(
                new LogRecord(
                    new \DateTimeImmutable(),
                    'test',
                    Level::Info,
                    'message',
                )
            )->toArray()['extra']
        );
    }
}
