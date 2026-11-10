<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\DexQueryOptions;
use App\DTO\ElectionReportQueryOptions;
use App\DTO\Response\DexResponse;
use App\DTO\Response\ElectionReportResponse;
use App\Factory\DexResponseFactory;
use App\Factory\ElectionReportResponseFactory;
use App\Service\Election\ElectionReportService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\Serialize;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/election')]
final class ElectionReportController extends AbstractController
{
    use LangQueryTrait;

    /**
     * @param string[] $locales the enabled locales
     */
    public function __construct(
        private readonly ElectionReportService $electionReportService,
        private readonly array $locales,
    ) {}

    /** @return DexResponse[] */
    #[Route(path: '/{trainerExternalId}/list', methods: ['GET'], priority: 2)]
    #[Serialize]
    public function list(
        string $trainerExternalId,
        Request $request,
    ): array {
        $dexQueryOptions = new DexQueryOptions([
            'include_unreleased_dex' => $request->query->getBoolean('include_unreleased_dex', false),
            'include_premium_dex' => $request->query->getBoolean('include_premium_dex', false),
        ]);

        $reportQueryOptions = new ElectionReportQueryOptions([
            'election_slug' => $request->query->get('election_slug', ''),
            'count' => $request->query->get('count', 5),
        ]);

        $lang = $this->getLang($request, $this->locales);

        $dexRows = $this->electionReportService->getEligibleDex($dexQueryOptions, $lang);

        $dexSlugs = array_map(static function (array $row): string {
            /** @var scalar $slug */
            $slug = $row['slug'];

            return (string) $slug;
        }, $dexRows);

        $reports = $this->electionReportService->getBatch(
            $trainerExternalId,
            $dexSlugs,
            $reportQueryOptions->electionSlug,
            $reportQueryOptions->count,
            $lang,
        );

        return DexResponseFactory::fromSqlRows($dexRows, $reports, null !== $lang);
    }

    #[Route(path: '/{trainerExternalId}/{dexSlug}', methods: ['GET'])]
    #[Serialize]
    public function show(
        string $trainerExternalId,
        string $dexSlug,
        Request $request,
    ): ElectionReportResponse {
        $reportQueryOptions = new ElectionReportQueryOptions([
            'election_slug' => $request->query->get('election_slug', ''),
            'count' => $request->query->get('count', 5),
        ]);

        $lang = $this->getLang($request, $this->locales);

        $report = $this->electionReportService->get(
            $trainerExternalId,
            $dexSlug,
            $reportQueryOptions->electionSlug,
            $reportQueryOptions->count,
            $lang,
        );

        return ElectionReportResponseFactory::fromReport($report, null !== $lang);
    }
}
