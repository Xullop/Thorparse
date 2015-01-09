 <?php 
error_reporting(E_ALL);
 
$time_start = microtime(true);

include_once("autoloader.class.php");
autoloader::init();

\einherjar\Main::init();

$time_end = microtime(true);
$time = $time_end - $time_start;

?>