<?
$langValues = [
    'APPLICATION_TITLE' => 'Действия бизнес-процессов',
];

// lib/helpers/bx24.restapi.class.php
$langValues['ERROR_EMPTY_PARAMS'] = 'Не указаны параметры domain, а так же либо access_token для обычного REST-запоса, '
                                  . 'либо webhook_token и webhook_userid для рабты через вебхук';
$langValues['ERROR_BAD_RESTAPI_METHOD_NAME'] = 'Для методов REST API надо использовать конструкцию call<СamelСase названия метода>(<параметры метода>)';

// lib/helpers/bpactivity.class.php
$langValues['ERROR_ACTIVITY_CODE'] = 'Этот запрос не может быть обработан этим действием БП';
$langValues['ERROR_EMPTY_ACTIVITY_PROPERTY'] = 'Не был указан параметр #PROPERTY#';
$langValues['ERROR_NO_ACTIVITY_INDEX_FILE'] = 'Для действия #ACTIVITY# отсутствует index.php';

/**
 * Описание действий для БП в bx/bizproc/activities/*
 *
 * Основной заголовок списка с неутановленными действиями
 */
$langValues['BP_ACTIVITIES_INSTALLED_TITLE'] = 'Были установлены следующие действия для Бизнес-процессов';
$langValues['BP_ACTIVITIES_EMPTY_TITLE'] = 'Необходимо установить следующие действия для Бизнес-процессов';
$langValues['ACTIVITY_LIST_REMOVE_BUTTON'] = 'Удалить';
$langValues['ACTIVITY_LIST_INSTALL_BUTTON'] = 'Установить';

/*
 * Описание параметров для действия test.action
 */
$langValues['TEST_ACTION_TITLE'] = 'Тестовое действие';
$langValues['TEST_ACTION_DESCRIPTION'] = 'Описание тестового действия';
$langValues['SOME_VALUE_NAME'] = 'Некоторое значение';
$langValues['SOME_VALUE_DESCRIPTION'] = 'Описание некоторого значения';