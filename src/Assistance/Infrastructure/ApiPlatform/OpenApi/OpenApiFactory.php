<?php

declare(strict_types=1);

namespace App\Assistance\Infrastructure\ApiPlatform\OpenApi;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\Model\PathItem;
use ApiPlatform\OpenApi\OpenApi;
use ApiPlatform\OpenApi\Model;

final readonly class OpenApiFactory implements OpenApiFactoryInterface
{
    public function __construct(
        private OpenApiFactoryInterface $decorated,
    ) {
    }

    public function __invoke(array $context = []): OpenApi
    {
        $openApi = $this->decorated->__invoke($context);
        $paths = $openApi->getPaths()->getPaths();
        $filteredPaths = new Model\Paths();

        /** @var string $path */
        /** @var PathItem $pathItem */
        foreach ($paths as $path => $pathItem) {
            switch ($path) {
                case '/api/stays':
                    continue 2;
                case '/api/journals':
                    $pathItem = $pathItem->withGet(null);
                    break;
            }
            $filteredPaths->addPath($path, $pathItem);
        }

        return $openApi->withPaths($filteredPaths);
    }
}
