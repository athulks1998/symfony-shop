<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final class OrderItemDTO
{
    #[Assert\NotBlank]
    #[Assert\Positive]
    public int $productId;

    #[Assert\NotBlank]
    #[Assert\Positive]
    public int $quantity;
}
