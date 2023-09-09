<?php

namespace App\Tests\Mapper;

use App\Entity\Book;
use App\Mapper\BookMapper;
use App\Model\BookDetails;
use App\Tests\AbstractTestCase;

class BookMapperTest extends AbstractTestCase
{
    public function testMap(): void
    {
        $book = (new Book())
            ->setTitle('title')
            ->setSlug('slug')
            ->setImage('123')
            ->setAuthors(['tester'])
            ->setMeap(true)
            ->setPublicationDate(new \DateTimeImmutable('2023-09-09'));
        $this->setEntityId($book, 1);

        $expected = (new BookDetails())
            ->setId(1)
            ->setTitle('title')
            ->setSlug('slug')
            ->setImage('123')
            ->setAuthors(['tester'])
            ->setMeap(true)
            ->setPublicationDate(1694217600);

        $this->assertEquals($expected, BookMapper::map($book, new BookDetails()));
    }
}
