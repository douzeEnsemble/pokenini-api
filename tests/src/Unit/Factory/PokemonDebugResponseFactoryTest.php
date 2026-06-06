<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\Entity\CategoryForm;
use App\Entity\GameBundle;
use App\Entity\GameGeneration;
use App\Entity\Pokemon;
use App\Entity\RegionalForm;
use App\Entity\SpecialForm;
use App\Entity\Type;
use App\Entity\VariantForm;
use App\Factory\PokemonDebugResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

/**
 * @internal
 */
#[CoversClass(PokemonDebugResponseFactory::class)]
final class PokemonDebugResponseFactoryTest extends TestCase
{
    #[Test]
    public function fromPokemonMapsAllScalarFields(): void
    {
        $pokemon = $this->buildBasePokemon();

        $result = PokemonDebugResponseFactory::fromPokemon($pokemon);

        self::assertNull($result->identifier);
        self::assertSame('bulbasaur', $result->slug);
        self::assertSame('Bulbasaur', $result->name);
        self::assertSame('Bulbizarre', $result->frenchName);
        self::assertSame('Bulbasaur', $result->simplifiedName);
        self::assertSame('Bulbizarre', $result->simplifiedFrenchName);
        self::assertSame('', $result->formsLabel);
        self::assertSame('', $result->formsFrenchLabel);
        self::assertSame(1, $result->nationalDexNumber);
        self::assertSame('bulbasaur', $result->family);
        self::assertTrue($result->bankable);
        self::assertNull($result->bankableish);
        self::assertSame('bulbasaur', $result->iconName);
        self::assertSame(0, $result->familyOrder);
        self::assertNull($result->variantForm);
        self::assertNull($result->regionalForm);
        self::assertNull($result->specialForm);
        self::assertNull($result->categoryForm);
        self::assertNull($result->primaryType);
        self::assertNull($result->secondaryType);
        self::assertNull($result->deletedAt);
    }

    #[Test]
    public function fromPokemonMapsGameBundleAndGeneration(): void
    {
        $pokemon = $this->buildBasePokemon();

        $result = PokemonDebugResponseFactory::fromPokemon($pokemon);

        self::assertSame('redgreenblueyellow', $result->originalGameBundle->slug);
        self::assertSame('Red/Green/Blue/Yellow', $result->originalGameBundle->name);
        self::assertSame('Rouge/Vert/Bleu/Jaune', $result->originalGameBundle->frenchName);
        self::assertSame(1, $result->originalGameBundle->orderNumber);
        self::assertNull($result->originalGameBundle->identifier);
        self::assertNull($result->originalGameBundle->deletedAt);
        self::assertSame('1', $result->originalGameBundle->generation->slug);
        self::assertSame('1', $result->originalGameBundle->generation->name);
        self::assertNull($result->originalGameBundle->generation->identifier);
        self::assertNull($result->originalGameBundle->generation->deletedAt);
    }

    #[Test]
    public function fromPokemonWithVariantFormMapsFormFields(): void
    {
        $variantForm = new VariantForm();
        $variantForm->slug = 'gender';
        $variantForm->name = 'Gender';
        $variantForm->frenchName = 'Genre';
        $variantForm->orderNumber = 1;

        $pokemon = $this->buildBasePokemon();
        $pokemon->variantForm = $variantForm;

        $result = PokemonDebugResponseFactory::fromPokemon($pokemon);

        self::assertNotNull($result->variantForm);
        self::assertSame('gender', $result->variantForm->slug);
        self::assertSame('Gender', $result->variantForm->name);
        self::assertSame('Genre', $result->variantForm->frenchName);
        self::assertSame(1, $result->variantForm->orderNumber);
        self::assertNull($result->variantForm->identifier);
        self::assertNull($result->variantForm->deletedAt);
    }

    #[Test]
    public function fromPokemonWithRegionalFormMapsFormFields(): void
    {
        $regionalForm = new RegionalForm();
        $regionalForm->slug = 'alolan';
        $regionalForm->name = 'Alolan';
        $regionalForm->frenchName = "d'Alola";
        $regionalForm->orderNumber = 2;

        $pokemon = $this->buildBasePokemon();
        $pokemon->regionalForm = $regionalForm;

        $result = PokemonDebugResponseFactory::fromPokemon($pokemon);

        self::assertNotNull($result->regionalForm);
        self::assertSame('alolan', $result->regionalForm->slug);
        self::assertSame('Alolan', $result->regionalForm->name);
        self::assertSame("d'Alola", $result->regionalForm->frenchName);
        self::assertSame(2, $result->regionalForm->orderNumber);
    }

    #[Test]
    public function fromPokemonWithSpecialFormMapsFormFields(): void
    {
        $specialForm = new SpecialForm();
        $specialForm->slug = 'mega';
        $specialForm->name = 'Mega';
        $specialForm->frenchName = 'Méga';
        $specialForm->orderNumber = 3;

        $pokemon = $this->buildBasePokemon();
        $pokemon->specialForm = $specialForm;

        $result = PokemonDebugResponseFactory::fromPokemon($pokemon);

        self::assertNotNull($result->specialForm);
        self::assertSame('mega', $result->specialForm->slug);
        self::assertSame('Mega', $result->specialForm->name);
        self::assertSame('Méga', $result->specialForm->frenchName);
        self::assertSame(3, $result->specialForm->orderNumber);
    }

    #[Test]
    public function fromPokemonWithCategoryFormMapsFormFields(): void
    {
        $categoryForm = new CategoryForm();
        $categoryForm->slug = 'starter';
        $categoryForm->name = 'Starter';
        $categoryForm->frenchName = 'Starter';
        $categoryForm->orderNumber = 4;

        $pokemon = $this->buildBasePokemon();
        $pokemon->categoryForm = $categoryForm;

        $result = PokemonDebugResponseFactory::fromPokemon($pokemon);

        self::assertNotNull($result->categoryForm);
        self::assertSame('starter', $result->categoryForm->slug);
        self::assertSame('Starter', $result->categoryForm->name);
        self::assertSame('Starter', $result->categoryForm->frenchName);
        self::assertSame(4, $result->categoryForm->orderNumber);
    }

    #[Test]
    public function fromPokemonWithPrimaryTypeMapsTypeFields(): void
    {
        $type = new Type();
        $type->slug = 'grass';
        $type->name = 'Grass';
        $type->frenchName = 'Plante';
        $type->orderNumber = 3;
        $type->color = '#78C850';

        $pokemon = $this->buildBasePokemon();
        $pokemon->primaryType = $type;

        $result = PokemonDebugResponseFactory::fromPokemon($pokemon);

        self::assertNotNull($result->primaryType);
        self::assertSame('grass', $result->primaryType->slug);
        self::assertSame('Grass', $result->primaryType->name);
        self::assertSame('Plante', $result->primaryType->frenchName);
        self::assertSame(3, $result->primaryType->orderNumber);
        self::assertSame('#78C850', $result->primaryType->color);
        self::assertNull($result->primaryType->identifier);
        self::assertNull($result->primaryType->deletedAt);
    }

    #[Test]
    public function fromPokemonWithSecondaryTypeMapsTypeFields(): void
    {
        $type = new Type();
        $type->slug = 'poison';
        $type->name = 'Poison';
        $type->frenchName = 'Poison';
        $type->orderNumber = 4;
        $type->color = '#A040A0';

        $pokemon = $this->buildBasePokemon();
        $pokemon->secondaryType = $type;

        $result = PokemonDebugResponseFactory::fromPokemon($pokemon);

        self::assertNotNull($result->secondaryType);
        self::assertSame('poison', $result->secondaryType->slug);
        self::assertSame('Poison', $result->secondaryType->name);
        self::assertSame('Poison', $result->secondaryType->frenchName);
        self::assertSame(4, $result->secondaryType->orderNumber);
        self::assertSame('#A040A0', $result->secondaryType->color);
    }

    #[Test]
    public function fromPokemonWithBankableishMapsBoolValue(): void
    {
        $pokemon = $this->buildBasePokemon();
        $pokemon->bankableish = true;

        $result = PokemonDebugResponseFactory::fromPokemon($pokemon);

        self::assertTrue($result->bankableish);
    }

    #[Test]
    public function fromPokemonWithDeletedAtFormatsAtomDate(): void
    {
        $pokemon = $this->buildBasePokemon();
        $pokemon->deletedAt = new \DateTime('2024-03-15T12:00:00+00:00');

        $result = PokemonDebugResponseFactory::fromPokemon($pokemon);

        self::assertSame('2024-03-15T12:00:00+00:00', $result->deletedAt);
    }

    #[Test]
    public function fromPokemonWithIdentifierReturnsUuidString(): void
    {
        $uuid = Uuid::fromString('550e8400-e29b-41d4-a716-446655440000');
        $pokemon = $this->buildBasePokemon();

        $reflection = new \ReflectionProperty(Pokemon::class, 'identifier');
        $reflection->setValue($pokemon, $uuid);

        $result = PokemonDebugResponseFactory::fromPokemon($pokemon);

        self::assertSame('550e8400-e29b-41d4-a716-446655440000', $result->identifier);
    }

    #[Test]
    public function fromPokemonGameBundleWithDeletedAtFormatsAtomDate(): void
    {
        $pokemon = $this->buildBasePokemon();
        $pokemon->originalGameBundle->deletedAt = new \DateTime('2024-04-20T08:00:00+00:00');

        $result = PokemonDebugResponseFactory::fromPokemon($pokemon);

        self::assertSame('2024-04-20T08:00:00+00:00', $result->originalGameBundle->deletedAt);
    }

    #[Test]
    public function fromPokemonGameBundleWithIdentifierReturnsUuidString(): void
    {
        $uuid = Uuid::fromString('6ba7b810-9dad-11d1-80b4-00c04fd430c8');
        $pokemon = $this->buildBasePokemon();

        $reflection = new \ReflectionProperty(GameBundle::class, 'identifier');
        $reflection->setValue($pokemon->originalGameBundle, $uuid);

        $result = PokemonDebugResponseFactory::fromPokemon($pokemon);

        self::assertSame('6ba7b810-9dad-11d1-80b4-00c04fd430c8', $result->originalGameBundle->identifier);
    }

    #[Test]
    public function fromPokemonGenerationWithDeletedAtFormatsAtomDate(): void
    {
        $pokemon = $this->buildBasePokemon();
        $pokemon->originalGameBundle->generation->deletedAt = new \DateTime('2024-05-10T00:00:00+00:00');

        $result = PokemonDebugResponseFactory::fromPokemon($pokemon);

        self::assertSame('2024-05-10T00:00:00+00:00', $result->originalGameBundle->generation->deletedAt);
    }

    #[Test]
    public function fromPokemonGenerationWithIdentifierReturnsUuidString(): void
    {
        $uuid = Uuid::fromString('550e8400-e29b-41d4-a716-446655440099');
        $pokemon = $this->buildBasePokemon();

        $reflection = new \ReflectionProperty(GameGeneration::class, 'identifier');
        $reflection->setValue($pokemon->originalGameBundle->generation, $uuid);

        $result = PokemonDebugResponseFactory::fromPokemon($pokemon);

        self::assertSame('550e8400-e29b-41d4-a716-446655440099', $result->originalGameBundle->generation->identifier);
    }

    #[Test]
    public function fromPokemonFormWithDeletedAtFormatsAtomDate(): void
    {
        $variantForm = new VariantForm();
        $variantForm->slug = 'gender';
        $variantForm->name = 'Gender';
        $variantForm->frenchName = 'Genre';
        $variantForm->orderNumber = 1;
        $variantForm->deletedAt = new \DateTime('2024-06-01T00:00:00+00:00');

        $pokemon = $this->buildBasePokemon();
        $pokemon->variantForm = $variantForm;

        $result = PokemonDebugResponseFactory::fromPokemon($pokemon);

        self::assertNotNull($result->variantForm);
        self::assertSame('2024-06-01T00:00:00+00:00', $result->variantForm->deletedAt);
    }

    #[Test]
    public function fromPokemonFormWithIdentifierReturnsUuidString(): void
    {
        $uuid = Uuid::fromString('550e8400-e29b-41d4-a716-446655440011');

        $variantForm = new VariantForm();
        $variantForm->slug = 'gender';
        $variantForm->name = 'Gender';
        $variantForm->frenchName = 'Genre';
        $variantForm->orderNumber = 1;

        $reflection = new \ReflectionProperty(VariantForm::class, 'identifier');
        $reflection->setValue($variantForm, $uuid);

        $pokemon = $this->buildBasePokemon();
        $pokemon->variantForm = $variantForm;

        $result = PokemonDebugResponseFactory::fromPokemon($pokemon);

        self::assertNotNull($result->variantForm);
        self::assertSame('550e8400-e29b-41d4-a716-446655440011', $result->variantForm->identifier);
    }

    #[Test]
    public function fromPokemonTypeWithDeletedAtFormatsAtomDate(): void
    {
        $type = new Type();
        $type->slug = 'grass';
        $type->name = 'Grass';
        $type->frenchName = 'Plante';
        $type->orderNumber = 3;
        $type->color = '#78C850';
        $type->deletedAt = new \DateTime('2024-07-01T00:00:00+00:00');

        $pokemon = $this->buildBasePokemon();
        $pokemon->primaryType = $type;

        $result = PokemonDebugResponseFactory::fromPokemon($pokemon);

        self::assertNotNull($result->primaryType);
        self::assertSame('2024-07-01T00:00:00+00:00', $result->primaryType->deletedAt);
    }

    #[Test]
    public function fromPokemonTypeWithIdentifierReturnsUuidString(): void
    {
        $uuid = Uuid::fromString('550e8400-e29b-41d4-a716-446655440022');

        $type = new Type();
        $type->slug = 'grass';
        $type->name = 'Grass';
        $type->frenchName = 'Plante';
        $type->orderNumber = 3;
        $type->color = '#78C850';

        $reflection = new \ReflectionProperty(Type::class, 'identifier');
        $reflection->setValue($type, $uuid);

        $pokemon = $this->buildBasePokemon();
        $pokemon->primaryType = $type;

        $result = PokemonDebugResponseFactory::fromPokemon($pokemon);

        self::assertNotNull($result->primaryType);
        self::assertSame('550e8400-e29b-41d4-a716-446655440022', $result->primaryType->identifier);
    }

    private function buildBaseGameBundle(): GameBundle
    {
        $generation = new GameGeneration();
        $generation->slug = '1';
        $generation->name = '1';

        $gameBundle = new GameBundle();
        $gameBundle->slug = 'redgreenblueyellow';
        $gameBundle->name = 'Red/Green/Blue/Yellow';
        $gameBundle->frenchName = 'Rouge/Vert/Bleu/Jaune';
        $gameBundle->orderNumber = 1;
        $gameBundle->generation = $generation;

        return $gameBundle;
    }

    private function buildBasePokemon(): Pokemon
    {
        $pokemon = new Pokemon();
        $pokemon->slug = 'bulbasaur';
        $pokemon->name = 'Bulbasaur';
        $pokemon->frenchName = 'Bulbizarre';
        $pokemon->simplifiedName = 'Bulbasaur';
        $pokemon->simplifiedFrenchName = 'Bulbizarre';
        $pokemon->formsLabel = '';
        $pokemon->formsFrenchLabel = '';
        $pokemon->nationalDexNumber = 1;
        $pokemon->family = 'bulbasaur';
        $pokemon->bankable = true;
        $pokemon->bankableish = null;
        $pokemon->iconName = 'bulbasaur';
        $pokemon->familyOrder = 0;
        $pokemon->originalGameBundle = $this->buildBaseGameBundle();
        $pokemon->variantForm = null;
        $pokemon->regionalForm = null;
        $pokemon->specialForm = null;
        $pokemon->categoryForm = null;
        $pokemon->primaryType = null;
        $pokemon->secondaryType = null;

        return $pokemon;
    }
}
