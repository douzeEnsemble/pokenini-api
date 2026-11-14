<?php

declare(strict_types=1);

namespace App\ActionStarter;

final readonly class ActionDefinition implements \JsonSerializable
{
    /**
     * @param list<string> $dependsOn "type:name" of the actions whose data this one reads
     */
    public function __construct(
        public string $type,
        public string $name,
        public ActionStarterInterface $starter,
        public array $dependsOn = [],
    ) {}

    /**
     * @return array{type: string, name: string, depends_on: list<string>}
     */
    #[\Override]
    public function jsonSerialize(): array
    {
        return [
            'type' => $this->type,
            'name' => $this->name,
            'depends_on' => $this->dependsOn,
        ];
    }
}
