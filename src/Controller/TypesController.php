<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\Response\LocalizedTypeResponse;
use App\DTO\Response\TypeResponse;
use App\DTO\TypesQueryOptions;
use App\Factory\TypeResponseFactory;
use App\Service\TypesService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\Serialize;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\OptionsResolver\Exception\InvalidArgumentException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/types')]
final class TypesController extends AbstractController
{
    /**
     * Without "lang", each type has its name in English and in French (legacy shape).
     * With "lang", each type has a single name, in that language.
     *
     * @param string[] $locales
     *
     * @return LocalizedTypeResponse[]|TypeResponse[]
     */
    #[Route(path: '', methods: ['GET'])]
    #[Serialize]
    public function get(
        Request $request,
        TypesService $service,
        array $locales,
    ): array {
        try {
            $options = new TypesQueryOptions(['lang' => $request->query->get('lang')], $locales);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        $types = $service->getAll($options->lang);

        if (null === $options->lang) {
            return TypeResponseFactory::fromSqlRows($types);
        }

        return TypeResponseFactory::localizedFromSqlRows($types);
    }
}
