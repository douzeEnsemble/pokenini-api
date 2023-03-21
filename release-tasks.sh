php bin/console messenger:stop-workers

php bin/console app:update:labels
php bin/console app:update:games_and_dex
php bin/console app:update:pokemons
php bin/console app:update:regional_dex_numbers
php bin/console app:update:games_availabilities
php bin/console app:calculate:game_bundles_availabilities
php bin/console app:calculate:dex_availabilities
