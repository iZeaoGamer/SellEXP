<?php

/*
*   _____      _ _ 
*  / ____|    | | |
* | (___   ___| | |
*  \___ \ / _ \ | |
*  ____) |  __/ | |
* |_____/ \___|_|_|
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU Lesser General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*/

namespace SellEXP;

use pocketmine\Player;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as TF;
use onebone\economyapi\EconomyAPI;

class Main extends PluginBase implements Listener{

	public function onLoad(){
		$this->getLogger()->info("§ePlugin Loading!");
	}

	public function onEnable(){
    	$this->getLogger()->info(TF::GREEN.TF::BOLD."
 Fully enabled, by VMPE Development Team
 		");
		$files = array("sellexp.yml", "xpmessages.yml");
		foreach($files as $file){
			if(!file_exists($this->getDataFolder() . $file)) {
				@mkdir($this->getDataFolder());
				file_put_contents($this->getDataFolder() . $file, $this->getResource($file));
			}
		}
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->sellexp = new Config($this->getDataFolder() . "sellexp.yml", Config::YAML);
		$this->xpmessages = new Config($this->getDataFolder() . "xpmessages.yml", Config::YAML);
	}

	public function onDisable(){
    	$this->getLogger()->info("§cPlugin Disabled!");
  	}

	public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool{
		switch(strtolower($cmd->getName())){
			case "sellexp":
			// Checks if command is executed by console.
			// It further solves the crash problem.
			if(!($sender instanceof Player)){
				$sender->sendMessage(TF::RED . TF::BOLD ."Error: ". TF::RESET . TF::DARK_RED ."Please use this command in game!");
				return true;
				break;
			}

				/* Check if the player is permitted to use the command */
				if($sender->hasPermission("sellexp") || $sender->hasPermission("sellexp.amount") || $sender->hasPermission("sellexp.all")){
					/* Disallow non-survival mode abuse */
					if(!$sender->isSurvival()){
						$sender->sendMessage(TF::RED . TF::BOLD ."Error: ". TF::RESET . TF::DARK_RED ."Please switch back to survival mode.");
						return false;
					}
					
					/* SellEXP Hand */
					if(isset($args[0]) && strtolower($args[0]) == "amount"){
						if(!$sender->hasPermission("sellexp.amount")){
							$error_handPermission = $this->messages->get("error-nopermission-sellEXPAmount");
							$sender->sendMessage(TF::RED . TF::BOLD . "Error: " . TF::RESET . TF::RED . $error_handPermission);
							return false;
						}
						$XP = $sender->getInventory()->getXPAmount();
						$XPId = $item->getXP();
						/* Check if the player has EXP */
						if($item->getId() === 0){
							$sender->sendMessage(TF::RED . TF::BOLD ."Error: ". TF::RESET . TF::DARK_RED ."You do not have any EXP.");
							return false;
						}
						/* Recheck if the item the player is holding is a block */
						if($this->sell->get($XPAmount) == null){
							$sender->sendMessage(TF::RED . TF::BOLD ."Error: ". TF::RESET . TF::GREEN . $XPAmount->getName() . TF::DARK_RED ." cannot be sold.");
							return false;
						}
						/* Sell the item in the player's hand */
						EconomyAPI::getInstance()->addMoney($sender, $this->sell->get($XPAmount) * $XP->getCount());
						$sender->getInventory()->removeEXP($XPAmount);
						$price = $this->sell->get($XP->getInventory()) * $XP->getCount();
						$sender->sendMessage(TF::GREEN . TF::BOLD . "(!) " . TF::RESET . TF::GREEN . "$" . $price . " has been added to your account.");
						$sender->sendMessage(TF::GREEN . "Sold for " . TF::RED . "$" . $price . TF::GREEN . " (" . $XP->getCount() . " " . $XP->getName() . " at $" . $this->sell->get($XPAmount) . " each).");

					/* Sell All */
					}elseif(isset($args[0]) && strtolower($args[0]) == "all"){
						if(!$sender->hasPermission("sellexp.all")){
							$error_allPermission = $this->messages->get("error-nopermission-sellEXPAll");
							$sender->sendMessage(TF::RED . TF::BOLD . "Error: " . TF::RESET . TF::RED . $error_allPermission);
							return false;
						}
						$items = $sender->getInventory()->getContents();
						foreach($items as $item){
							if($this->sell->get($item->getId()) !== null && $this->sell->get($item->getId()) > 0){
								$price = $this->sell->get($item->getId()) * $item->getCount();
								EconomyAPI::getInstance()->addMoney($sender, $price);
								$sender->sendMessage(TF::GREEN . TF::BOLD . "(!) " . TF::RESET . TF::GREEN . "Sold for " . TF::RED . "$" . $price . TF::GREEN . " (" . $item->getCount() . " " . $item->getName() . " at $" . $this->sell->get($item->getId()) . " each).");
								$sender->getInventory()->remove($item);
							}
						}
					}elseif(isset($args[0]) && strtolower($args[0]) == "about"){
						$sender->sendMessage(TF::RED . TF::BOLD . "(!) " . TF::RESET . TF::GRAY . "This server uses the plugin, SellEXP, by VMPE Development Team");
					}else{
						$sender->sendMessage(TF::RED . TF::BOLD . "(!) " . TF::RESET . TF::DARK_RED . "Sell Online Market");
						$sender->sendMessage(TF::RED . "- " . TF::DARK_RED . "/sellexp amount " . TF::GRAY . "- Sell the item that's in your hand.");
						$sender->sendMessage(TF::RED . "- " . TF::DARK_RED . "/sellexp all " . TF::GRAY . "- Sell every possible thing in inventory.");
						return true;
					}
				}else{
					$error_permission = $this->messages->get("error-permission");
					$sender->sendMessage(TF::RED . TF::BOLD . "Error: " . TF::RESET . TF::RED . $error_permission);
				}
				break;
		}
		return true;
	}
}
