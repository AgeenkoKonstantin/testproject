<?php

namespace App\Tests\Repository;

use App\Entity\BookCategory;
use App\Repository\BookCategoryRepository;
use App\Tests\AbstractRepositoryTest;

class BookCategoryRepositoryTest extends AbstractRepositoryTest
{
    private BookCategoryRepository $bookCategoryRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bookCategoryRepository = $this->getRepositoryForEntity(BookCategory::class);
    }

    public function testFindAllSortedByTitle()
    {
        $android = (new BookCategory())->setTitle('Android')->setSlug('android');
        $devices = (new BookCategory())->setTitle('Devices')->setSlug('devices');
        $computer = (new BookCategory())->setTitle('Computer')->setSlug('computer');

        foreach ([$android, $devices, $computer] as $category) {
            $this->entityManager->persist($category);
        }
        $this->entityManager->flush();

        $titles = array_map(
            fn (BookCategory $bookCategory) => $bookCategory->getTitle(),
            $this->bookCategoryRepository->findAllSortedByTitle()
        );

        $this->assertEquals(['Android', 'Computer', 'Devices'], $titles);
    }
}
