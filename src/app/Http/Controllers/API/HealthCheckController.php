<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Vyuldashev\LaravelOpenApi\Attributes as OpenApi;
use Illuminate\Http\JsonResponse;

#[OpenApi\PathItem]
class HealthCheckController extends Controller
{
    /**
     * API Health Check Endpoint.
     *
     * This method provides an endpoint to verify the health status of the API and its underlying infrastructure. It checks 
     * various dependencies such as the database and storage capabilities, reports on the available disk space, latency, 
     * and API version details. Based on these checks, it will return a status indicating the health of the API.
     *
     * Key Return Values:
     * - 'OK': Indicates that the component or dependency is functioning correctly.
     * - 'Error': Signifies that there's an issue with that particular component or dependency.
     *
     * @return JsonResponse A structured JSON response containing:
     *                      - 'status': Overall health status of the API, either 'ok' or 'error'.
     *                      - 'code': HTTP status code, either 200 (OK) or 503 (Service Unavailable).
     *                      - 'message': A brief message indicating the status of the API.
     *                      - 'data': An array of diagnostic information including:
     *                                 * 'uptime': The uptime of the service (To be implemented).
     *                                 * 'timestamp': Current timestamp.
     *                                 * 'app_version': The version of the application.
     *                                 * 'api_version': The version of the API being used.
     *                                 * 'diskspace': Percentage of free disk space.
     *                                 * 'latency': Time taken for the request to be processed.
     *                                 * 'dependencies': An array reporting the status of various dependencies.
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

        // Disk space
        $diskspace = disk_free_space('/') / disk_total_space('/') * 100;
        $diskspace = round($diskspace, 2);

        // Response
        $response = [
            'status' => $status,
            'code' => $status === 'ok' ? 200 : 503,
            'message' => $status === 'ok' ? 'API v1 is up and running!' : 'API v1 is having issues.',
            'data' => [
                'uptime' => 'N/A', // TODO: Get uptime from 'uptime' command
                'timestamp' => now()->toAtomString(),
                'app_version' => config('app.version'),
                'api_version' => 'v1',
                'diskspace' => $diskspace,
                'latency' => round(microtime(true) - LARAVEL_START, 3) . 's',
                'dependencies' => $dependencies
            ]
        ];

        return response()->json($response);
    }
}
