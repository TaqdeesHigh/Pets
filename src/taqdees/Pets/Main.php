<?php
declare(strict_types=1);

namespace taqdees\Pets;

use pocketmine\event\Listener;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDespawnEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;

use taqdees\Pets\commands\PetsCommand;
use taqdees\Pets\util\EntityRegistry;
use taqdees\Pets\util\PetManager;
use taqdees\Pets\tasks\CleanupPetsTask;

class Main extends PluginBase implements Listener {
    private PetManager $petManager;
    private EntityRegistry $entityRegistry;
    private Config $petData;

    protected function onEnable(): void {
        $this->initializePlugin();
        $this->registerEventHandlers();
        $this->scheduleCleanupTask();
    }
    
    private function initializePlugin(): void {
        $this->loadConfigurations();
        $this->initializeManagers();
        $this->registerCommands();
    }

    private function loadConfigurations(): void {
        @mkdir($this->getDataFolder());
        $this->petData = new Config($this->getDataFolder() . "players.yml", Config::YAML);
    }
    
    private function initializeManagers(): void {
        $this->entityRegistry = new EntityRegistry($this);
        $this->petManager = new PetManager($this, $this->petData);
        $this->entityRegistry->registerAllEntities();
    }

    private function registerCommands(): void {
        $this->getServer()->getCommandMap()->register(
            "pets",
            new PetsCommand($this, $this->petManager, $this->entityRegistry)
        );
    }

    private function registerEventHandlers(): void {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    private function scheduleCleanupTask(): void {
        $this->getScheduler()->scheduleDelayedTask(
            new CleanupPetsTask($this->petManager),
            20
        );
    }

    public function onPlayerJoin(PlayerJoinEvent $event): void {
        $this->petManager->handlePlayerJoin($event->getPlayer());
    }
    
    public function onPlayerQuit(PlayerQuitEvent $event): void {
        $this->petManager->handlePlayerQuit($event->getPlayer());
    }
    
    public function onEntityDespawn(EntityDespawnEvent $event): void {
        $this->petManager->handleEntityDespawn($event->getEntity());
    }

    public function onEntityDamage(EntityDamageEvent $event): void {
        $entity = $event->getEntity();
        
        if (!$this->petManager->isPetEntity($entity)) {
            return;
        }

        $event->cancel();

    }
    
    public function getPetManager(): PetManager {
        return $this->petManager;
    }
    
    public function getEntityRegistry(): EntityRegistry {
        return $this->entityRegistry;
    }
}