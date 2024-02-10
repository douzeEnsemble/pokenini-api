<?php

declare(strict_types=1);

namespace App\Repository\Trait;

use App\DTO\AlbumFilter\AlbumFilters;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\ParameterType;

trait FiltersTrait
{
    protected function getFiltersQuery(AlbumFilters $filters): string
    {
        return $this->getFiltersQueryTypes($filters)
            . $this->getFiltersQueryForms($filters)
            . $this->getFiltersQueryCatchStates($filters);
    }

    /**
     * @return string[][]|null[][]
     */
    protected function getFiltersParameters(AlbumFilters $filters): array
    {
        return array_merge(
            $this->getFiltersParametersTypes($filters),
            $this->getFiltersParametersForms($filters),
            $this->getFiltersParametersCatchStates($filters),
        );
    }

    /**
     * @return int[]
     */
    protected function getFiltersTypes(): array
    {
        return [
            'trainer_external_id' => ParameterType::STRING,
            'dex_slug' => ParameterType::STRING,
            'filter_primary_types' => ArrayParameterType::STRING,
            'filter_secondary_types' => ArrayParameterType::STRING,
            'filter_any_types' => ArrayParameterType::STRING,
            'filter_category_forms' => ArrayParameterType::STRING,
            'filter_regional_forms' => ArrayParameterType::STRING,
            'filter_special_forms' => ArrayParameterType::STRING,
            'filter_variant_forms' => ArrayParameterType::STRING,
            'filter_catch_states' => ArrayParameterType::STRING,
        ];
    }

    private function getFiltersQueryTypes(AlbumFilters $filters): string
    {
        $query = '';

        if (!empty($filters->primaryTypes->values)) {
            $query .= ' AND (pt.slug IN(:filter_primary_types)';
            if ($filters->primaryTypes->hasNull()) {
                $query .= ' OR pt.slug IS NULL';
            }
            $query .= ')';
        }
        if (!empty($filters->secondaryTypes->values)) {
            $query .= ' AND (st.slug IN(:filter_secondary_types)';
            if ($filters->secondaryTypes->hasNull()) {
                $query .= ' OR st.slug IS NULL';
            }
            $query .= ')';
        }
        if (!empty($filters->anyTypes->values)) {
            $query .= ' AND (pt.slug IN(:filter_any_types) or st.slug IN(:filter_any_types)';
            if ($filters->anyTypes->hasNull()) {
                $query .= ' OR pt.slug IS NULL OR st.slug IS NULL';
            }
            $query .= ')';
        }

        return $query;
    }

    /**
     * @return string[][]|null[][]
     */
    private function getFiltersParametersTypes(AlbumFilters $filters): array
    {
        $parameters = [];

        if (!empty($filters->primaryTypes->values)) {
            $parameters['filter_primary_types'] = $filters->primaryTypes->extract();
        }
        if (!empty($filters->secondaryTypes->values)) {
            $parameters['filter_secondary_types'] = $filters->secondaryTypes->extract();
        }
        if (!empty($filters->anyTypes->values)) {
            $parameters['filter_any_types'] = $filters->anyTypes->extract();
        }

        return $parameters;
    }

    private function getFiltersQueryForms(AlbumFilters $filters): string
    {
        $query = '';

        if (!empty($filters->categoryForms->values)) {
            $query .= ' AND (cf.slug IN(:filter_category_forms)';
            if ($filters->categoryForms->hasNull()) {
                $query .= ' OR cf.slug IS NULL';
            }
            $query .= ')';
        }
        if (!empty($filters->regionalForms->values)) {
            $query .= ' AND (rf.slug IN(:filter_regional_forms)';
            if ($filters->regionalForms->hasNull()) {
                $query .= ' OR rf.slug IS NULL';
            }
            $query .= ')';
        }
        if (!empty($filters->specialForms->values)) {
            $query .= ' AND (sf.slug IN(:filter_special_forms)';
            if ($filters->specialForms->hasNull()) {
                $query .= ' OR sf.slug IS NULL';
            }
            $query .= ')';
        }
        if (!empty($filters->variantForms->values)) {
            $query .= ' AND (vf.slug IN(:filter_variant_forms)';
            if ($filters->variantForms->hasNull()) {
                $query .= ' OR vf.slug IS NULL';
            }
            $query .= ')';
        }

        return $query;
    }

    /**
     * @return string[][]|null[][]
     */
    private function getFiltersParametersForms(AlbumFilters $filters): array
    {
        $parameters = [];

        if (!empty($filters->categoryForms->values)) {
            $parameters['filter_category_forms'] = $filters->categoryForms->extract();
        }
        if (!empty($filters->regionalForms->values)) {
            $parameters['filter_regional_forms'] = $filters->regionalForms->extract();
        }
        if (!empty($filters->specialForms->values)) {
            $parameters['filter_special_forms'] = $filters->specialForms->extract();
        }
        if (!empty($filters->variantForms->values)) {
            $parameters['filter_variant_forms'] = $filters->variantForms->extract();
        }

        return $parameters;
    }

    private function getFiltersQueryCatchStates(AlbumFilters $filters): string
    {
        $query = '';

        if (!empty($filters->catchStates->values)) {
            $query .= ' AND (cs.slug IN(:filter_catch_states)';
            if ($filters->catchStates->hasNull()) {
                $query .= ' OR cs.slug IS NULL';
            }
            $query .= ')';
        }

        return $query;
    }

    /**
     * @return string[][]|null[][]
     */
    private function getFiltersParametersCatchStates(AlbumFilters $filters): array
    {
        $parameters = [];

        if (!empty($filters->catchStates->values)) {
            $parameters['filter_catch_states'] = $filters->catchStates->extract();
        }

        return $parameters;
    }
}
