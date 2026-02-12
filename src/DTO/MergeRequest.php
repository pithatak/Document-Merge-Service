<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class MergeRequest
{
    #[Assert\NotNull]
    #[Assert\Count(
        min: 2,
        max: 10,
        minMessage: 'Minimum {{ limit }} files required.',
        maxMessage: 'Maximum {{ limit }} files allowed.'
    )]
    #[Assert\All([
        new Assert\File(
            maxSize: '15M',
            mimeTypes: [
                'application/pdf',
                'image/jpeg',
                'image/png'
            ],
            mimeTypesMessage: 'Unsupported file type.'
        )
    ])]
    public array $files = [];

    public function __construct(array $files = [])
    {
        $this->files = $files;
    }
}
