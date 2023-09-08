<?php

namespace App\Tests\Service;

use App\Entity\Subscriber;
use App\Exception\SubscriberAlreadyExistsException;
use App\Model\SubscriberRequest;
use App\Repository\SubscriberRepository;
use App\Service\SubscriberService;
use App\Tests\AbstractTestCase;
use Doctrine\ORM\EntityManagerInterface;

class SubscriberServiceTest extends AbstractTestCase
{
    private SubscriberRepository $subscriberRepository;
    private EntityManagerInterface $entityManager;

    private const EMAIL = 'test@test.com';

    protected function setUp(): void
    {
        parent::setUp();
        $this->subscriberRepository = $this->createMock(SubscriberRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
    }

    public function testSubscribeAlreadyExists()
    {
        $this->expectException(SubscriberAlreadyExistsException::class);
        $this->subscriberRepository->expects($this->once())
            ->method('existsByEmail')
            ->with(self::EMAIL)
            ->willReturn(true);

        $request = new SubscriberRequest();
        $request->setEmail(self::EMAIL);
        (new SubscriberService($this->subscriberRepository, $this->entityManager))->subscribe($request);
    }

    public function testSubscribe()
    {

        $this->subscriberRepository->expects($this->once())
            ->method('existsByEmail')
            ->with(self::EMAIL)
            ->willReturn(false);

        $expectedSubscriber = new Subscriber();
        $expectedSubscriber->setEmail(self::EMAIL);

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($expectedSubscriber);

        $this->entityManager->expects($this->once())
            ->method('flush');

        $request = new SubscriberRequest();
        $request->setEmail(self::EMAIL);
        (new SubscriberService($this->subscriberRepository, $this->entityManager))->subscribe($request);
    }
}
