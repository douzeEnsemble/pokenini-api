<?php

declare(strict_types=1);

namespace App\Controller\Debug;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

abstract class AbstractDebugController extends AbstractController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
    ) {
    }

    protected function serialize(mixed $value): string
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
