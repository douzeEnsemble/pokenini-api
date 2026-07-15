<?php

declare(strict_types=1);

namespace App\Exception;

final class ImagePipelineRunNotFoundException extends \RuntimeException
{
    public function __construct(string $correlationId)
    {
        parent::__construct("Image pipeline run not found for correlation id '{$correlationId}'");
    }
}
