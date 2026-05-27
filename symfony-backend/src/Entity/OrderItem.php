<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\OrderItemRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: OrderItemRepository::class)]
#[ORM\Table(name: 'order_items')]
class OrderItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['order:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Order::class, inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Order $order;

    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'orderItems')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['order:read'])]
    private Product $product;

    #[ORM\Column(type: Types::INTEGER)]
    #[Assert\Positive(message: 'Quantity must be at least 1.')]
    #[Groups(['order:read', 'order:write'])]
    private int $quantity;

    /**
     * Unit price captured at time of order — decoupled from product price changes.
     */
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Groups(['order:read'])]
    private string $unitPrice;

    public function getLineTotal(): string
    {
        return bcmul($this->unitPrice, (string) $this->quantity, 2);
    }

    public function getId(): ?int          { return $this->id; }
    public function getOrder(): Order      { return $this->order; }
    public function setOrder(Order $order): static { $this->order = $order; return $this; }
    public function getProduct(): Product  { return $this->product; }
    public function setProduct(Product $product): static { $this->product = $product; return $this; }
    public function getQuantity(): int     { return $this->quantity; }
    public function setQuantity(int $qty): static { $this->quantity = $qty; return $this; }
    public function getUnitPrice(): string { return $this->unitPrice; }
    public function setUnitPrice(string $price): static { $this->unitPrice = $price; return $this; }
}
