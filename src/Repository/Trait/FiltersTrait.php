<?php

declare(strict_types=1);

namespace App\Repository\Trait;

use App\DTO\AlbumFilter\AlbumFilters;
use Doctrine\DBAL\ArrayParameterType;

trait FiltersTrait
{
    protected function getFiltersQuery(AlbumFilters $filters): string
    {
        return $this->getFiltersQueryTypes($filters)
            .$this->getFiltersQueryForms($filters)
            .$this->getFiltersQueryCatchStates($filters)
            .$this->getFiltersQueryGames($filters)
            .$this->getFiltersQueryFamilies($filters)
            .$this->getFiltersQueryCollections($filters);
    }

    /**
     * @return null[][]|string[][]
     */
    protected function getFiltersParameters(AlbumFilters $filters): array
    {
        return array_merge(
            $this->getFiltersParametersTypes($filters),
            $this->getFiltersParametersForms($filters),
            $this->getFiltersParametersCatchStates($filters),
            $this->getFiltersParametersGames($filters),
            $this->getFiltersParametersFamilies($filters),
            $this->getFiltersParametersCollections($filters),
        );
    }

    /**
     * @return ArrayParameterType[]
     */
    protected function getFiltersTypes(): array
    {
        return [
            'filter_primary_types' => ArrayParameterType::STRING,
            'filter_secondary_types' => ArrayParameterType::STRING,
            'filter_any_types' => ArrayParameterType::STRING,
            'filter_category_forms' => ArrayParameterType::STRING,
            'filter_regional_forms' => ArrayParameterType::STRING,
            'filter_special_forms' => ArrayParameterType::STRING,
            'filter_variant_forms' => ArrayParameterType::STRING,
            'filter_catch_states' => ArrayParameterType::STRING,
            'filter_catch_states_negative' => ArrayParameterType::STRING,
            'filter_original_game_bundles' => ArrayParameterType::STRING,
            'filter_game_bundle_availabilities' => ArrayParameterType::STRING,
            'filter_game_bundle_availabilities_negative' => ArrayParameterType::STRING,
            'filter_game_bundle_shiny_availabilities' => ArrayParameterType::STRING,
            'filter_game_bundle_shiny_availabilities_negative' => ArrayParameterType::STRING,
            'filter_families' => ArrayParameterType::STRING,
            'filter_collection_availabilities' => ArrayParameterType::STRING,
            'filter_collection_availabilities_negative' => ArrayParameterType::STRING,
        ];
    }

    private function getFiltersQueryTypes(AlbumFilters $filters): string
    {
        $query = '';

        if ($filters->primaryTypes->values) {
            $query .= ' AND (pt.slug IN(:filter_primary_types)';
            if ($filters->primaryTypes->hasNull()) {
                $query .= ' OR pt.slug IS NULL';
            }
            $query .= ')';
        }
        if ($filters->secondaryTypes->values) {
            $query .= ' AND (st.slug IN(:filter_secondary_types)';
            if ($filters->secondaryTypes->hasNull()) {
                $query .= ' OR st.slug IS NULL';
            }
            $query .= ')';
        }
        if ($filters->anyTypes->values) {
            $query .= ' AND (pt.slug IN(:filter_any_types) or st.slug IN(:filter_any_types)';
            if ($filters->anyTypes->hasNull()) {
                $query .= ' OR pt.slug IS NULL OR st.slug IS NULL';
            }
            $query .= ')';
        }

        return $query;
    }

    /**
     * @return null[][]|string[][]
     */
    private function getFiltersParametersTypes(AlbumFilters $filters): array
    {
        $parameters = [];

        if ($filters->primaryTypes->values) {
            $parameters['filter_primary_types'] = $filters->primaryTypes->extract();
        }
        if ($filters->secondaryTypes->values) {
            $parameters['filter_secondary_types'] = $filters->secondaryTypes->extract();
        }
        if ($filters->anyTypes->values) {
            $parameters['filter_any_types'] = $filters->anyTypes->extract();
        }

        return $parameters;
    }

    private function getFiltersQueryForms(AlbumFilters $filters): string
    {
        $query = '';

        if ($filters->categoryForms->values) {
            $query .= ' AND (cf.slug IN(:filter_category_forms)';
            if ($filters->categoryForms->hasNull()) {
                $query .= ' OR cf.slug IS NULL';
            }
            $query .= ')';
        }
        if ($filters->regionalForms->values) {
            $query .= ' AND (rf.slug IN(:filter_regional_forms)';
            if ($filters->regionalForms->hasNull()) {
                $query .= ' OR rf.slug IS NULL';
            }
            $query .= ')';
        }
        if ($filters->specialForms->values) {
            $query .= ' AND (sf.slug IN(:filter_special_forms)';
            if ($filters->specialForms->hasNull()) {
                $query .= ' OR sf.slug IS NULL';
            }
            $query .= ')';
        }
        if ($filters->variantForms->values) {
            $query .= ' AND (vf.slug IN(:filter_variant_forms)';
            if ($filters->variantForms->hasNull()) {
                $query .= ' OR vf.slug IS NULL';
            }
            $query .= ')';
        }

        return $query;
    }

    /**
     * @return null[][]|string[][]
     */
    private function getFiltersParametersForms(AlbumFilters $filters): array
    {
        $parameters = [];

        if ($filters->categoryForms->values) {
            $parameters['filter_category_forms'] = $filters->categoryForms->extract();
        }
        if ($filters->regionalForms->values) {
            $parameters['filter_regional_forms'] = $filters->regionalForms->extract();
        }
        if ($filters->specialForms->values) {
            $parameters['filter_special_forms'] = $filters->specialForms->extract();
        }
        if ($filters->variantForms->values) {
            $parameters['filter_variant_forms'] = $filters->variantForms->extract();
        }

        return $parameters;
    }

    private function getFiltersQueryCatchStates(AlbumFilters $filters): string
    {
        $query = '';

        if ($filters->catchStates->values) {
            $query .= ' AND (cs.slug IN(:filter_catch_states)';
            if (
                $filters->catchStates->hasNull()
                || in_array('no', $filters->catchStates->extract())
            ) {
                $query .= ' OR cs.slug IS NULL';
            }
            $query .= ')';
        }
        if ($filters->catchStates->negativeValues) {
            $query .= ' AND (cs.slug NOT IN(:filter_catch_states_negative)';
            if (!in_array('no', $filters->catchStates->extractNegatives())) {
                $query .= ' OR cs.slug IS NULL';
            }
            $query .= ')';
        }

        return $query;
    }

    /**
     * @return null[][]|string[][]
     */
    private function getFiltersParametersCatchStates(AlbumFilters $filters): array
    {
        $parameters = [];

        if ($filters->catchStates->values) {
            $parameters['filter_catch_states'] = $filters->catchStates->extract();
        }
        if ($filters->catchStates->negativeValues) {
            $parameters['filter_catch_states_negative'] = $filters->catchStates->extractNegatives();
        }

        return $parameters;
    }

    private function getFiltersQueryGames(AlbumFilters $filters): string
    {
        $query = '';

        if ($filters->originalGameBundles->values) {
            $query .= ' AND (ogb.slug IN(:filter_original_game_bundles)';
            if ($filters->originalGameBundles->hasNull()) {
                $query .= ' OR ogb.slug IS NULL';
            }
            $query .= ')';
        }
        if ($filters->gameBundleAvailabilities->values) {
            $query .= '
                AND p.id IN (SELECT  gba.pokemon_id
                            FROM    game_bundle_availability AS gba
                                LEFT JOIN game_bundle AS gb
                                    ON gba.bundle_id = gb.id
                            WHERE   gba.is_available = TRUE
                                    AND gb.slug IN(:filter_game_bundle_availabilities)
                        )
            ';
        }
        if ($filters->gameBundleAvailabilities->negativeValues) {
            $query .= '
                AND p.id NOT IN (SELECT  gba.pokemon_id
                            FROM    game_bundle_availability AS gba
                                LEFT JOIN game_bundle AS gb
                                    ON gba.bundle_id = gb.id
                            WHERE   gba.is_available = TRUE
                                    AND gb.slug IN(:filter_game_bundle_availabilities_negative)
                        )
            ';
        }
        if ($filters->gameBundleShinyAvailabilities->values) {
            $query .= '
                AND p.id IN (SELECT  gbsa.pokemon_id
                            FROM    game_bundle_shiny_availability AS gbsa
                                LEFT JOIN game_bundle AS gb
                                    ON gbsa.bundle_id = gb.id
                            WHERE   gbsa.is_available = TRUE
                                    AND gb.slug IN(:filter_game_bundle_shiny_availabilities)
                        )
            ';
        }
        if ($filters->gameBundleShinyAvailabilities->negativeValues) {
            $query .= '
                AND p.id NOT IN (SELECT  gbsa.pokemon_id
                            FROM    game_bundle_shiny_availability AS gbsa
                                LEFT JOIN game_bundle AS gb
                                    ON gbsa.bundle_id = gb.id
                            WHERE   gbsa.is_available = TRUE
                                    AND gb.slug IN(:filter_game_bundle_shiny_availabilities_negative)
                        )
            ';
        }

        return $query;
    }

    /**
     * @return null[][]|string[][]
     */
    private function getFiltersParametersGames(AlbumFilters $filters): array
    {
        $parameters = [];

        if ($filters->originalGameBundles->values) {
            $parameters['filter_original_game_bundles'] = $filters->originalGameBundles->extract();
        }
        if ($filters->gameBundleAvailabilities->values) {
            $parameters['filter_game_bundle_availabilities'] = $filters->gameBundleAvailabilities->extract();
        }
        if ($filters->gameBundleAvailabilities->negativeValues) {
            $parameters['filter_game_bundle_availabilities_negative'] = $filters->gameBundleAvailabilities->extractNegatives();
        }
        if ($filters->gameBundleShinyAvailabilities->values) {
            $parameters['filter_game_bundle_shiny_availabilities'] = $filters->gameBundleShinyAvailabilities->extract();
        }
        if ($filters->gameBundleShinyAvailabilities->negativeValues) {
            $parameters['filter_game_bundle_shiny_availabilities_negative'] = $filters->gameBundleShinyAvailabilities->extractNegatives();
        }

        return $parameters;
    }

    private function getFiltersQueryFamilies(AlbumFilters $filters): string
    {
        $query = '';

        if ($filters->families->values) {
            $query .= ' AND (pp.slug IN(:filter_families)'
                .'OR (p.slug IN(:filter_families) AND 0 = p.family_order)';
            if ($filters->families->hasNull()) {
                $query .= ' OR (pp.slug IS NULL AND 0 <> p.family_order)';
            }
            $query .= ')';
        }

        return $query;
    }

    /**
     * @return null[][]|string[][]
     */
    private function getFiltersParametersFamilies(AlbumFilters $filters): array
    {
        $parameters = [];

        if ($filters->families->values) {
            $parameters['filter_families'] = $filters->families->extract();
        }

        return $parameters;
    }

    private function getFiltersQueryCollections(AlbumFilters $filters): string
    {
        $query = '';

        if ($filters->collectionAvailabilities->values) {
            $query .= "
                AND p.slug IN (SELECT ca.pokemon_slug
                            FROM    collection_availability AS ca
                                LEFT JOIN collection AS c
                                    ON ca.collection_id = c.id
                            WHERE   ca.availability NOT IN ('—', '-', '')
                                    AND c.slug IN(:filter_collection_availabilities)
                        )
            ";
        }

        if ($filters->collectionAvailabilities->negativeValues) {
            $query .= "
                AND p.slug NOT IN (SELECT ca.pokemon_slug
                            FROM    collection_availability AS ca
                                LEFT JOIN collection AS c
                                    ON ca.collection_id = c.id
                            WHERE   ca.availability NOT IN ('—', '-', '')
                                    AND c.slug IN(:filter_collection_availabilities_negative)
                        )
            ";
        }

        return $query;
    }

    /**
     * @return null[][]|string[][]
     */
    private function getFiltersParametersCollections(AlbumFilters $filters): array
    {
        $parameters = [];

        if ($filters->collectionAvailabilities->values) {
            $parameters['filter_collection_availabilities'] = $filters->collectionAvailabilities->extract();
        }
        if ($filters->collectionAvailabilities->negativeValues) {
            $parameters['filter_collection_availabilities_negative'] = $filters->collectionAvailabilities->extractNegatives();
        }

        return $parameters;
    }
}
