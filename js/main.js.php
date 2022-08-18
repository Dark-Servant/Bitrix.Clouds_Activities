<?
define('SESSION_CONTANTS', true);

$setting = require dirname(__DIR__) . '/configs/settings.php';
$activities = BPActivity::getUnits();

$places = [
    '/\/\*lang_values\*\//iu' => 'var LANG_VALUES = ' . json_encode($langValues) . ';',
    '/\/\*server_constants\*\//iu' => 'var SERVER_CONSTANTS = ' . json_encode($_SESSION['CONST_LIST'] ?? []) . ';',
    '/\/\*activities\*\//iu' => 'var activities = ' . (empty($activities) ? '{}' : json_encode($activities)) . ';'
];
ob_start();
include __DIR__ . '/vue.components.js';
$places['/\/\*vue\.components\*\//iu'] = ob_get_clean();

ob_start();
include __DIR__ . '/vue.main.js';
$places['/\/\*vue\.main\*\//iu'] = ob_get_clean();

ob_start();
include __DIR__ . '/main.js';
$data = ob_get_clean();
foreach ($places as $regEx => $value) {
    $data = preg_replace($regEx, $value, $data);
}

header('Content-Type: application/javascript; charset=utf-8');
echo $data;?>
