<?php
declare(strict_types=1);

namespace taqdees\Pets\forms;

use taqdees\Pets\lib\jojoe77777\FormAPI\SimpleForm;

use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use taqdees\Pets\util\EntityRegistry;
use taqdees\Pets\util\PetManager;

class PetMenuForm {
    private PetManager $petManager;
    private EntityRegistry $entityRegistry;
    
    public function __construct(PetManager $petManager, EntityRegistry $entityRegistry) {
        $this->petManager = $petManager;
        $this->entityRegistry = $entityRegistry;
    }
    
    public function send(Player $player): void {
        $form = new SimpleForm(function(Player $player, ?int $data) {
            if ($data === null) {
                return;
            }
            
            $entityTypes = $this->entityRegistry->getEntityTypes();
            if ($data >= count($entityTypes)) {
                $this->petManager->removePet($player);
                return;
            }

            $entityType = $entityTypes[$data];

            if (!$player->hasPermission("pets.type." . $entityType)) {
                $player->sendMessage(TextFormat::RED . "You don't have this pet unlocked!");
                return;
            }

            $namingForm = new PetNamingForm($this->petManager, $entityType);
            $namingForm->send($player);
        });
        
        $form->setTitle("Pets Menu");
        $form->setContent("Select a pet to follow you:");
        
        $this->addPetButtons($form, $player);
        $form->addButton("Remove Pet");
        
        $form->sendToPlayer($player);
    }
    
    private function addPetButtons(SimpleForm $form, Player $player): void {
        foreach ($this->entityRegistry->getEntityTypes() as $entityType) {
            $permission = "pets.type." . $entityType;
            $hasPermission = $player->hasPermission($permission);
            
            $buttonText = ucfirst($entityType);
            if (!$hasPermission) {
                $buttonText = TextFormat::RED . $buttonText;
            } else {
                $buttonText = TextFormat::GREEN . $buttonText;
            }
            
            $form->addButton($buttonText);
        }
    }
}