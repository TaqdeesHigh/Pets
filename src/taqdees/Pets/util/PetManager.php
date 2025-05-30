<?php
declare(strict_types=1);

namespace taqdees\Pets\util;

use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\entity\Location;
use pocketmine\entity\Zombie;
use pocketmine\entity\Squid;
use pocketmine\entity\Villager;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use taqdees\Pets\Main;
use taqdees\Pets\tasks\PetFollowTask;
use taqdees\Pets\tasks\RespawnPetTask;

class PetManager {
    private Main $plugin;
    private Config $petData;
    private array $playerPets = [];
    private array $petEntities = [];
    
    public function __construct(Main $plugin, Config $petData) {
        $this->plugin = $plugin;
        $this->petData = $petData;
    }
    
    public function cleanupOldPets(): void {
        foreach ($this->plugin->getServer()->getWorldManager()->getWorlds() as $world) {
            foreach ($world->getEntities() as $entity) {
                if ($entity instanceof Living) {
                    $nameTag = $entity->getNameTag();
                    if ($nameTag !== "" && strpos($nameTag, TextFormat::AQUA) !== false) {
                        $entity->kill();
                        $entity->close();
                        $this->plugin->getLogger()->debug("Removed old pet entity: " . $nameTag);
                    }
                }
            }
        }
    }
    
    public function spawnPet(Player $player, string $entityType, string $customName): void {
        if ($this->plugin->isWorldBlacklisted($player->getWorld()->getFolderName())) {
            return;
        }
        
        $registry = $this->plugin->getEntityRegistry();
        
        if (!$registry->entityTypeExists($entityType)) {
            $player->sendMessage(TextFormat::RED . "Unknown entity type: $entityType");
            return;
        }
        
        $this->removeExistingPet($player);
        
        try {
            $entity = $this->createPetEntity($player, $entityType);
            $this->setupPet($player, $entity, $entityType, $customName);
        } catch (\Throwable $e) {
            $this->plugin->getLogger()->error("Failed to spawn pet: " . $e->getMessage());
            $player->sendMessage(TextFormat::RED . "Failed to spawn pet. Please try another type.");
        }
    }
    
    private function removeExistingPet(Player $player): void {
        $playerName = $player->getName();
        if (isset($this->playerPets[$playerName])) {
            $this->playerPets[$playerName]->flagForDespawn();
            unset($this->playerPets[$playerName]);
        }
    }
    
    private function createPetEntity(Player $player, string $entityType): Entity {
        $pos = $player->getPosition()->add(0, 0, 1);
        $location = new Location(
            $pos->x,
            $pos->y,
            $pos->z,
            $player->getWorld(),
            $player->getLocation()->getYaw(),
            $player->getLocation()->getPitch()
        );
        
        if ($entityType === "zombie") {
            return new Zombie($location);
        } elseif ($entityType === "villager") {
            return new Villager($location);
        } elseif ($entityType === "squid") {
            return new Squid($location);
        } else {
            $customClass = "taqdees\\Pets\\entity\\pets\\" . ucfirst($entityType) . "Pet";
            return new $customClass($location);
        }
    }
    
    private function setupPet(Player $player, Living $entity, string $entityType, string $customName): void {
        $entity->setNameTag(TextFormat::GRAY . $customName);
        $entity->setNameTagAlwaysVisible(true);

        $entity->spawnToAll();
        $this->playerPets[$player->getName()] = $entity;
        $this->petEntities[] = $entity;
        
        $this->savePetData($player, $entityType, $customName);

        $this->plugin->getScheduler()->scheduleRepeatingTask(
            new PetFollowTask($player, $entity),
            1
        );
        
        $player->sendMessage(TextFormat::GREEN . "Pet spawned: " . $customName);
    }
    
    private function savePetData(Player $player, string $entityType, string $customName): void {
        $this->petData->set($player->getName(), [
            "type" => $entityType,
            "active" => true,
            "customName" => $customName
        ]);
        $this->petData->save();
    }
    
    public function removePet(Player $player): void {
        $playerName = $player->getName();
        
        if (isset($this->playerPets[$playerName])) {
            $this->playerPets[$playerName]->flagForDespawn();
            unset($this->playerPets[$playerName]);

            $this->petData->remove($playerName);
            $this->petData->save();
            
            $player->sendMessage(TextFormat::GREEN . "Pet removed!");
        }
    }
    
    public function removePetSilently(Player $player): void {
        $playerName = $player->getName();
        
        if (isset($this->playerPets[$playerName])) {
            $this->playerPets[$playerName]->flagForDespawn();
            unset($this->playerPets[$playerName]);
        }
    }
    
    public function handlePlayerJoin(Player $player): void {
        if ($this->plugin->isWorldBlacklisted($player->getWorld()->getFolderName())) {
            return;
        }
        
        $playerName = $player->getName();
        
        if ($this->petData->exists($playerName)) {
            $petData = $this->petData->get($playerName);
            if (isset($petData["active"]) && $petData["active"] === true) {
                $this->cleanupPlayerPets($player);

                $this->plugin->getScheduler()->scheduleDelayedTask(
                    new RespawnPetTask($this, $player, $petData["type"], $petData["customName"] ?? ""),
                    20
                );
            }
        }
    }
    
    private function cleanupPlayerPets(Player $player): void {
        foreach ($player->getWorld()->getEntities() as $entity) {
            if ($entity instanceof Living) {
                $nameTag = $entity->getNameTag();
                if ($nameTag !== "" && strpos($nameTag, TextFormat::AQUA) !== false) {
                    $entity->kill();
                    $entity->close();
                }
            }
        }

        $playerName = $player->getName();
        if (isset($this->playerPets[$playerName])) {
            $this->playerPets[$playerName]->kill();
            $this->playerPets[$playerName]->close();
            unset($this->playerPets[$playerName]);
        }
    }
    
    public function handlePlayerQuit(Player $player): void {
        $playerName = $player->getName();
        
        if (isset($this->playerPets[$playerName])) {
            $this->playerPets[$playerName]->flagForDespawn();
            unset($this->playerPets[$playerName]);
        }
    }
    
    public function handleEntityDespawn(Entity $entity): void {
        if ($entity instanceof Living) {
            $key = array_search($entity, $this->petEntities, true);
            if ($key !== false) {
                unset($this->petEntities[$key]);
            }

            foreach ($this->playerPets as $playerName => $pet) {
                if ($pet === $entity) {
                    unset($this->playerPets[$playerName]);
                    break;
                }
            }
        }
    }
    
    public function isPetEntity(Entity $entity): bool {
        return in_array($entity, $this->petEntities, true);
    }
    
    public function getPlayerPet(string $playerName): ?Living {
        return $this->playerPets[$playerName] ?? null;
    }
}