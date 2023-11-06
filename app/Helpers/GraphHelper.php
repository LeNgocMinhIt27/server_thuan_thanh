<?php

namespace App\Helpers;

use Microsoft\Graph\Graph;
use Microsoft\Graph\Http;
use Microsoft\Graph\Model;
use GuzzleHttp\Client;

class GraphHelper
{
    private static ?Client $tokenClient = null;
    private static ?string $clientId = null;
    private static ?string $tenantId = null;
    private static ?string $graphUserScopes = null;
    private static ?Graph $userClient = null;
    private static ?string $userToken = null;

    private static bool $initialized = false;

    public static function initializeGraphForUserAuth(): void
    {
        if (!GraphHelper::$initialized) {
            GraphHelper::$tokenClient = new Client();
            GraphHelper::$clientId = env('MSAL_CLIENT_ID');
            GraphHelper::$tenantId = env('MSAL_TENANT_ID');
            GraphHelper::$graphUserScopes = env('GRAPH_USER_SCOPES');
            GraphHelper::$userClient = new Graph();
            GraphHelper::$initialized = true;
        }
    }

    public static function getUserClient(): Graph
    {
        if (!GraphHelper::$initialized) {
            // Nếu chưa khởi tạo, hãy gọi initializeGraphForUserAuth trước
            GraphHelper::initializeGraphForUserAuth();
        }
        return GraphHelper::$userClient;
    }
}
