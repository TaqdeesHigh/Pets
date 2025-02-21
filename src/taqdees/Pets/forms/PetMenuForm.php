<?php
declare(strict_types=1);

namespace taqdees\Pets\forms;

use jojoe77777\FormAPI\SimpleForm;
use pocketmine\player\Player;
use taqdees\Pets\util\EntityRegistry;
use taqdees\Pets\util\PetManager;
use taqdees\Pets\data\EntitySizeData;

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
            $namingForm = new PetNamingForm($this->petManager, $entityType);
            $namingForm->send($player);
        });
        
        $form->setTitle("Pets Menu");
        $form->setContent("Select a pet to follow you:");
        
        $this->addPetButtons($form);

        $form->addButton("Remove Pet\nNo Pet");
        
        $form->sendToPlayer($player);
    }
    
    private function addPetButtons(SimpleForm $form): void {
        foreach ($this->entityRegistry->getEntityTypes() as $entityType) {
            $category = EntitySizeData::getEntityCategory($entityType);
            $form->addButton(ucfirst($entityType) . "\n" . $category);
        }
    }
}