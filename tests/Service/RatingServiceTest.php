<?php

namespace App\Tests\Service;

use App\Repository\ReviewRepository;
use App\Service\RatingService;
use App\Tests\AbstractTestCase;

class RatingServiceTest extends AbstractTestCase
{
    private ReviewRepository $reviewRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reviewRepository = $this->createMock(ReviewRepository::class);
    }

    public function provider(): array
    {
        return [
            [25, 20, 1.25],
            [0, 5, 0],
        ];
    }

    /**
     * @dataProvider provider
     */
    public function testCalcReviewServiceForBook(int $repositoryRatingSum, int $total, float $expectingRating): void
    {
        $this->reviewRepository->expects($this->once())
            ->method('getBookTotalRatingSum')
            ->with(1)
            ->willReturn($repositoryRatingSum);

        $actual = (new RatingService($this->reviewRepository))->calcReviewServiceForBook(1, $total);

        $this->assertEquals($expectingRating, $actual);
    }

    public function testCalcReviewServiceForBookZeroTotal(): void
    {
        $this->reviewRepository->expects($this->never())->method('getBookTotalRatingSum');

        $actual = (new RatingService($this->reviewRepository))->calcReviewServiceForBook(1, 0);

        $this->assertEquals(0, $actual);
    }
}
