<?php
declare(strict_types=1);
namespace taqdees\Pets\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginOwned;
use pocketmine\utils\TextFormat;
use taqdees\Pets\forms\PetMenuForm;
use taqdees\Pets\Main;
use taqdees\Pets\util\EntityRegistry;
use taqdees\Pets\util\PetManager;

class PetsCommand extends Command implements PluginOwned {
    private Main $owningPlugin;
    private PetManager $petManager;
    private EntityRegistry $entityRegistry;
   
    public function __construct(Main $plugin, PetManager $petManager, EntityRegistry $entityRegistry) {
        parent::__construct(
            "pets",
            "Open the pets menu",
            "/pets",
            ["pet"]
        );
        $this->setPermission("pets.command");
        
        $this->owningPlugin = $plugin;
        $this->petManager = $petManager;
        $this->entityRegistry = $entityRegistry;
    }
    
    public function getOwningPlugin(): Plugin {
        return $this->owningPlugin;
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::RED . "This command can only be used in-game.");
            return true;
        }
        
        if (!$this->testPermission($sender)) {
            return true;
        }
        
        $petMenuForm = new PetMenuForm($this->petManager, $this->entityRegistry);
        $petMenuForm->send($sender);
        return true;
    }
}