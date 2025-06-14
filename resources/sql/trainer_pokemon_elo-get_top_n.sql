WITH total AS (
    SELECT
        COUNT(1) AS count
    FROM
        dex_availability AS da
        JOIN dex AS d 
            ON da.dex_id = d.id AND d.slug = :dex_slug
),
views AS (
    SELECT
        COUNT(1) AS count
    FROM
        trainer_pokemon_elo AS tpe
        JOIN dex AS d 
            ON tpe.dex_id = d.id AND d.slug = :dex_slug
    WHERE
        trainer_external_id = :trainer_external_id
        AND election_slug = :election_slug
),
elo AS (
    SELECT
        tpe.elo,
        tpe.pokemon_id
    FROM
        trainer_pokemon_elo tpe
        JOIN dex AS d 
            ON tpe.dex_id = d.id AND d.slug = :dex_slug
    WHERE
        trainer_external_id = :trainer_external_id
        AND election_slug = :election_slug
),
stats AS (
    SELECT
        AVG(elo) AS avg_elo,
        STDDEV(elo) AS stddev_elo
    FROM
        elo
),
variables AS (
    SELECT
        CASE
            WHEN 0 < t.count THEN COALESCE(
                1 / NULLIF(
                    AVG(
                        CASE WHEN t.count > 0 THEN v.count * 1.0 / t.count ELSE NULL END
                    ),
                    0
                ),
                0
            )
            ELSE 0
        END AS multiplier
    FROM
        views AS v,
        total AS t
    GROUP BY
        t.count,
        v.count
)
SELECT
    e.elo AS elo,
    e.elo > (s.avg_elo + (s.stddev_elo * var.multiplier)) AS significance,
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
    stats AS s,
    variables AS var,
    elo AS e
    JOIN pokemon AS p ON e.pokemon_id = p.id
    LEFT JOIN category_form AS cf ON p.category_form_id = cf.id
    LEFT JOIN regional_form AS rf ON p.regional_form_id = rf.id
    LEFT JOIN special_form AS sf ON p.special_form_id = sf.id
    LEFT JOIN variant_form AS vf ON p.variant_form_id = vf.id
    LEFT JOIN "type" AS pt ON p.primary_type_id = pt.id
    LEFT JOIN "type" AS st ON p.secondary_type_id = st.id
    LEFT JOIN pokemon AS pp ON p.family = pp.slug
    LEFT JOIN game_bundle AS ogb ON p.original_game_bundle_id = ogb.id
ORDER BY
    e.elo DESC,
    p.slug ASC
LIMIT
    :count