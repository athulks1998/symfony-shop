<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: 'orders')]
#[ORM\HasLifecycleCallbacks]
class Order
{
    public const STATUS_PENDING    = 'pending';
    public const STATUS_CONFIRMED  = 'confirmed';
    public const STATUS_SHIPPED    = 'shipped';
    public const STATUS_DELIVERED  = 'delivered';
    public const STATUS_CANCELLED  = 'cancelled';

    public const VALID_STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_CONFIRMED,
        self::STATUS_SHIPPED,
        self::STATUS_DELIVERED,
        self::STATUS_CANCELLED,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['order:read'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 100, unique: true)]
    #[Groups(['order:read'])]
    private string $reference;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\NotBlank]
    #[Assert\Email]
    #[Groups(['order:read', 'order:write'])]
    private string $customerEmail;

    #[ORM\Column(type: Types::STRING, length: 50)]
    #[Assert\Choice(choices: Order::VALID_STATUSES)]
    #[Groups(['order:read'])]
    private string $status = self::STATUS_PENDING;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2, options: ['default' => '0.00'])]
    #[Groups(['order:read'])]
    private string $totalAmount = '0.00';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['order:read', 'order:write'])]
    private ?string $shippingAddress = null;

    #[ORM\OneToMany(mappedBy: 'order', targetEntity: OrderItem::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['order:read'])]
    private Collection $items;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups(['order:read'])]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['order:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->items      = new ArrayCollection();
        $this->createdAt  = new \DateTimeImmutable();
        $this->reference  = 'ORD-' . strtoupper(substr(uniqid('', true), -8));
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function recalculateTotal(): void
    {
        $total = '0.00';
        foreach ($this->items as $item) {
            $total = bcadd($total, $item->getLineTotal(), 2);
        }
        $this->totalAmount = $total;
    }

    public function getId(): ?int            { return $this->id; }
    public function getReference(): string   { return $this->reference; }

    public function getCustomerEmail(): string { return $this->customerEmail; }
    public function setCustomerEmail(string $email): static
    {
        $this->customerEmail = $email;
        return $this;
    }

    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getTotalAmount(): string { return $this->totalAmount; }

    public function getShippingAddress(): ?string { return $this->shippingAddress; }
    public function setShippingAddress(?string $address): static
    {
        $this->shippingAddress = $address;
        return $this;
    }

    public function getItems(): Collection { return $this->items; }

    public function addItem(OrderItem $item): static
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setOrder($this);
        }
        $this->recalculateTotal();
        return $this;
    }

    public function removeItem(OrderItem $item): static
    {
        $this->items->removeElement($item);
        $this->recalculateTotal();
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable    { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable   { return $this->updatedAt; }
}
