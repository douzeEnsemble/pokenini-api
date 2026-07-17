WITH stats AS (
    SELECT MAX(view_count) AS max_view
    FROM trainer_pokemon_elo AS tpe
        JOIN dex AS d ON tpe.dex_id = d.id
        AND d.slug = :dex_slug
    WHERE tpe.trainer_external_id = :trainer_external_id
        AND tpe.election_slug = :election_slug
),
variables AS (
    SELECT COUNT(
            CASE
                WHEN tpe.view_count = s.max_view - 1
                AND tpe.view_count = tpe.win_count THEN 1
            END
        ) AS under_max_view_count
    FROM trainer_pokemon_elo AS tpe
        JOIN dex AS d ON tpe.dex_id = d.id
        AND d.slug = :dex_slug
        CROSS JOIN stats s
    WHERE tpe.trainer_external_id = :trainer_external_id
        AND tpe.election_slug = :election_slug
)
SELECT p.slug AS pokemon_slug,
    p.name AS pokemon_name,
    p.national_dex_number AS pokemon_national_dex_number,
    p.simplified_name AS pokemon_simplified_name,
    p.forms_label AS pokemon_forms_label,
    p.french_name AS pokemon_french_name,
    p.simplified_french_name AS pokemon_simplified_french_name,
    p.forms_french_label AS pokemon_forms_french_label,
    p.icon_name AS pokemon_icon,
    p.family_order AS pokemon_family_order,
    pp.slug AS family_lead_slug,
    cf.slug as category_form_slug,
    cf.name as category_form_name,
    cf.french_name as category_form_french_name,
    rf.slug as regional_form_slug,
    rf.name as regional_form_name,
    rf.french_name as regional_form_french_name,
    sf.slug as special_form_slug,
    sf.name as special_form_name,
    sf.french_name as special_form_french_name,
    vf.slug as variant_form_slug,
    vf.name as variant_form_name,
    vf.french_name as variant_form_french_name,
    pt.slug AS primary_type_slug,
    pt.name AS primary_type_name,
    pt.french_name AS primary_type_french_name,
    pt.color AS primary_type_color,
    st.slug AS secondary_type_slug,
    st.name AS secondary_type_name,
    st.french_name AS secondary_type_french_name,
    st.color AS secondary_type_color,
    ogb.slug AS original_game_bundle_slug,
    pagb.items AS game_bundle_slugs,
    pagbs.items AS game_bundle_shiny_slugs,
    pic_sr.source_name AS small_regular_credit_name, pic_sr.source_url AS small_regular_credit_url,
    pic_ss.source_name AS small_shiny_credit_name, pic_ss.source_url AS small_shiny_credit_url,
    pic_br.source_name AS big_regular_credit_name, pic_br.source_url AS big_regular_credit_url,
    pic_bs.source_name AS big_shiny_credit_name, pic_bs.source_url AS big_shiny_credit_url,
    CONCAT(
        '9999',
        '-',
        LPAD(CAST(p.national_dex_number AS varchar), 4, '0'),
        '-',
        LPAD(CAST(p.family_order AS varchar), 3, '0')
    ) as pokemon_order_number
FROM pokemon AS p
    LEFT JOIN category_form AS cf ON p.category_form_id = cf.id
    LEFT JOIN regional_form AS rf ON p.regional_form_id = rf.id
    LEFT JOIN special_form AS sf ON p.special_form_id = sf.id
    LEFT JOIN variant_form AS vf ON p.variant_form_id = vf.id
    LEFT JOIN "type" AS pt ON p.primary_type_id = pt.id
    LEFT JOIN "type" AS st ON p.secondary_type_id = st.id
    LEFT JOIN pokemon AS pp ON p.family = pp.slug
    LEFT JOIN game_bundle AS ogb ON p.original_game_bundle_id = ogb.id
    LEFT JOIN pokemon_availabilities AS pagb
        ON p.id = pagb.pokemon_id AND pagb.category = :pokemon_availabilities_game_bundle_category
    LEFT JOIN pokemon_availabilities AS pagbs
        ON p.id = pagbs.pokemon_id AND pagbs.category = :pokemon_availabilities_game_bundle_shiny_category
    LEFT JOIN pokemon_image_credit AS pic_sr ON p.id = pic_sr.pokemon_id AND pic_sr.size = 'small' AND pic_sr.is_shiny = false
    LEFT JOIN pokemon_image_credit AS pic_ss ON p.id = pic_ss.pokemon_id AND pic_ss.size = 'small' AND pic_ss.is_shiny = true
    LEFT JOIN pokemon_image_credit AS pic_br ON p.id = pic_br.pokemon_id AND pic_br.size = 'big' AND pic_br.is_shiny = false
    LEFT JOIN pokemon_image_credit AS pic_bs ON p.id = pic_bs.pokemon_id AND pic_bs.size = 'big' AND pic_bs.is_shiny = true
    JOIN dex_availability AS da ON p.id = da.pokemon_id
    JOIN dex AS d ON da.dex_id = d.id
    AND d.slug = :dex_slug
WHERE EXISTS (
        SELECT 1
        FROM stats AS s,
            variables as v,
            trainer_pokemon_elo AS tpe
        WHERE p.id = tpe.pokemon_id
            AND tpe.trainer_external_id = :trainer_external_id
            AND tpe.dex_id = d.id
            AND tpe.election_slug = :election_slug
            AND tpe.view_count = CASE
                WHEN 0 = v.under_max_view_count THEN s.max_view
                ELSE s.max_view - 1
            END
            AND tpe.view_count = tpe.win_count
    ) -- {album_filters}
ORDER BY RANDOM()
LIMIT :count
