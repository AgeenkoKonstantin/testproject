<?php

namespace App\Tests\Service;

use App\Entity\Review;
use App\Model\Review as ReviewModel;
use App\Model\ReviewPage;
use App\Repository\ReviewRepository;
use App\Service\RatingService;
use App\Service\ReviewService;
use App\Tests\AbstractTestCase;

class ReviewServiceTest extends AbstractTestCase
{
    private ReviewRepository $reviewRepository;
    private RatingService $ratingService;

    private const BOOK_ID = 1;

    private const PER_PAGE = 5;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reviewRepository = $this->createMock(ReviewRepository::class);
        $this->ratingService = $this->createMock(RatingService::class);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testGetReviewPageByBookIdInvalidPage(int $page, int $offset): void
    {
        $this->ratingService->expects($this->once())
            ->method('calcReviewServiceForBook')
            ->with(self::BOOK_ID, 0)
            ->willReturn(0.0);
        $this->reviewRepository->expects($this->once())
            ->method('getPageByBookId')
            ->with(self::BOOK_ID, $offset, self::PER_PAGE)
            ->willReturn(new \ArrayIterator());

        $reviewService = new ReviewService($this->reviewRepository, $this->ratingService);
        $expected = (new ReviewPage())
            ->setTotal(0)
            ->setRating(0)
            ->setPages(0)
            ->setPage($page)
            ->setPerPage(self::PER_PAGE)
            ->setItems([]);
        $this->assertEquals($expected, $reviewService->getReviewPageByBookId(self::BOOK_ID, $page));
    }

    public function testGetReviewPageByBookId(): void
    {
        $this->ratingService->expects($this->once())
            ->method('calcReviewServiceForBook')
            ->with(self::BOOK_ID, 1)
            ->willReturn(4.0);

        $entity = (new Review())
            ->setAuthor('tester')
            ->setContent('test content')
            ->setCreatedAt(new \DateTimeImmutable('2023-09-09'))
            ->setRating(4);
        $this->setEntityId($entity, 1);

        $this->reviewRepository->expects($this->once())
            ->method('getPageByBookId')
            ->with(self::BOOK_ID, 0, self::PER_PAGE)
            ->willReturn(new \ArrayIterator([$entity]));

        $reviewService = new ReviewService($this->reviewRepository, $this->ratingService);
        $expected = (new ReviewPage())
            ->setTotal(1)
            ->setRating(4)
            ->setPages(1)
            ->setPage(1)
            ->setPerPage(self::PER_PAGE)
            ->setItems([(new ReviewModel())
                ->setId(1)
                ->setRating(4)
                ->setContent('test content')
                ->setAuthor('tester')
                ->setCreatedAt(1694217600)]);
        $this->assertEquals($expected, $reviewService->getReviewPageByBookId(self::BOOK_ID, 1));
    }

    public static function dataProvider(): array
    {
        return [
            [0, 0],
            [-1, 0],
            [-20, 0],
        ];
    }
}
