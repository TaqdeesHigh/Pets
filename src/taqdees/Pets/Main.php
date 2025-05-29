<?php
declare(strict_types=1);

namespace taqdees\Pets;

use pocketmine\event\Listener;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDespawnEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerMoveEvent;
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
    private Config $config;
    private array $playerWorlds = [];

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
        $this->saveDefaultConfig();
        $this->config = $this->getConfig();
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
        $player = $event->getPlayer();
        $worldName = $player->getWorld()->getFolderName();
        $this->playerWorlds[$player->getName()] = $worldName;
        
        if (!$this->isWorldBlacklisted($worldName)) {
            $this->petManager->handlePlayerJoin($player);
        }
    }
    
    public function onPlayerQuit(PlayerQuitEvent $event): void {
        $player = $event->getPlayer();
        $this->petManager->handlePlayerQuit($player);
        unset($this->playerWorlds[$player->getName()]);
    }
    
    public function onPlayerMove(PlayerMoveEvent $event): void {
        $player = $event->getPlayer();
        $playerName = $player->getName();
        $currentWorld = $player->getWorld()->getFolderName();
        if (!isset($this->playerWorlds[$playerName]) || $this->playerWorlds[$playerName] !== $currentWorld) {
            $previousWorld = $this->playerWorlds[$playerName] ?? null;
            $this->playerWorlds[$playerName] = $currentWorld;
            if ($previousWorld !== null) {
                $this->handleWorldChange($player, $currentWorld);
            }
        }
    }
    
    private function handleWorldChange(Player $player, string $newWorld): void {
        if ($this->isWorldBlacklisted($newWorld)) {
            $this->petManager->removePetSilently($player);
            $player->sendMessage(TextFormat::YELLOW . "Pets are disabled in this world.");
        } else {
            $this->getScheduler()->scheduleDelayedTask(new class($this->petManager, $player) extends \pocketmine\scheduler\Task {
                private PetManager $petManager;
                private Player $player;
                
                public function __construct(PetManager $petManager, Player $player) {
                    $this->petManager = $petManager;
                    $this->player = $player;
                }
                
                public function onRun(): void {
                    if ($this->player->isOnline()) {
                        $this->petManager->handlePlayerJoin($this->player);
                    }
                }
            }, 40);
        }
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
    
    public function isWorldBlacklisted(string $worldName): bool {
        $blacklistedWorlds = $this->config->get("blacklisted-worlds", []);
        return in_array($worldName, $blacklistedWorlds, true);
    }
    
    public function getPetManager(): PetManager {
        return $this->petManager;
    }
    
    public function getEntityRegistry(): EntityRegistry {
        return $this->entityRegistry;
    }
    
    public function getPluginConfig(): Config {
        return $this->config;
    }
}