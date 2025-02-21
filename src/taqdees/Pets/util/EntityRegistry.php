<?php 
declare(strict_types=1);

namespace taqdees\Pets\util;

use pocketmine\entity\Entity;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\entity\Living;
use pocketmine\entity\Zombie;
use pocketmine\entity\Squid;
use pocketmine\entity\Villager;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\world\World;
use taqdees\Pets\Main;
use taqdees\Pets\data\EntitySizeData;

class EntityRegistry {
    private Main $plugin;
    private array $entityMap = [];
    
    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }
    
    public function registerAllEntities(): void {
        $this->registerNativeEntities();
        $this->registerCustomEntities();
    }
    
    private function registerNativeEntities(): void {
        $nativeEntities = [
            "zombie" => [Zombie::class, EntityIds::ZOMBIE],
            "villager" => [Villager::class, EntityIds::VILLAGER],
            "squid" => [Squid::class, EntityIds::SQUID]
        ];
        
        foreach ($nativeEntities as $name => [$className, $entityId]) {
            $this->registerNativeEntity($name, $className, $entityId);
        }
    }
    
    private function registerCustomEntities(): void {
        $customEntities = [
            "zombie" => EntityIds::ZOMBIE,
            "skeleton" => EntityIds::SKELETON,
            "creeper" => EntityIds::CREEPER,
            "spider" => EntityIds::SPIDER,
            "cow" => EntityIds::COW,
            "pig" => EntityIds::PIG,
            "sheep" => EntityIds::SHEEP,
            "chicken" => EntityIds::CHICKEN,
            "blaze" => EntityIds::BLAZE,
            "enderman" => EntityIds::ENDERMAN,
            "wolf" => EntityIds::WOLF,
            "ghast" => EntityIds::GHAST,
            "slime" => EntityIds::SLIME,
            "magma_cube" => EntityIds::MAGMA_CUBE,
            "witch" => EntityIds::WITCH,
            "villager" => EntityIds::VILLAGER,
            "squid" => EntityIds::SQUID
        ];
        
        foreach ($customEntities as $name => $entityId) {
            $this->registerCustomEntity($name, $entityId);
        }
    }
    
    private function registerNativeEntity(string $name, string $className, string $entityId): void {
        if (class_exists($className)) {
            $this->entityMap[$name] = $entityId;
        } else {
            $this->plugin->getLogger()->debug("Entity class $className not found, skipping $name");
        }
    }
    
    private function registerCustomEntity(string $name, string $entityId): void {
        $sizes = EntitySizeData::getEntitySize($name);
        $height = $sizes[0];
        $width = $sizes[1];
        
        $className = ucfirst($name) . "Pet";
        $fullClassName = "taqdees\\Pets\\entity\\pets\\{$className}";
        
        if (!class_exists($fullClassName, false)) {
            $this->defineCustomEntityClass($fullClassName, $name, $height, $width, $entityId);
        }
        
        EntityFactory::getInstance()->register($fullClassName, function(World $world, CompoundTag $nbt) use ($fullClassName): Entity {
            return new $fullClassName(EntityDataHelper::parseLocation($nbt, $world), $nbt);
        }, [$entityId]);
        
        $this->entityMap[$name] = $entityId;
    }
    
    private function defineCustomEntityClass(string $fullClassName, string $name, float $height, float $width, string $entityId): void {
        eval("
            namespace taqdees\\Pets\\entity\\pets;
            
            use pocketmine\\entity\\EntitySizeInfo;
            use pocketmine\\entity\\Location;
            use pocketmine\\nbt\\tag\\CompoundTag;
            use pocketmine\\world\\World;
            use taqdees\\Pets\\entity\\BasePet;
            
            class " . ucfirst($name) . "Pet extends BasePet {
                protected float \$height = {$height};
                protected float \$width = {$width};
                
                public static function getNetworkTypeId(): string {
                    return \"{$entityId}\";
                }
                
                public function getName(): string {
                    return \"" . ucfirst($name) . "\";
                }
                
                public static function create(World \$world, CompoundTag \$nbt): self {
                    return new self(\\pocketmine\\entity\\EntityDataHelper::parseLocation(\$nbt, \$world), \$nbt);
                }
            }
        ");
    }
    
    public function getEntityTypes(): array {
        return array_keys($this->entityMap);
    }
    
    public function getEntityId(string $entityType): ?string {
        return $this->entityMap[$entityType] ?? null;
    }
    
    public function entityTypeExists(string $entityType): bool {
        return isset($this->entityMap[$entityType]);
    }
}