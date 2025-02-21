<?php
declare(strict_types=1);

namespace taqdees\Pets\tasks;

use pocketmine\entity\Living;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use taqdees\Pets\util\MovementHelper;

class PetFollowTask extends Task {
    private Player $player;
    private Living $pet;
    
    public function __construct(Player $player, Living $pet) {
        $this->player = $player;
        $this->pet = $pet;
    }
    
    public function onRun(): void {
        if ($this->pet->isClosed() || !$this->player->isOnline()) {
            $this->getHandler()->cancel();
            return;
        }
        if ($this->pet->getWorld() !== $this->player->getWorld()) {
            $groundPos = MovementHelper::findGroundPosition($this->player);
            $this->pet->teleport($groundPos);
            return;
        }
        
        $distance = $this->pet->getPosition()->distance($this->player->getPosition());
        if ($distance > 10) {
            $groundPos = MovementHelper::findGroundPosition($this->player->getPosition()->subtract(0, 0, 2));
            $this->pet->teleport($groundPos);
            return;
        }
        if ($distance > 2) {
            MovementHelper::movePetTowardPlayer($this->pet, $this->player);
        }
    }
}