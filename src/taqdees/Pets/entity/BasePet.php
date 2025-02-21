<?php
declare(strict_types=1);
namespace taqdees\Pets\entity;

use pocketmine\entity\Living;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Location;
use pocketmine\nbt\tag\CompoundTag;

abstract class BasePet extends Living {
    protected float $height = 1.9;
    protected float $width = 0.6;
    
    public function __construct(Location $location, ?CompoundTag $nbt = null) {
        parent::__construct($location, $nbt);
    }
    
    protected function getInitialSizeInfo(): EntitySizeInfo {
        return new EntitySizeInfo($this->height, $this->width);
    }
    
    abstract public static function getNetworkTypeId(): string;
    
    public function getName(): string {
        return $this->getNameTag() ?: static::class;
    }
}