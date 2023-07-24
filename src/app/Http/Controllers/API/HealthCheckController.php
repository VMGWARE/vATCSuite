<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Vyuldashev\LaravelOpenApi\Attributes as OpenApi;
use Illuminate\Http\JsonResponse;
use PDO;

#[OpenApi\PathItem]
class HealthCheckController extends Controller
{
    /**
     * API Health Check
     *
     * @return JsonResponse
     */
    #[OpenApi\Operation(tags: ['Utilities'])]
    #[OpenApi\Response(factory: \App\OpenApi\Responses\Utilities\HealthCheck\ErrorResponse::class, statusCode: 503)]
    #[OpenApi\Response(factory: \App\OpenApi\Responses\Utilities\HealthCheck\SuccessResponse::class, statusCode: 200)]
    public function index(): JsonResponse
    {
        // Status
        $status = 'ok';

        // Dependencies
        $dependencies = [
            'database' => DB::connection()->getPdo() ? 'OK' : 'Error',
            'storage' => is_writable(storage_path()) ? 'OK' : 'Error',
        ];

        // Check dependencies
        foreach ($dependencies as $dependency) {
            if ($dependency !== 'OK') {
                $status = 'error';
                break;
            }
        }

        // Response
        $response = [
            'status' => $status,
            'code' => $status === 'healthy' ? 200 : 503,
            'message' => $status === 'healthy' ? 'API v1 is up and running!' : 'API v1 is having issues.',
            'data' => [
                'uptime' => 'N/A', // TODO: Get uptime from 'uptime' command
                'timestamp' => now()->toAtomString(),
                'version' => config('app.version'),
                'diskspace' => disk_free_space('/') / disk_total_space('/') * 100,
                'latency' => round(microtime(true) - LARAVEL_START, 3),
                'dependencies' => $dependencies
            ]
        ];

        return response()->json($response);
    }
}
