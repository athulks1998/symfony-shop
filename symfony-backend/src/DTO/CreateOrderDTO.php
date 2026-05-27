<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final class CreateOrderDTO
{
    #[Assert\NotBlank]
    #[Assert\Email]
    public string $customerEmail;

    public ?string $shippingAddress = null;

    /**
     * @var OrderItemDTO[]
     */
    #[Assert\NotBlank]
    #[Assert\Count(min: 1, minMessage: 'An order must contain at least one item.')]
    #[Assert\Valid]
    public array $items = [];
}
