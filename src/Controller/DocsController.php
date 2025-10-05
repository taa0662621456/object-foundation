<?php
    declare(strict_types=1);

    namespace ObjectFoundation\Controller;

    use ObjectFoundation\Http\{Request, Response};
    // NOTE: Replace with your actual OpenApi generator
    use ObjectFoundation\Api\OpenApiGenerator;

    final class DocsController
    {
        public function openapi(Request $req): Response
        {
            if (class_exists(OpenApiGenerator::class)) {
                $gen = new OpenApiGenerator();
                $json = $gen->toJson($gen->build());
                return Response::json($json);
            }
            return Response::json(['openapi' => 'not-configured'], 200);
        }

        public function swagger(Request $req): Response
        {
            $html = <<<'HTML'
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Object Foundation API — Swagger UI</title>
  <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5/swagger-ui.css" />
</head>
<body>
  <div id="swagger-ui"></div>
  <script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
  <script>
    window.onload = () => {
      window.ui = SwaggerUIBundle({ url: '/api/openapi.json', dom_id: '#swagger-ui' });
    };
  </script>
</body>
</html>
HTML;
            return Response::html($html);
        }
    }
