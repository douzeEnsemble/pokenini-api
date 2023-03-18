# Init
mkdir -p tests/Performance/Commands/Results

for i in $(seq 1 $1)
do
    # Execute commands
    # time -f "%e" -o "tests/Performance/Commands/Results/update_labels-$i.txt" \
    #     php bin/console --env=int app:update:labels \
    #     > /dev/null
    # time -f "%e" -o "tests/Performance/Commands/Results/update_games_and_dex-$i.txt" \
    #     php bin/console --env=int app:update:games_and_dex \
    #     > /dev/null
    # time -f "%e" -o "tests/Performance/Commands/Results/update_pokemons-$i.txt" \
    #     php bin/console --env=int app:update:pokemons \
    #     > /dev/null
    # time -f "%e" -o "tests/Performance/Commands/Results/update_regional_dex_numbers-$i.txt" \
    #     php bin/console --env=int app:update:regional_dex_numbers \
    #     > /dev/null
    # time -f "%e" -o "tests/Performance/Commands/Results/update_games_availabilities-$i.txt" \
    #     php bin/console --env=int app:update:games_availabilities \
    #     > /dev/null
    # time -f "%e" -o "tests/Performance/Commands/Results/calculate_game_bundles_availabilities-$i.txt" \
    #     php bin/console --env=int app:calculate:game_bundles_availabilities \
    #     > /dev/null
    time -f "%e" -o "tests/Performance/Commands/Results/calculate_dex_availabilities-$i.txt" \
        php bin/console --env=int app:calculate:dex_availabilities \
        > /dev/null
done