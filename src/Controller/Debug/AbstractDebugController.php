<?php

declare(strict_types=1);

namespace App\Controller\Debug;

use App\Entity\Dex;
use App\Entity\Pokemon;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

abstract class AbstractDebugController extends AbstractController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
    ) {}

    /**
     * @param (bool[]|string)[]|Dex|Pokemon $value
     *
     * @psalm-param Dex|Pokemon|array{0?: string, gamesAvailabilities?: array<bool>, gamesShiniesAvailabilities?: array<bool>, gameBundlesAvailabilities?: array<bool>, gameBundlesShiniesAvailabilities?: array<bool>,...} $value
     */
    protected function serialize(array|Dex|Pokemon $value): string
    {
        return $this->serializer->serialize(
            $value,
            'json',
            [
                AbstractNormalizer::IGNORED_ATTRIBUTES => [
                    '__initializer__',
                    '__cloner__',
                    '__isInitialized__',
                ],
            ]
        );
    }
}
