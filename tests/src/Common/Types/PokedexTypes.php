<?php

declare(strict_types=1);

namespace App\Tests\Common\Types;

/**
 * @psalm-type PokedexRepositoryItem = array{
 *  pokemon_slug: string,
 *  pokemon_name: string,
 *  pokemon_national_dex_number: string,
 *  pokemon_simplified_name: string,
 *  pokemon_forms_label: string,
 *  pokemon_french_name: string,
 *  pokemon_simplified_french_name: string,
 *  pokemon_forms_french_label: string,
 *  pokemon_icon: string,
 *  pokemon_family_order: int,
 *  family_lead_slug: string,
 *  category_form_slug: string|null,
 *  category_form_name: string|null,
 *  regional_form_slug: string|null,
 *  regional_form_name: string|null,
 *  special_form_slug: string|null,
 *  special_form_name: string|null,
 *  variant_form_slug: string|null,
 *  variant_form_name: string|null,
 *  catch_state_slug: string|null,
 *  catch_state_name: string|null,
 *  catch_state_french_name: string|null,
 *  catch_state_color: string|null,
 *  pokemon_regional_dex_number: string|null,
 *  primary_type_slug: string,
 *  primary_type_name: string,
 *  primary_type_french_name: string,
 *  primary_type_color: string,
 *  secondary_type_slug: string|null,
 *  secondary_type_name: string|null,
 *  secondary_type_french_name: string|null,
 *  secondary_type_color: string|null,
 *  original_game_bundle_slug: string,
 *  pokemon_order_number: string,
 *  game_bundle_slugs: string|null,
 *  game_bundle_shiny_slugs: string|null,
 * }
 * @psalm-type PokedexRepositoryItems = array<int, PokedexRepositoryItem>
 * @psalm-type PokedexResponseReport = array{
 *  detail: array<int, array{
 *      count: int,
 *      catch_state: array{
 *          slug: string,
 *          name: string,
 *          french_name: string,
 *          color: string
 *      }
 *  }>,
 *  total: int,
 *  total_caught: int,
 *  total_uncaught: int
 * }
 * @psalm-type PokedexResponse = array{
 *  dex: array<string, mixed>|null,
 *  pokemons: PokedexRepositoryItems,
 *  filtered_report: PokedexResponseReport,
 *  report: PokedexResponseReport,
 * }
 *
 * @psalm-suppress UnusedClass
 */
final class PokedexTypes {}
