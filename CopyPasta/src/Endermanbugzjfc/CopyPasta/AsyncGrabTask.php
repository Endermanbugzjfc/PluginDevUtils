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
use pocketmine\Server;
use pocketmine\utils\Internet;
use pocketmine\scheduler\AsyncTask;
use function fopen;
use function microtime;
use function file_put_contents;
use function stream_copy_to_stream;

class AsyncGrabTask extends AsyncTask {

    private $type;
    private $url;
    private $path;

    /**
     * AsyncGrabTask constructor.
     * @param string $type
     * @param string $url
     * @param string $path
     * @param \Closure|null $callback Compatible with <code>function(bool $succeed, float $time) {}</code>
     */
    public function __construct(string $type, string $url, string $path, ?\Closure $callback) {
        $this->type = $type;
        $this->url = $url;
        $this->path = $path;
        $this->storeLocal([$callback]);
    }

    public function onRun() : void {
        $time = microtime(true);
        switch ($this->type) {

            case CopyPasta::CURL:
                $data = Internet::getURL($this->url);
                if ($data === false) {
                    $result = false;
                    break;
                }
                file_put_contents($this->path, $data);
                break;

            case CopyPasta::STREAM:
                $path = $this->path;
                file_put_contents($path, '');
                $source = @fopen($path, 'r');
                $dest = @fopen($path, 'w+');
                if (
                    $source === false or
                    $dest === false or
                    $source === null or
                    $dest === null
                ) {
                    @fclose($source);
                    @fclose($dest);
                    $result = false;
                    break;
                }
                $result = stream_copy_to_stream($source, $dest);
                @fclose($source);
                @fclose($dest);
                if ($result === false) {
                    $result = false;
                    break;
                }
                break;

            default:
                $result = false;
                break;
        }
        $this->setResult([$result ?? true, microtime(true) - $time]);
    }

    public function onCompletion(Server $server) : void {
        $result = $this->getResult();
        $fridge = $this->fetchLocal(); // Don't judge variable name...
        $fridge[0]((bool)$result[0], (float)$result[1]);
    }
}