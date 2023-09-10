<?php

namespace App\Service;

use App\Entity\Review;
use App\Model\Review as ReviewModel;
use App\Model\ReviewPage;
use App\Repository\ReviewRepository;

class ReviewService
{
    // if change => change in test too
    private const PAGE_LIMIT = 5;

    public function __construct(private ReviewRepository $reviewRepository, private RatingService $ratingService)
    {
    }

    public function getReviewPageByBookId(int $id, int $page): ReviewPage
    {
        $offset = max($page - 1, 0) * self::PAGE_LIMIT;
        $paginator = $this->reviewRepository->getPageByBookId($id, $offset, self::PAGE_LIMIT);
        $items = [];

        foreach ($paginator as $item) {
            $items[] = $this->map($item);
        }

        $rating = $this->ratingService->calcReviewRatingForBook($id);
        return (new ReviewPage())
            ->setRating($rating->getRating())
            ->setTotal($rating->getTotal())
            ->setPage($page)
            ->setPerPage(self::PAGE_LIMIT)
            ->setPages(ceil($rating->getTotal() / self::PAGE_LIMIT))
            ->setItems($items);
    }

    private function map(Review $review): ReviewModel
    {
        return (new ReviewModel())
            ->setId($review->getId())
            ->setRating($review->getRating())
            ->setCreatedAt($review->getCreatedAt()->getTimestamp())
            ->setAuthor($review->getAuthor())
            ->setContent($review->getContent());
    }
}
