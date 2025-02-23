<?php
declare(strict_types=1);

namespace taqdees\Pets\util;

use pocketmine\entity\Living;
use pocketmine\math\Vector3;
use pocketmine\player\Player;

class MovementHelper {
    
    public static function findGroundPosition(Vector3 $pos, ?Player $player = null): Vector3 {
        $world = $player ? $player->getWorld() : null;
        if (!$world) {
            return $pos;
        }
        
        $x = $pos->x;
        $z = $pos->z;
        
        // Start from player's position
        $y = $pos->y;
        
        // If in air, start searching from a bit below the player
        if ($y > 0) {
            // Search for ground below player
            while ($y > 0) {
                $block = $world->getBlockAt((int)$x, (int)$y, (int)$z);
                $blockBelow = $world->getBlockAt((int)$x, (int)$y - 1, (int)$z);
                
                // If current block is air and block below is solid, we found ground
                if (!$block->isSolid() && $blockBelow->isSolid()) {
                    return new Vector3($x, $y, $z);
                }
                
                $y--;
            }
        }
        
        // If no ground found or player below world, return spawn height
        return new Vector3($x, $world->getSpawnLocation()->y, $z);
    }
    
    public static function movePetTowardPlayer(Living $pet, Player $player): void {
        // Calculate direction
        $directionVector = $player->getPosition()->subtractVector($pet->getPosition());
        
        $speed = 0.75; 
        if ($directionVector->lengthSquared() > 0) {
            $directionVector = $directionVector->normalize()->multiply($speed);
            
            // Direct motion setting
            $pet->setMotion(new Vector3(
                $directionVector->x,
                $pet->getMotion()->y, // Keep current Y motion
                $directionVector->z
            ));
        }
        
        if (self::shouldPetClimb($pet, $player)) {
            $pet->setMotion(new Vector3(
                $directionVector->x,
                0.5,
                $directionVector->z
            ));
        }
        
        self::updatePetRotation($pet, $player);
    }
    
    private static function shouldPetClimb(Living $pet, Player $player): bool {
        $petPos = $pet->getPosition();
        
        // Get the block directly in front of the pet
        $direction = $player->getPosition()->subtractVector($petPos)->normalize();
        $frontPos = $petPos->add($direction->x, 0, $direction->z);
        
        // Check blocks
        $blockInFront = $pet->getWorld()->getBlock($frontPos);
        $blockAboveInFront = $pet->getWorld()->getBlock($frontPos->add(0, 1, 0));
        $blockTwoAbove = $pet->getWorld()->getBlock($petPos->add(0, 2, 0));
        
        // Return true if there's a solid block in front and space to jump
        return $blockInFront->isSolid() 
            && !$blockAboveInFront->isSolid() 
            && !$blockTwoAbove->isSolid() 
            && $pet->getMotion()->y <= 0;
    }
    
    private static function updatePetRotation(Living $pet, Player $player): void {
        // Calculate horizontal rotation (yaw)
        $dx = $player->getPosition()->x - $pet->getPosition()->x;
        $dz = $player->getPosition()->z - $pet->getPosition()->z;
        $yaw = atan2($dz, $dx) * 180 / M_PI - 90;
        
        // Calculate vertical rotation (pitch)
        $dy = $player->getPosition()->y + $player->getEyeHeight() - ($pet->getPosition()->y + $pet->getEyeHeight());
        $horizontalDistance = sqrt($dx * $dx + $dz * $dz);
        $pitch = -atan2($dy, $horizontalDistance) * 180 / M_PI;
        
        // Update pet rotation
        $pet->setRotation($yaw, $pitch);
    } // Math
}