<?php
declare(strict_types=1);

namespace taqdees\Pets\forms;

use jojoe77777\FormAPI\CustomForm;
use pocketmine\player\Player;
use taqdees\Pets\util\PetManager;

class PetNamingForm {
    private PetManager $petManager;
    private string $entityType;
    
    public function __construct(PetManager $petManager, string $entityType) {
        $this->petManager = $petManager;
        $this->entityType = $entityType;
    }
    
    public function send(Player $player): void {
        $form = new CustomForm(function(Player $player, ?array $data) {
            if ($data === null) {
                return;
            }

            $customName = trim($data[0] ?? "");
            if (empty($customName)) {
                $customName = $player->getName() . "'s " . ucfirst($this->entityType);
            }

            $this->petManager->spawnPet($player, $this->entityType, $customName);
        });
        
        $form->setTitle("Name Your " . ucfirst($this->entityType));
        $form->addInput("Enter a name for your pet:", "Enter pet name here...");
        $form->sendToPlayer($player);
    }
}