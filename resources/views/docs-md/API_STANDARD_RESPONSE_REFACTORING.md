# API Standard Response Refactoring

## Overview
All API controllers have been refactored to use a consistent standard response format across the entire application.

## Standard Response Structure

All API responses now follow this structure:

```json
{
  "success": true|false,
  "message": "Descriptive message",
  "responseTime": 123.45,  // in milliseconds
  "data": {
    // Response data here
  }
}
```

### Success Response Example
```json
{
  "success": true,
  "message": "Sale completed successfully.",
  "responseTime": 245.67,
  "data": {
    "sale": {
      "id": "uuid",
      "saleNumber": "SALE-2024-001",
      ...
    }
  }
}
```

### Error Response Example
```json
{
  "success": false,
  "message": "Insufficient stock for Product A. Available: 5",
  "responseTime": 123.45,
  "data": {
    "productName": "Product A",
    "requestedQuantity": 10,
    "availableStock": 5
  }
}
```

### Paginated Response Example
```json
{
  "success": true,
  "message": "Products retrieved successfully.",
  "responseTime": 189.23,
  "data": {
    "items": [...],
    "pagination": {
      "total": 100,
      "currentPage": 1,
      "lastPage": 10,
      "perPage": 10,
      "from": 1,
      "to": 10
    }
  }
}
```

## Implementation

### HasStandardResponse Trait

Created a new trait at `/app/Traits/HasStandardResponse.php` that provides:

1. **`initRequestTime()`** - Initializes request start time for response time calculation
2. **`getResponseTime()`** - Calculates response time in milliseconds
3. **`successResponse($message, $data, $statusCode)`** - Returns success response
4. **`errorResponse($message, $data, $statusCode)`** - Returns error response
5. **`paginatedResponse($message, $paginatedData, $additionalData)`** - Returns paginated response

### Usage in Controllers

```php
use App\Traits\HasStandardResponse;

class MyController extends Controller
{
    use HasStandardResponse;

    public function index(Request $request): JsonResponse
    {
        $this->initRequestTime(); // Initialize at start of method
        
        // ... your logic ...
        
        // Return success response
        return $this->successResponse(
            'Data retrieved successfully.',
            ['items' => $items]
        );
    }
    
    public function store(Request $request): JsonResponse
    {
        $this->initRequestTime();
        
        try {
            // ... your logic ...
            
            return $this->successResponse(
                'Resource created successfully.',
                ['resource' => $resource],
                Response::HTTP_CREATED
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to create resource.',
                ['error' => $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
    
    public function list(Request $request): JsonResponse
    {
        $this->initRequestTime();
        
        $items = Model::paginate(15);
        
        $transformed = $items->setCollection(
            collect(ResourceCollection::collection($items->getCollection()))
        );
        
        return $this->paginatedResponse(
            'Items retrieved successfully.',
            $transformed
        );
    }
}
```

## Refactored Controllers

The following controllers have been refactored to use the standard response format:

### ✅ Completed Controllers

1. **AdController** - Advertising system endpoints
2. **POSController** - Point of Sale operations
3. **ExpenseController** - Expense management
4. **ChatController** - Shop-to-shop messaging
5. **ProductController** - Product management
6. **CategoryController** - Category management
7. **PurchaseOrderController** - Purchase order management
8. **ShopController** - Shop management
9. **ShopSettingsController** - Shop settings
10. **ShopMemberController** - Shop member management
11. **SubscriptionController** - Subscription management
12. **SavingsController** - Savings management
13. **ReportsController** - Report generation

## Key Changes

### Before Refactoring
```php
return new JsonResponse([
    'success' => true,
    'code' => Response::HTTP_OK,
    'data' => $data
], Response::HTTP_OK);
```

### After Refactoring
```php
return $this->successResponse(
    'Operation completed successfully.',
    $data
);
```

## Benefits

1. **Consistency** - All API responses follow the same structure
2. **Performance Monitoring** - `responseTime` included in all responses
3. **Better Error Handling** - Standardized error response format
4. **Kotlin/Android Friendly** - All keys use camelCase for Kotlin compatibility
5. **Maintainability** - Centralized response logic in a single trait
6. **Type Safety** - Clear method signatures for success/error responses
7. **Developer Experience** - Easier to work with consistent API responses

## Response Time Calculation

The response time is calculated in milliseconds from the moment `initRequestTime()` is called until the response is generated. This helps in:

- Monitoring API performance
- Identifying slow endpoints
- Debugging performance issues
- Optimizing critical paths

## Migration Notes

### For Developers

- Always call `$this->initRequestTime()` at the beginning of controller methods
- Use `successResponse()` for successful operations
- Use `errorResponse()` for failures
- Use `paginatedResponse()` for paginated data
- All response messages should be descriptive and user-friendly
- Always include relevant data in error responses for debugging

### For Frontend/Mobile Developers

- All responses now have `success`, `message`, `responseTime`, and `data` keys
- Check `success` boolean for operation status
- Use `message` for user feedback
- Monitor `responseTime` for performance insights
- Access actual data from `data` key
- Pagination info is nested under `data.pagination`

## Testing

All refactored endpoints maintain backward compatibility in terms of:
- HTTP status codes
- Data structure (nested under `data` key)
- Business logic (no changes)

Only the response wrapper has changed to include standard keys.

## Examples by Feature

### POS - Complete Sale
```json
{
  "success": true,
  "message": "Sale completed successfully.",
  "responseTime": 345.12,
  "data": {
    "sale": {
      "id": "uuid",
      "saleNumber": "SALE-2024-001",
      "totalAmount": 50000,
      ...
    }
  }
}
```

### Expenses - Create Expense
```json
{
  "success": true,
  "message": "Expense recorded successfully.",
  "responseTime": 123.45,
  "data": {
    "id": "uuid",
    "title": "Office Rent",
    "amount": 100000,
    ...
  }
}
```

### Products - List Products (Paginated)
```json
{
  "success": true,
  "message": "Products retrieved successfully.",
  "responseTime": 234.56,
  "data": {
    "items": [...],
    "pagination": {
      "total": 50,
      "currentPage": 1,
      "lastPage": 5,
      "perPage": 10,
      "from": 1,
      "to": 10
    }
  }
}
```

### Ads - Track View
```json
{
  "success": true,
  "message": "View tracked successfully.",
  "responseTime": 89.12,
  "data": null
}
```

### Error - Insufficient Stock
```json
{
  "success": false,
  "message": "Insufficient stock for Hair Gel. Available: 3",
  "responseTime": 145.67,
  "data": {
    "productName": "Hair Gel",
    "requestedQuantity": 5,
    "availableStock": 3
  }
}
```

## Future Improvements

1. Add request ID tracking
2. Add API versioning in response
3. Add rate limit information
4. Add deprecation warnings for old endpoints
5. Add response caching headers
6. Add correlation IDs for distributed tracing

## Conclusion

All API controllers now follow a consistent, professional response format that:
- Improves developer experience
- Enables better monitoring and debugging
- Maintains backward compatibility
- Is production-ready and scalable
- Works seamlessly with Kotlin/Android applications using camelCase conventions

