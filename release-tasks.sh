php bin/console app:import:pokemon resources/data/pokemon_list.csv
php bin/console app:import:game_availability resources/data/bulbapedia_availability.csv
php bin/console app:calculate:game_bundle_availability
php bin/console app:calculate:dex_availability
