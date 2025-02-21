<?php
declare(strict_types=1);

namespace taqdees\Pets\tasks;

use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use taqdees\Pets\util\PetManager;

class RespawnPetTask extends Task {
    private PetManager $petManager;
    private Player $player;
    private string $petType;
    private string $customName;
    
    public function __construct(PetManager $petManager, Player $player, string $petType, string $customName) {
        $this->petManager = $petManager;
        $this->player = $player;
        $this->petType = $petType;
        $this->customName = $customName;
    }
    
    public function onRun(): void {
        if ($this->player->isOnline()) {
            $this->petManager->spawnPet(
                $this->player, 
                $this->petType, 
                $this->customName ?: ($this->player->getName() . "'s " . ucfirst($this->petType))
            );
        }
    }
}