<?php
namespace primus;

use pocketmine\plugin\PluginBase;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class AdminHelp extends PluginBase {

	/** @var string[] array[player's name]question */
	private $questions = [];

	public function onEnable() {
		$this->getLogger()->info("AdminHelp is ready to serve.");
	}

	public function onDisable() {
		$this->getLogger()->info("Disabled");
	}

	public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
		switch (strtolower($command->getName())) {
			case 'ask':
				if (!($sender instanceof Player)) {
					$sender->sendMessage("Run this command in-game");
					return true;
				}
				if (count($args) < 1) {
					return false;
				}
				$ops = [new ConsoleCommandSender()];
				foreach ($this->getServer()->getOnlinePlayers() as $p) {
					if ($p->hasPermission("adminhelp.helper")) $ops[] = $p;
				}
				if (count($ops) === 1) {
					$sender->sendMessage("Altough no players who could see your question is online, your question will be registered.");
				}
				$this->questions[$sender->getName()] = implode(" ", $args);
				foreach ($ops as $rec) {
					$rec->sendMessage("--- " . $sender->getDisplayName() . " needs help ---");
					$rec->sendMessage("Question: " . $this->questions[$sender->getName()]);
				}
				$sender->sendMessage(TextFormat::GREEN . "Your question was sent.");
				return true;
				break;
			case 'reply':
				if (count($args) < 2) {
					return false;
				}
				if (($player = $this->getServer()->getPlayer($args[0])) === null) {
					$sender->sendMessage("Player '{$args[0]}' was not found on the server");
					return true;
				}
				if (!isset($this->questions[$player->getName()])) {
					$sender->sendMessage("Player doesn't need help.");
					return true;
				}
				array_shift($args);
				$reply = implode(" ", $args);
				$this->reply($sender, $player, $reply);
				return true;
			default:
				break;
		}
		return true;
	}

	public function reply(CommandSender $admin, Player $player, string $answer) {
		if (isset($this->questions[$player->getName()])) {
			$out = (($admin instanceof Player) ? $admin->getDisplayName() : $admin->getName()) . TextFormat::WHITE . " >> " . $answer;
			$player->sendMessage($out);
			$admin->sendMessage($out);
			unset($this->questions[$player->getName()]);
		}
	}
}