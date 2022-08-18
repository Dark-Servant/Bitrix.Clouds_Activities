<?
error_reporting(0);

include_once dirname(__DIR__) . '/vendor/autoload.php';

spl_autoload_register('infoservice_auotload', false, true);

function infoservice_auotload($className)
{
    $className = strtolower($className);    
    foreach ([dirname(__DIR__) . '/lib/helpers/#classname#.class.php'] as $unitPath)  {

        $file = str_replace('#classname#', $className, $unitPath);
        if (!file_exists($file)) continue;

        require_once $file;
        return;
    }
}

require_once __DIR__ . '/constants/main.php';
require_once dirname(__DIR__) . '/lang/' . LANG . '.php';

return [];