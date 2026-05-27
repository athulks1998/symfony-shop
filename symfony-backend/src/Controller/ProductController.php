<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\CreateProductDTO;
use App\Service\ProductService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/products', name: 'api_products_')]
class ProductController extends AbstractController
{
    public function __construct(
        private readonly ProductService      $productService,
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface  $validator,
    ) {}

    /**
     * GET /api/products
     * Returns a paginated list of active products with optional category filter.
     */
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $page     = max(1, (int) $request->query->get('page', 1));
        $limit    = min(50, max(1, (int) $request->query->get('limit', 20)));
        $category = $request->query->get('category');

        $products = $this->productService->findPaginated($page, $limit, $category);

        return $this->json(
            $products,
            Response::HTTP_OK,
            [],
            ['groups' => ['product:read']],
        );
    }

    /**
     * GET /api/products/{id}
     * Returns a single product by ID.
     */
    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): JsonResponse
    {
        $product = $this->productService->findOrFail($id);

        return $this->json(
            $product,
            Response::HTTP_OK,
            [],
            ['groups' => ['product:read']],
        );
    }

    /**
     * POST /api/products
     * Creates a new product. Requires JSON body matching CreateProductDTO.
     */
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        /** @var CreateProductDTO $dto */
        $dto = $this->serializer->deserialize(
            $request->getContent(),
            CreateProductDTO::class,
            'json',
        );

        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            return $this->json(
                ['errors' => $this->formatValidationErrors($errors)],
                Response::HTTP_UNPROCESSABLE_ENTITY,
            );
        }

        $product = $this->productService->create($dto);

        return $this->json(
            $product,
            Response::HTTP_CREATED,
            [],
            ['groups' => ['product:read']],
        );
    }

    /**
     * PUT /api/products/{id}
     * Fully replaces a product. Partial updates use PATCH (not shown for brevity).
     */
    #[Route('/{id}', name: 'update', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function update(int $id, Request $request): JsonResponse
    {
        /** @var CreateProductDTO $dto */
        $dto = $this->serializer->deserialize(
            $request->getContent(),
            CreateProductDTO::class,
            'json',
        );

        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            return $this->json(
                ['errors' => $this->formatValidationErrors($errors)],
                Response::HTTP_UNPROCESSABLE_ENTITY,
            );
        }

        $product = $this->productService->update($id, $dto);

        return $this->json(
            $product,
            Response::HTTP_OK,
            [],
            ['groups' => ['product:read']],
        );
    }

    /**
     * DELETE /api/products/{id}
     * Soft-deletes a product (sets active = false).
     */
    #[Route('/{id}', name: 'delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(int $id): JsonResponse
    {
        $this->productService->softDelete($id);

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    private function formatValidationErrors(iterable $errors): array
    {
        $formatted = [];
        foreach ($errors as $error) {
            $formatted[] = [
                'field'   => $error->getPropertyPath(),
                'message' => $error->getMessage(),
            ];
        }
        return $formatted;
    }
}
