<?php
declare(strict_types=1);

namespace taqdees\Pets\data;

class EntitySizeData {
    /** @var array Entity size data (height, width) */
    private static array $entitySizes = [
        "zombie" => [1.95, 0.6],
        "skeleton" => [1.99, 0.6], 
        "creeper" => [1.7, 0.6],
        "spider" => [0.9, 1.4],
        "cow" => [1.4, 0.9],
        "pig" => [0.9, 0.9],
        "sheep" => [1.3, 0.9],
        "chicken" => [0.7, 0.4],
        "blaze" => [1.8, 0.6],
        "enderman" => [2.9, 0.6],
        "wolf" => [0.85, 0.6],
        "ghast" => [4.0, 4.0],
        "slime" => [2.04, 2.04],
        "magma_cube" => [2.04, 2.04],
        "witch" => [1.95, 0.6],
        "villager" => [1.95, 0.6],
        "squid" => [0.8, 0.8]
    ];
    
    public static function getEntitySize(string $entityType): array {
        return self::$entitySizes[$entityType] ?? [1.8, 0.6];
    }
    
    public static function getEntityCategories(): array {
        return [
            "hostile" => ["zombie", "skeleton", "creeper", "spider", "blaze", "enderman", 
                         "ghast", "slime", "magma_cube", "witch"],
            "passive" => ["cow", "pig", "sheep", "chicken", "wolf", "villager", "squid"]
        ];
    }
    
    public static function getEntityCategory(string $entityType): string {
        $categories = self::getEntityCategories();
        
        if (in_array($entityType, $categories["hostile"])) {
            return "Hostile Mob";
        }
        
        return "Passive Mob";
    }
}