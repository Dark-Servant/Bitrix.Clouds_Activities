<?

class BPActivity
{
    protected $foundActivity;
    protected $isChecked;
    protected $values;

    /**
     * Читает содержимое папки bx/bizproc/activities и возвращает данные по каждому действию,
     * если для действия создана папка и в ней содержится файл params.php с информацией
     * о действии. По каждому дейсвтию будет возвращен массив с параметрами:
     *     - path. Полный путь к папке действия, включая и само название папки действия;
     *     - params. параметры действия, прочитанные из файла params.php;
     *     - code. Код действия, составленный по имени папки действия, название папки
     *     приведено тут к camelCase-форме.
     * 
     * @yield
     */
    protected static function ativityData()
    {
        global $langValues;

        foreach (glob(dirname(__DIR__) . '/../bx/bizproc/activities/*') as $path) {
            if (!is_dir($path) || !file_exists($path . '/params.php'))
                continue;

            $folder = basename($path);
            $code = preg_replace_callback(
                        '/(\w)\W(\w)/',
                        function($parts) { return $parts[1] . strtoupper($parts[2]); },
                        $folder
                    );
            yield [
                'path' => $path,
                'params' => require $path . '/params.php',
                'code' => $code,
            ];
        }
    }

    /**
     * Возвращает список всех доступных действий в системе
     * 
     * @return array
     */
    public static function getUnits()
    {
        $params = [];
        foreach (self::ativityData() as $activity) {
            $params[$activity['code']] = $activity['params'];
        }
        return $params;
    }

    /**
     * Создает экземпляр класса для работы с конкретным действием по переданному симольному коду
     * действия. По коду будет сделан поиск подходящего действия в bx/bizproc/activities. Если найти
     * не удастся, то произойдет исключение с информацией об этом
     * 
     * @param string $code - символьный код действия
     * @throw
     */
    public function __construct(string $code)
    {
        global $langValues;

        $this->foundActivity = null;
        $this->isChecked = false;
        foreach (self::ativityData() as $activity) {
            if ($activity['code'] == $code) {
                $this->foundActivity = $activity;
                break;
            }
        }
        if (!$this->foundActivity)
            throw new Exception($langValues['ERROR_ACTIVITY_CODE']);
    }

    /**
     * Проверяет установленные параметры у действия. Если какой-то важный параметр не заполнен,
     * то произойдет исключение
     * 
     * @return void
     * @throw
     */
    protected function checkParams()
    {
        global $langValues;

        $this->isChecked = false;

        foreach ($this->foundActivity['params']['PROPERTIES'] as $propertyCode => $propertyParams) {
            if (
                (strtolower($propertyParams['Required']) == 'y')
                && !isset($this->values[$propertyCode])
            ) throw new Exception(strtr($langValues['ERROR_EMPTY_ACTIVITY_PROPERTY'], ['#PROPERTY#' => $propertyCode]));
        }
        $this->isChecked = true;
    }

    /**
     * Сразу устанавливает несколько параметров для действия и проверяет их через checkParams
     * 
     * @param array $params - параметры действия
     * @return self
     */
    public function setParams(array $params)
    {
        if (!$this->foundActivity) return;

        $this->values = [];
        foreach ($params as $paramName => $paramValue) {
            if (empty($this->foundActivity['params']['PROPERTIES'][$paramName]))
                continue;

            $this->values[$paramName] = $paramValue;
        }
        $this->checkParams();
        return $this;
    }

    /**
     * Устанавливает конкретный параметр действия, если установить этот параметр позволяет
     * инфомрация о параметрах действия, считанных при создании экземпляра класса BPActivity
     * После установки параметра во время запуска действия далее будет вызвана проверка
     * всех параметров действия через checkParams
     * 
     * @param string $name - имя параметра действия
     * @param $value - значение параметре
     *
     * @return void
     */
    public function __set(string $name, $value)
    {
        if (
            !$this->foundActivity
            || empty($this->foundActivity['params']['PROPERTIES'][$name])
        ) return;

        $this->isChecked = false;
        $this->values[$name] = $value;
    }

    /**
     * Возвращает значение конкретного параметра действия, если этот параметр был установлен
     * ранее
     * 
     * @param string $name - имя параметра действия
     * @return mixed
     */
    public function __get(string $name)
    {
        return $this->foundActivity && isset($this->values[$name]) ? $this->values[$name] : null;
    }

    /**
     * Возвращает путь к файлу с кодом, который вызывается для запуска действия
     *
     * @return string
     */
    public function getPath()
    {
        return $this->foundActivity['path'] . '/index.php';
    }

    /**
     * Запускает действие, проверив прежде установленные параметры, если они не были проверены.
     * Для запуска необходимо, чтобы в папке действия был файл index.php, иначе произойдет исключение
     * с информацией об этом
     * 
     * @param BX24RestAPI $restAPIUnit - экземляр класса BX24RestAPI для работы с REST API Bitrix24
     * @param string $tokenValue - значение токена, который был передан в запросе в параметре event_token,
     * оно будет использоваться при ответе действия
     * 
     * @return array
     */
    public function run(BX24RestAPI $restAPIUnit, string $tokenValue = null)
    {
        global $langValues;

        if (!$this->foundActivity) return;
        if (!$this->isChecked) $this->checkParams();

        $executeFileName = $this->foundActivity['path'] . '/index.php';
        if (!file_exists($executeFileName))
            throw new Exception(strtr($langValues['ERROR_NO_ACTIVITY_INDEX_FILE'], ['#ACTIVITY#' => $this->foundActivity['code']]));

        $activityResult = require $executeFileName;
        if (!$tokenValue) return;

        $answer = ['EVENT_TOKEN' => $tokenValue];
        if (is_array($activityResult)) $answer['RETURN_VALUES'] = $activityResult;

        $restAPIUnit->callBizprocEventSend($answer);
        return $activityResult;
    }

    /**
     * Возвращает выходные параметры заданные в описании действия 'RETURN_PROPERTIES'
     *
     * @return array
     */
    public function getReturnedProperties()
    {
        $result = [];
        foreach ($this->foundActivity['params']['RETURN_PROPERTIES'] as $propertyName => $returnProperty)
            $result[$propertyName] = $returnProperty['VALUE'];

        return $result;
    }

    /**
     * Устанавливает значения возвращаемых полей действий бизнес-процесса
     *
     * @param string $name - имя возвращаемого параметра действия
     * @param mixed $value - значение возвращаемого параметра
     *
     * @return void
     */
    public function setReturnedProperties(string $name, $value)
    {
        if (
            !$this->foundActivity
            || empty($this->foundActivity['params']['RETURN_PROPERTIES'][$name])
        ) return;

        $this->foundActivity['params']['RETURN_PROPERTIES'][$name]['VALUE'] = $value;
     }
}