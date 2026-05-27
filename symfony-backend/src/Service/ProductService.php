<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\CreateProductDTO;
use App\Entity\Product;
use App\Exception\ResourceNotFoundException;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;

class ProductService
{
    public function __construct(
        private readonly ProductRepository    $productRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {}

    /**
     * Returns a paginated array of active products.
     *
     * @return Product[]
     */
    public function findPaginated(int $page, int $limit, ?string $category): array
    {
        $offset = ($page - 1) * $limit;
        return $this->productRepository->findPaginated($offset, $limit, $category);
    }

    /**
     * @throws ResourceNotFoundException
     */
    public function findOrFail(int $id): Product
    {
        $product = $this->productRepository->find($id);

        if (!$product || !$product->isActive()) {
            throw new ResourceNotFoundException(
                sprintf('Product with ID %d not found.', $id)
            );
        }

        return $product;
    }

    public function create(CreateProductDTO $dto): Product
    {
        $product = new Product();
        $product->setName($dto->name)
                ->setDescription($dto->description)
                ->setPrice((string) $dto->price)
                ->setCategory($dto->category)
                ->setStock($dto->stock ?? 0);

        $this->entityManager->persist($product);
        $this->entityManager->flush();

        return $product;
    }

    /**
     * @throws ResourceNotFoundException
     */
    public function update(int $id, CreateProductDTO $dto): Product
    {
        $product = $this->findOrFail($id);

        $product->setName($dto->name)
                ->setDescription($dto->description)
                ->setPrice((string) $dto->price)
                ->setCategory($dto->category)
                ->setStock($dto->stock ?? $product->getStock());

        $this->entityManager->flush();

        return $product;
    }

    /**
     * Soft-delete: marks the product inactive instead of removing the row.
     *
     * @throws ResourceNotFoundException
     */
    public function softDelete(int $id): void
    {
        $product = $this->findOrFail($id);
        $product->setActive(false);
        $this->entityManager->flush();
    }
}
