<?php

namespace App\Service;

use App\Entity\Book;
use App\Exception\BookCategoryNotFoundException;
use App\Model\BookListItem;
use App\Model\BookListResponse;
use App\Repository\BookCategoryRepository;
use App\Repository\BookRepository;

class BookService
{
    public function __construct(private BookRepository $bookRepository, private BookCategoryRepository $bookCategoryRepository)
    {
    }

    public function getBooksByCategory(int $categoryId): BookListResponse
    {
        $category = $this->bookCategoryRepository->find($categoryId);
        if (null === $category) {
            throw new BookCategoryNotFoundException();
        }

        return new BookListResponse(array_map(
            [$this, 'map'],
            $this->bookRepository->findBooksByCategoryId($categoryId)
        ));
    }

    private function map(Book $book): BookListItem
    {
        return (new BookListItem())
            ->setId($book->getId())
            ->setSlug($book->getSlug())
            ->setTitle($book->getTitle())
            ->setAuthors($book->getAuthors())
            ->setMeap($book->isMeap())
            ->setImage($book->getImage())
            ->setPublicationDate($book->getPublicationDate()->getTimestamp());
    }
}
