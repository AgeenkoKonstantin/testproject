<?php

namespace App\Service;

use App\Exception\UploadFileTypeInvalidException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Uid\Uuid;
use function Symfony\Component\String\b;

class UploadService
{
    private const LINK_BOOK_PATTERN = '/upload/book/%d/%s';
    public function __construct(private Filesystem $filesystem, private string $uploadDir)
    {
    }

    public function uploadBookFile(int $bookId, UploadedFile $uploadedFile): string
    {
        $extension = $uploadedFile->guessExtension();
        if (null === $extension) {
            throw new UploadFileTypeInvalidException();
        }
        $uniqueName = Uuid::v7()->toRfc4122() . '.' . $extension;
        $uploadPath = $this->getUploadPathForBook($bookId);
        $uploadedFile->move($uploadPath, $uniqueName);

        return sprintf(self::LINK_BOOK_PATTERN, $bookId, $uniqueName);
    }

    public function deleteBookFile(int $id, string $fileName): void
    {
        $this->filesystem->remove($this->getUploadPathForBook($id) . DIRECTORY_SEPARATOR . $fileName);
    }

    private function getUploadPathForBook(int $id): string
    {
        return $this->uploadDir . DIRECTORY_SEPARATOR . 'book' . DIRECTORY_SEPARATOR . $id;
    }
}
