<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final class CreateProductDTO
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 255)]
    public string $name;

    public ?string $description = null;

    #[Assert\NotBlank]
    #[Assert\Positive]
    public float $price;

    public ?string $category = null;

    #[Assert\PositiveOrZero]
    public ?int $stock = 0;
}
