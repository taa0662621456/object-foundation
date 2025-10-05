<?php
namespace ObjectFoundation\Api;

final class OpenApiGenerator
{
    public function build(): array
    {
        return [
            'openapi' => '3.1.0',
            'info' => [
                'title' => 'Object Foundation API',
                'version' => '2.1.0-beta',
                'description' => 'REST API for OQL queries and ontology introspection.'
            ],
            'servers' => [
                ['url' => '/']
            ],
            'paths' => [
                '/api/oql' => [
                    'get' => $this->oqlEndpoint('GET'),
                    'post' => $this->oqlEndpoint('POST'),
                ],
                '/api/ontology/entities' => [
                    'get' => [
                        'summary' => 'List manifests for provided class names',
                        'parameters' => [
                            [
                                'name' => 'classes',
                                'in' => 'query',
                                'required' => false,
                                'schema' => ['type' => 'array', 'items' => ['type' => 'string']],
                                'style' => 'form',
                                'explode' => true
                            ]
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Array of manifests',
                                'content' => [
                                    'application/json' => ['schema' => ['type' => 'array', 'items' => ['type' => 'object']]]
                                ]
                            ]
                        ]
                    ]
                ],
                '/api/ontology/entity' => [
                    'get' => [
                        'summary' => 'Manifest for a single class',
                        'parameters' => [
                            ['name' => 'name', 'in' => 'query', 'required' => true, 'schema' => ['type' => 'string']]
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Manifest',
                                'content' => ['application/json' => ['schema' => ['type' => 'object']]]
                            ],
                            '404' => ['description' => 'Not found']
                        ]
                    ]
                ],
                '/api/ontology/traits' => [
                    'get' => [
                        'summary' => 'List available ObjectFoundation traits',
                        'responses' => [
                            '200' => [
                                'description' => 'Array of trait FQCNs',
                                'content' => ['application/json' => ['schema' => ['type' => 'array', 'items' => ['type' => 'string']]]]
                            ]
                        ]
                    ]
                ],
                '/api/openapi.json' => [
                    'get' => [
                        'summary' => 'Download OpenAPI JSON',
                        'responses' => ['200' => ['description' => 'OpenAPI JSON']]
                    ]
                ],
                '/api/docs' => [
                    'get' => [
                        'summary' => 'Swagger UI',
                        'responses' => ['200' => ['description' => 'HTML UI']]
                    ]
                ]
            ]
        ];
    }

    public function toJson(array $doc): string
    {
        return json_encode($doc, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public function toYaml(array $doc, int $indent = 0): string
    {
        // Simple YAML emitter (sufficient for our doc)
        return self::yaml($doc, $indent);
    }

    private static function yaml($data, int $indent = 0): string
    {
        $pad = str_repeat('  ', $indent);
        if (is_array($data)) {
            $out = '';
            $isAssoc = array_keys($data) !== range(0, count($data) - 1);
            if (!$isAssoc) {
                foreach ($data as $item) {
                    $out .= $pad . "- " . trim(self::yaml($item, $indent + 1)) . "\n";
                }
                return $out;
            } else {
                foreach ($data as $k => $v) {
                    if (is_array($v)) {
                        $out .= $pad . $k . ":\n" . self::yaml($v, $indent + 1);
                    } else {
                        $val = is_bool($v) ? ($v ? 'true' : 'false') : (string)$v;
                        $out .= $pad . $k . ": " . $val . "\n";
                    }
                }
                return $out;
            }
        }
        return $pad . (string)$data . "\n";
    }
}