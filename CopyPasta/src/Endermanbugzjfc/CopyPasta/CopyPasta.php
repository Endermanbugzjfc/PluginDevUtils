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

namespace Endermanbugzjfc\CopyPasta;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Utils;
use pocketmine\command\Command;
use pocketmine\utils\TextFormat;
use pocketmine\plugin\PluginBase;
use pocketmine\lang\TextContainer;
use pocketmine\command\CommandSender;
use function round;
use function is_dir;
use function substr;
use function strlen;
use function dirname;
use function strtolower;

class CopyPasta extends PluginBase {

    public const CURL = 'curl';
    public const STREAM = 'stream';
    private static $instance;

    /**
     * @var string|TextContainer
     */
    private $cmd_notfound_msg;

    protected static function getTaskCallback(CommandSender $sender) : \Closure {
        return function(bool $succeed, float $time) use ($sender) : void {
            if ($succeed) $msg = TextFormat::BOLD . TextFormat::GREEN . 'Operation succeed';
            else $msg = TextFormat::BOLD . TextFormat::RED . 'Operation failed';
            $msg .= ' ' . TextFormat::GRAY . '(' . round($time, 2) . 's)';
            $sender->sendMessage($msg);
        };
    }

    protected static function validatePath(CommandSender $sender, string $path) : ?string {
        if (is_dir(dirname(Server::getInstance()->getDataPath() . $path))) return $path;
        $sender->sendMessage(TextFormat::BOLD . TextFormat::RED . 'Destination directory does not exists');
        return null;
    }

    /**
     * @return string|TextContainer
     */
    public function getCommandPermissionLackMessage() {
        return $this->cmd_notfound_msg;
    }

    public function onLoad() : void {
        self::$instance = $this;
    }

    public function onEnable() : void {
        $this->setCommandPermissionLackMessage($this->getServer()->getLanguage()->translateString(TextFormat::RED . "%commands.generic.notfound"));
    }

    /**
     * @param string|TextContainer $cmd_notfound_msg
     */
    public function setCommandPermissionLackMessage($cmd_notfound_msg) : void {
        $this->cmd_notfound_msg = $cmd_notfound_msg;
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool {
        if ($command->getName() !== 'wget') return true;
        if ($sender instanceof Player and !$sender->hasPermission('copypasta.wget')) {
            $sender->sendMessage($this->getCommandPermissionLackMessage());
            return true;
        }
        if (!isset($args[0])) return false;
        switch (strtolower($args[0])) {

            case self::CURL:
            case 'false':
                if (!$sender->hasPermission('copypasta.wget.curl')) {
                    $sender->sendMessage(TextFormat::BOLD . TextFormat::RED . 'Lacking permission "copypasta.wget.curl"');
                    break;
                }
                if (!isset($args[1])) return false;
                $this->wget(self::CURL, $args[1], static::validatePath($sender, static::trimPath($args[2] ?? null)), static::getTaskCallback($sender));
                break;

            case self::STREAM:
            case 'true':
                if (!$sender->hasPermission('copypasta.wget.stream')) {
                    $sender->sendMessage(TextFormat::BOLD . TextFormat::RED . 'Lacking permission "copypasta.wget.stream"');
                    break;
                }
                if (!isset($args[1])) return false;
                $this->wget(self::STREAM, $args[1], static::validatePath($sender, static::trimPath($args[2] ?? null)), static::getTaskCallback($sender));
                break;

            default:
                $this->wget(self::CURL, $args[0], static::validatePath($sender, static::trimPath($args[1] ?? null)), static::getTaskCallback($sender));
                break;
        }
        return true;
    }

    public static function wget(string $type, string $url, ?string $path = null, ?\Closure $callback = null) : void {
        if (!isset($path)) return;
        Server::getInstance()->getAsyncPool()->submitTask(new AsyncGrabTask($type, $url, Server::getInstance()->getDataPath() . $path, $callback));
    }

    public static function trimPath(?string $path = null) : string {
        if (!isset($path)) $path = static::getInstance()->getDataFolder() . 'download.txt';
        $serverpath = Server::getInstance()->getDataPath();
        if (substr($path, 0, strlen($serverpath)) === $serverpath) $path = substr($path, strlen($serverpath) - 1);
        return Utils::cleanPath($path);
    }

    public static function getInstance() : self {
        return self::$instance;
    }
}