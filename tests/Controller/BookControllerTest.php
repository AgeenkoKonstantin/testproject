<?php

namespace App\Tests\Controller;

use App\Entity\Book;
use App\Entity\BookCategory;
use App\Entity\BookFormat;
use App\Entity\BookToBookFormat;
use App\Tests\AbstractControllerTest;
use Doctrine\Common\Collections\ArrayCollection;

class BookControllerTest extends AbstractControllerTest
{
    public function testBooksByCategory(): void
    {
        $categoryId = $this->createCategory();

        $this->client->request('GET', '/api/v1/category/'.$categoryId.'/books');
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertResponseIsSuccessful();
        $this->assertJsonDocumentMatchesSchema($responseContent, [
            'type' => 'object',
            'required' => ['items'],
            'properties' => [
                'items' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'required' => ['id', 'title', 'slug', 'image', 'authors', 'meap', 'publicationDate'],
                        'properties' => [
                            'title' => ['type' => 'string'],
                            'slug' => ['type' => 'string'],
                            'id' => ['type' => 'integer'],
                            'publicationDate' => ['type' => 'integer'],
                            'image' => ['type' => 'string'],
                            'meap' => ['type' => 'boolean'],
                            'authors' => [
                                'type' => 'array',
                                'items' => ['type' => 'string'],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    public function testBookById(): void
    {
        $bookId = $this->createBook();

        $this->client->request('GET', '/api/v1/book/'.$bookId);
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertResponseIsSuccessful();
        $this->assertJsonDocumentMatchesSchema($responseContent, [
            'type' => 'object',
            'required' => [
                'id', 'title', 'slug', 'image', 'authors', 'meap', 'publicationDate', 'rating', 'reviews',
                'categories', 'formats',
            ],
            'properties' => [
                'title' => ['type' => 'string'],
                'slug' => ['type' => 'string'],
                'id' => ['type' => 'integer'],
                'publicationDate' => ['type' => 'integer'],
                'image' => ['type' => 'string'],
                'meap' => ['type' => 'boolean'],
                'authors' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                ],
                'rating' => ['type' => 'number'],
                'reviews' => ['type' => 'integer'],
                'categories' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'required' => ['id', 'title', 'slug'],
                        'properties' => [
                            'title' => ['type' => 'string'],
                            'slug' => ['type' => 'string'],
                            'id' => ['type' => 'integer'],
                        ],
                    ],
                ],
            ],
        ]);
    }

    private function createCategory(): int
    {
        $bookCategory = (new BookCategory())->setTitle('Devices')->setSlug('devices');
        $this->entityManager->persist($bookCategory);

        $this->entityManager->persist((new Book())
            ->setTitle('Test book')
            ->setImage('http://localhost.png')
            ->setIsbn('1231231')
            ->setDescription('test description')
            ->setMeap(true)
            ->setPublicationDate(new \DateTimeImmutable())
            ->setAuthors(['Tester'])
            ->setCategories(new ArrayCollection([$bookCategory]))
            ->setSlug('test-book'));

        $this->entityManager->flush();

        return $bookCategory->getId();
    }

    private function createBook(): int
    {
        $bookCategory = (new BookCategory())->setTitle('Devices')->setSlug('devices');
        $this->entityManager->persist($bookCategory);

        $format = (new BookFormat())->setTitle('format')->setDescription('description format')
            ->setComment(null);

        $this->entityManager->persist($format);

        $book = (new Book())
            ->setTitle('Test book')
            ->setImage('http://localhost.png')
            ->setMeap(true)
            ->setIsbn('123321')
            ->setDescription('test')
            ->setPublicationDate(new \DateTimeImmutable())
            ->setAuthors(['Tester'])
            ->setCategories(new ArrayCollection([$bookCategory]))
            ->setSlug('test-book');

        $this->entityManager->persist($book);

        $join = (new BookToBookFormat())->setPrice(123.55)
            ->setFormat($format)
            ->setDiscountPercent(5)
            ->setBook($book);

        $this->entityManager->persist($join);

        $this->entityManager->flush();

        return $book->getId();
    }
}
