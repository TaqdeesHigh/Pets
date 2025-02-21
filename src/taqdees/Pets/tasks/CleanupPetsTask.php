<?php
declare(strict_types=1);

namespace taqdees\Pets\tasks;

use pocketmine\scheduler\Task;
use taqdees\Pets\util\PetManager;

class CleanupPetsTask extends Task {
    private PetManager $petManager;
    
    public function __construct(PetManager $petManager) {
        $this->petManager = $petManager;
    }
    
    public function onRun(): void {
        $this->petManager->cleanupOldPets();
    }
}