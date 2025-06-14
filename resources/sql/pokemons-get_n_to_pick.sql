SELECT
    p.slug AS pokemon_slug,
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
    rf.slug as regional_form_slug,
    rf.name as regional_form_name,
    sf.slug as special_form_slug,
    sf.name as special_form_name,
    vf.slug as variant_form_slug,
    vf.name as variant_form_name,
    pt.slug AS primary_type_slug,
    pt.name AS primary_type_name,
    pt.french_name AS primary_type_french_name,
    st.slug AS secondary_type_slug,
    st.name AS secondary_type_name,
    st.french_name AS secondary_type_french_name,
    ogb.slug AS original_game_bundle_slug,
    CONCAT(
        '9999',
        '-',
        LPAD(CAST(p.national_dex_number AS varchar), 4, '0'),
        '-',
        LPAD(CAST(p.family_order AS varchar), 3, '0')
    ) as pokemon_order_number
FROM
    pokemon AS p
    LEFT JOIN category_form AS cf 
        ON p.category_form_id = cf.id
    LEFT JOIN regional_form AS rf 
        ON p.regional_form_id = rf.id
    LEFT JOIN special_form AS sf 
        ON p.special_form_id = sf.id
    LEFT JOIN variant_form AS vf 
        ON p.variant_form_id = vf.id
    LEFT JOIN "type" AS pt 
        ON p.primary_type_id = pt.id
    LEFT JOIN "type" AS st 
        ON p.secondary_type_id = st.id
    LEFT JOIN pokemon AS pp 
        ON p.family = pp.slug
    LEFT JOIN game_bundle AS ogb 
        ON p.original_game_bundle_id = ogb.id
    JOIN dex_availability AS da
        ON p.id = da.pokemon_id
    JOIN dex AS d
        ON da.dex_id = d.id AND d.slug = :dex_slug
WHERE NOT EXISTS (
        SELECT  1
        FROM    trainer_pokemon_elo AS tpe
        WHERE   p.id = tpe.pokemon_id
            AND tpe.trainer_external_id = :trainer_external_id
            AND tpe.dex_id = d.id
            AND tpe.election_slug = :election_slug
    )
    -- {album_filters}
ORDER BY RANDOM()
LIMIT :count