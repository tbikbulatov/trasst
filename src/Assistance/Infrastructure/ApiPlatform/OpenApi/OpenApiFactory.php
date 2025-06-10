<?php

declare(strict_types=1);

namespace App\Assistance\Infrastructure\ApiPlatform\OpenApi;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\Model;
use ApiPlatform\OpenApi\Model\PathItem;
use ApiPlatform\OpenApi\OpenApi;
use Override;

final readonly class OpenApiFactory implements OpenApiFactoryInterface
{
    public function __construct(
        private OpenApiFactoryInterface $decorated,
    ) {
    }

    /**
     * @param array<string,mixed> $context
     */
    #[Override]
    public function __invoke(array $context = []): OpenApi
    {
        $openApi = $this->decorated->__invoke($context);

        /** @var array<string,PathItem> $paths */
        $paths = $openApi->getPaths()->getPaths();

        $filteredPaths = new Model\Paths();

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
