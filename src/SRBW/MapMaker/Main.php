<?php

declare(strict_types=1);

namespace SRBW\MapMaker;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerToggleSneakEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\player\Player;
use pocketmine\math\Vector3;

class Main extends PluginBase implements Listener {

    /** @var array<string, int> */
    private array $sneakStartTime = [];

    /** @var array<string, Vector3> */
    private array $sneakStartPos = [];

    public function onEnable(): void {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    /** Send block coordinates when player clicks a block **/
    public function onBlockClick(PlayerInteractEvent $event): void {
        $block = $event->getBlock();
        $player = $event->getPlayer();

        $player->sendMessage("§a[MapMaker] Block: §fX: {$block->getPosition()->getX()} Y: {$block->getPosition()->getY()} Z: {$block->getPosition()->getZ()}");
    }

    /** Send coordinates after sneaking for 3 seconds **/
    public function onSneak(PlayerToggleSneakEvent $event): void {
        $player = $event->getPlayer();
        $name = $player->getName();

        if ($event->isSneaking()) {
            // Sneak started
            $this->sneakStartTime[$name] = time();
            $this->sneakStartPos[$name] = $player->getPosition()->asVector3();

            // Check after 3 seconds
            $this->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use ($player) {
                $name = $player->getName();

                // If player stopped sneaking
                if (!$player->isSneaking()) return;

                // If they moved too much
                if ($player->getPosition()->distance($this->sneakStartPos[$name]) > 0.3) return;

                // If 3 seconds passed
                if (time() - $this->sneakStartTime[$name] >= 3) {
                    $pos = $player->getPosition();
                    $player->sendMessage("§b[MapMaker] Position: §fX: {$pos->getX()} Y: {$pos->getY()} Z: {$pos->getZ()}");
                }

            }), 20 * 3);

        } else {
            // Sneak ended — remove timer
            unset($this->sneakStartTime[$name], $this->sneakStartPos[$name]);
        }
    }
}
