<?php

namespace App\Http\Controllers;

use App\Models\FeatureFlag;
use Illuminate\Http\Request;

class ApiDocumentationController extends Controller
{
    /**
     * Show public API documentation
     */
    public function index()
    {
        // Check if API is enabled
        $apiEnabled = FeatureFlag::isEnabled('enable_api');

        // Check if documentation is public
        $docsPublic = FeatureFlag::isEnabled('enable_public_api_docs');

        // Get API settings
        $settings = [
            'base_url' => url('/api/v1'),
            'oauth_url' => url('/oauth/token'),
            'rate_limit' => config('api.default_rate_limit', 1000),
            'version' => 'v1.0.0',
        ];

        return view('api.documentation', compact('apiEnabled', 'docsPublic', 'settings'));
    }

    /**
     * Show API status
     */
    public function status()
    {
        $apiEnabled = FeatureFlag::isEnabled('enable_api');

        return response()->json([
            'status' => $apiEnabled ? 'operational' : 'disabled',
            'version' => 'v1.0.0',
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Show OpenAPI/Swagger specification
     */
    public function openApiSpec()
    {
        $spec = [
            'openapi' => '3.0.0',
            'info' => [
                'title' => config('app.name') . ' API',
                'description' => 'Complete API for music streaming platform',
                'version' => '1.0.0',
                'contact' => [
                    'email' => 'api@' . parse_url(config('app.url'), PHP_URL_HOST),
                ],
            ],
            'servers' => [
                [
                    'url' => url('/api/v1'),
                    'description' => 'Production server',
                ],
            ],
            'paths' => $this->getApiPaths(),
            'components' => [
                'securitySchemes' => [
                    'bearerAuth' => [
                        'type' => 'http',
                        'scheme' => 'bearer',
                        'bearerFormat' => 'JWT',
                    ],
                ],
            ],
            'security' => [
                ['bearerAuth' => []],
            ],
        ];

        return response()->json($spec);
    }

    /**
     * Get API paths for OpenAPI spec
     */
    protected function getApiPaths()
    {
        return [
            '/tracks' => [
                'get' => [
                    'summary' => 'List tracks',
                    'tags' => ['Tracks'],
                    'parameters' => [
                        [
                            'name' => 'page',
                            'in' => 'query',
                            'schema' => ['type' => 'integer'],
                        ],
                        [
                            'name' => 'per_page',
                            'in' => 'query',
                            'schema' => ['type' => 'integer'],
                        ],
                        [
                            'name' => 'genre',
                            'in' => 'query',
                            'schema' => ['type' => 'string'],
                        ],
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'Successful response',
                        ],
                    ],
                ],
            ],
            '/tracks/{id}' => [
                'get' => [
                    'summary' => 'Get track by ID',
                    'tags' => ['Tracks'],
                    'parameters' => [
                        [
                            'name' => 'id',
                            'in' => 'path',
                            'required' => true,
                            'schema' => ['type' => 'integer'],
                        ],
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'Successful response',
                        ],
                        '404' => [
                            'description' => 'Track not found',
                        ],
                    ],
                ],
            ],
            '/search' => [
                'get' => [
                    'summary' => 'Search tracks, albums, artists, playlists',
                    'tags' => ['Search'],
                    'parameters' => [
                        [
                            'name' => 'q',
                            'in' => 'query',
                            'required' => true,
                            'schema' => ['type' => 'string'],
                        ],
                        [
                            'name' => 'type',
                            'in' => 'query',
                            'schema' => [
                                'type' => 'string',
                                'enum' => ['tracks', 'albums', 'artists', 'playlists'],
                            ],
                        ],
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'Successful response',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Interactive API explorer
     */
    public function explorer()
    {
        return view('api.explorer');
    }
}
