<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\CreateOrderDTO;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Exception\InsufficientStockException;
use App\Exception\InvalidStatusTransitionException;
use App\Exception\ResourceNotFoundException;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;

class OrderService
{
    /**
     * Allowed status transitions — key: current status, value: allowed next statuses.
     */
    private const TRANSITIONS = [
        Order::STATUS_PENDING   => [Order::STATUS_CONFIRMED, Order::STATUS_CANCELLED],
        Order::STATUS_CONFIRMED => [Order::STATUS_SHIPPED,   Order::STATUS_CANCELLED],
        Order::STATUS_SHIPPED   => [Order::STATUS_DELIVERED],
        Order::STATUS_DELIVERED => [],
        Order::STATUS_CANCELLED => [],
    ];

    public function __construct(
        private readonly OrderRepository      $orderRepository,
        private readonly ProductService       $productService,
        private readonly EntityManagerInterface $entityManager,
    ) {}

    /**
     * @return Order[]
     */
    public function findAll(?string $status, ?string $email): array
    {
        return $this->orderRepository->findByFilters($status, $email);
    }

    /**
     * @throws ResourceNotFoundException
     */
    public function findOrFail(int $id): Order
    {
        $order = $this->orderRepository->find($id);

        if (!$order) {
            throw new ResourceNotFoundException(
                sprintf('Order with ID %d not found.', $id)
            );
        }

        return $order;
    }

    /**
     * Creates an order, validates stock availability, and persists everything
     * inside a single DB transaction.
     *
     * @throws ResourceNotFoundException
     * @throws InsufficientStockException
     */
    public function create(CreateOrderDTO $dto): Order
    {
        $this->entityManager->beginTransaction();

        try {
            $order = new Order();
            $order->setCustomerEmail($dto->customerEmail)
                  ->setShippingAddress($dto->shippingAddress);

            foreach ($dto->items as $itemDTO) {
                $product = $this->productService->findOrFail($itemDTO->productId);

                if ($product->getStock() < $itemDTO->quantity) {
                    throw new InsufficientStockException(
                        sprintf(
                            'Not enough stock for product "%s". Available: %d, requested: %d.',
                            $product->getName(),
                            $product->getStock(),
                            $itemDTO->quantity
                        )
                    );
                }

                $item = new OrderItem();
                $item->setProduct($product)
                     ->setQuantity($itemDTO->quantity)
                     ->setUnitPrice($product->getPrice());

                // Reserve stock
                $product->setStock($product->getStock() - $itemDTO->quantity);

                $order->addItem($item);
                $this->entityManager->persist($item);
            }

            $this->entityManager->persist($order);
            $this->entityManager->flush();
            $this->entityManager->commit();

        } catch (\Throwable $e) {
            $this->entityManager->rollback();
            throw $e;
        }

        return $order;
    }

    /**
     * Transitions an order to a new status following the allowed state machine.
     *
     * @throws ResourceNotFoundException
     * @throws InvalidStatusTransitionException
     */
    public function updateStatus(int $id, string $newStatus): Order
    {
        $order = $this->findOrFail($id);

        $allowed = self::TRANSITIONS[$order->getStatus()] ?? [];

        if (!in_array($newStatus, $allowed, true)) {
            throw new InvalidStatusTransitionException(
                sprintf(
                    'Cannot transition order from "%s" to "%s". Allowed transitions: [%s].',
                    $order->getStatus(),
                    $newStatus,
                    implode(', ', $allowed)
                )
            );
        }

        $order->setStatus($newStatus);
        $this->entityManager->flush();

        return $order;
    }
}
