<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\CreateOrderDTO;
use App\Service\OrderService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/orders', name: 'api_orders_')]
class OrderController extends AbstractController
{
    public function __construct(
        private readonly OrderService        $orderService,
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface  $validator,
    ) {}

    /**
     * GET /api/orders
     * Returns all orders, optionally filtered by status or customer email.
     */
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $status = $request->query->get('status');
        $email  = $request->query->get('email');

        $orders = $this->orderService->findAll($status, $email);

        return $this->json(
            $orders,
            Response::HTTP_OK,
            [],
            ['groups' => ['order:read']],
        );
    }

    /**
     * GET /api/orders/{id}
     */
    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): JsonResponse
    {
        $order = $this->orderService->findOrFail($id);

        return $this->json(
            $order,
            Response::HTTP_OK,
            [],
            ['groups' => ['order:read']],
        );
    }

    /**
     * POST /api/orders
     * Creates a new order and reserves stock.
     */
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        /** @var CreateOrderDTO $dto */
        $dto = $this->serializer->deserialize(
            $request->getContent(),
            CreateOrderDTO::class,
            'json',
        );

        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            return $this->json(
                ['errors' => $this->formatValidationErrors($errors)],
                Response::HTTP_UNPROCESSABLE_ENTITY,
            );
        }

        $order = $this->orderService->create($dto);

        return $this->json(
            $order,
            Response::HTTP_CREATED,
            [],
            ['groups' => ['order:read']],
        );
    }

    /**
     * PATCH /api/orders/{id}/status
     * Updates only the status field.
     */
    #[Route('/{id}/status', name: 'update_status', methods: ['PATCH'], requirements: ['id' => '\d+'])]
    public function updateStatus(int $id, Request $request): JsonResponse
    {
        $body   = json_decode($request->getContent(), true);
        $status = $body['status'] ?? null;

        if (!$status) {
            return $this->json(['error' => 'status field is required.'], Response::HTTP_BAD_REQUEST);
        }

        $order = $this->orderService->updateStatus($id, $status);

        return $this->json(
            $order,
            Response::HTTP_OK,
            [],
            ['groups' => ['order:read']],
        );
    }

    private function formatValidationErrors(iterable $errors): array
    {
        $result = [];
        foreach ($errors as $error) {
            $result[] = [
                'field'   => $error->getPropertyPath(),
                'message' => $error->getMessage(),
            ];
        }
        return $result;
    }
}
