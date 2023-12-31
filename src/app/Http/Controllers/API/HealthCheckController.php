<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Vyuldashev\LaravelOpenApi\Attributes as OpenApi;
use Illuminate\Http\JsonResponse;
use App\Custom\Helpers;

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
                'uptime' => self::uptime(),
                'timestamp' => now()->toAtomString(),
                'app_version' => config('app.version'),
                'api_version' => 'v1',
                'diskspace' => $diskspace,
                'latency' => round(microtime(true) - LARAVEL_START, 3) . 's',
                'dependencies' => $dependencies
            ]
        ];

        return Helpers::response($response['message'], $response['data'], $response['code'], $response['status']);
    }

    /**
     * Get the uptime of the server.
     *
     * @return string The uptime of the server.
     */
    private static function uptime(): string
    {
        # Read from the atisgen.txt file
        try {
            # In root of the project: atisgen.txt
            $uptime = file_get_contents(storage_path('app/uptime.txt'));

            # Remove the last line break
            $uptime = trim($uptime);

            // If the uptime is empty, return 'Unknown'
            if (empty($uptime)) {
                return 'Unknown';
            }

            // Assuming the downtime timestamp
            $downtimeTimestamp = intval($uptime);

            // Get the current timestamp
            $currentTimestamp = time();

            // Calculate the uptime duration in seconds
            $uptimeInSeconds = $currentTimestamp - $downtimeTimestamp;

            // Format the uptime for a human-readable display
            $uptimeFormatted = self::formatUptime($uptimeInSeconds);

            // Return the uptime
            $uptime = $uptimeFormatted;
        } catch (\Exception $e) {
            $uptime = 'Unknown';
        }
        return $uptime;
    }

    private static function formatUptime($uptimeInSeconds)
    {
        $uptime = "";

        $days = floor($uptimeInSeconds / 86400);
        if ($days > 0) {
            $uptime .= "$days days, ";
            $uptimeInSeconds %= 86400;
        }

        $hours = floor($uptimeInSeconds / 3600);
        if ($hours > 0) {
            $uptime .= "$hours hours, ";
            $uptimeInSeconds %= 3600;
        }

        $minutes = floor($uptimeInSeconds / 60);
        if ($minutes > 0) {
            $uptime .= "$minutes minutes, ";
        }

        $seconds = $uptimeInSeconds % 60;
        $uptime .= "$seconds seconds";

        return rtrim($uptime, ', '); // Remove trailing comma and space
    }
}
