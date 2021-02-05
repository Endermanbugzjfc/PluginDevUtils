<?php

/*

     					_________	  ______________		
     				   /        /_____|_           /
					  /————/   /        |  _______/_____    
						  /   /_     ___| |_____       /
						 /   /__|    ||    ____/______/
						/   /    \   ||   |   |   
					   /__________\  | \   \  |
					       /        /   \   \ |
						  /________/     \___\|______
						                   |         \ 
							  PRODUCTION   \__________\	

							   翡翠出品 。 正宗廢品  
 
*/

declare(strict_types=1);
namespace Endermanbugzjfc\ChunkSee;

use pocketmine\{
	Player,
	plugin\PluginBase,
	command\Command,
	command\CommandSender,
	math\Vector3
};

use function strtolower;

final class ChunkSee extends PluginBase {

	public function onCommand(CommandSender $p, Command $cmd, string $alias, array $args) : bool {
		switch (strtolower($cmd->getName())) {
			case 'chunkcoords':
				$sp = isset($args[0]) ? $this->getServer()->getPlayer($args[0]) : ($p instanceof Player ? $p : null);
				if (!isset($sp)) return true;
				$chunk = $sp->getLevel()->getChunkAtPosition($sp->asPosition());
				$p->sendMessage('Level(Dir): ' . $sp->getLevel()->getFolderName() . ' | ' . 'Chunk X: ' . $chunk->getX() . ' | ' . 'Chunk Z: ' . $chunk->getZ());
				break;

			case 'gotochunk':
				if (!$p instanceof Player) return true;
				if (!(isset($args[0]) and isset($args[1]))) return true;
				$p->teleport(new Vector3((int)$args[0], (int)$args[1]));
				break;
		}
		return true;
	}
	
}
