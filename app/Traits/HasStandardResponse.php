<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

trait HasStandardResponse
{
    private float $requestStartTime;

    /**
     * Initialize request start time
     */
    protected function initRequestTime(): void
    {
        if (!isset($this->requestStartTime)) {
            $this->requestStartTime = microtime(true);
        }
    }

    /**
     * Calculate response time in milliseconds
     */
    protected function getResponseTime(): float
    {
        $this->initRequestTime();
        return round((microtime(true) - $this->requestStartTime) * 1000, 2);
    }

    /**
     * Return success response
     */
    protected function successResponse(
        string $message,
        mixed $data = null,
        int $statusCode = Response::HTTP_OK
    ): JsonResponse {
        return new JsonResponse([
            'success' => true,
            'message' => $message,
            'responseTime' => $this->getResponseTime(),
            'data' => $data,
        ], $statusCode);
    }

    /**
     * Return error response
     */
    protected function errorResponse(
        string $message,
        mixed $data = null,
        int $statusCode = Response::HTTP_OK
    ): JsonResponse {
        return new JsonResponse([
            'success' => false,
            'message' => $message,
            'responseTime' => $this->getResponseTime(),
            'data' => $data,
        ], $statusCode);
    }

    /**
     * Return paginated response
     */
    protected function paginatedResponse(
        string $message,
        $paginatedData,
        array $additionalData = []
    ): JsonResponse {
        // Check if items are already a resource collection
        $items = $paginatedData->items();
        if (is_object($items) && method_exists($items, 'collection')) {
            $items = $items->collection;
        }

        $data = array_merge([
            'items' => $items,
            'pagination' => [
                'total' => $paginatedData->total(),
                'currentPage' => $paginatedData->currentPage(),
                'lastPage' => $paginatedData->lastPage(),
                'perPage' => $paginatedData->perPage(),
                'from' => $paginatedData->firstItem(),
                'to' => $paginatedData->lastItem(),
            ],
        ], $additionalData);

        return $this->successResponse($message, $data);
    }
}

