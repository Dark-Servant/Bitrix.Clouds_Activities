;$(() => {
    /*LANG_VALUES*/
    /*SERVER_CONSTANTS*/
    var selector = {
        main: '#activities',
        activityList: '.activity-list'
    };
    var classList = {
        noReaction: 'no-reaction'
    };
    var BX24Auth;
    /*activities*/
    var notExistActivityCodes = [];
    mainApp = false;

    /*vue.components*/
    
    /**
     * Обработчик Bitrix RestAPI запроса
     * 
     * @param name - название метода
     * @param params - параметры метода
     *
     * @return Promise
     */
    var BXRestAPISend = function(name, params) {
        return new Promise(success => {
            BX24.callMethod(
                name,
                typeof(params) == 'object' ? params : {},
                data => success(data.answer)
            );
        });
    }

    /**
     * Основной метод приложения, с которого начинается работа в нем
     * 
     * @param wasInstalled - флаг с указанием, что все действия установлены
     * @return void
     */
    var showApplication = function(wasInstalled) {
        mainApp = new Vue(/*vue.main*/);
    }

    /**
     * Добавление действия Бизнес-процессов в систему
     * 
     * @return void
     */
    var addActivity = function() {
        if (!notExistActivityCodes.length) {
            $(selector.activityList).removeClass(classList.noReaction);
            mainApp.activityInstalled = true;
            mainApp.activities = activities;
            return;
        }

        var activityCode = notExistActivityCodes.shift();
        BXRestAPISend(
            'bizproc.activity.add',
            {
                ...activities[activityCode],
                CODE: activityCode,
                HANDLER: document.location.origin + SERVER_CONSTANTS.APPPATH + '/bx/bizproc/activities/index.php',
                AUTH_USER_ID: 1,
                USE_SUBSCRIPTION: 'Y',
                DOCUMENT_TYPE: ['lists', 'BizprocDocument']
            }
        ).then(answer => addActivity());
    }

    /**
     * Запуск процесса Добавления действий Бизнес-процессов в систему
     * 
     * @return void
     */
    var addActivities = function() {
        $(selector.activityList).addClass(classList.noReaction);
        addActivity();
    }

    /**
     * Удаление действия Бизнес-процессов из системы
     * 
     * @param activityCodes - список специальных кодов действий Бизнес-процессов
     * @param callBack - метод, который вызовется, если список activityCodes будет пуст
     * @return void
     */
    var deleteActivity = function(activityCodes, callBack) {
        if (!activityCodes.length) {
            $(selector.activityList).removeClass(classList.noReaction);
            if (mainApp) mainApp.activityInstalled = false;
            return typeof(callBack) == 'function' ? callBack() : true;
        }

        var activityCode = activityCodes.shift();
        /**
         * В случае, если некоторые действия были удалены из решения, но они остались на портале,
         * то эти действия удалятся с портала, и их не надо запоминать в списке неустановленных
         * действий
         */
        if (activities[activityCode] instanceof Object)
            notExistActivityCodes.push(activityCode);

        BXRestAPISend('bizproc.activity.delete', {code: activityCode})
            .then(() => deleteActivity(activityCodes, callBack));
    }

    /**
     * Запуск процесса удаления действий Бизнес-процессов из системы
     * 
     * @return void
     */
    var deleteActivities = function() {
        $(selector.activityList).addClass(classList.noReaction);
        deleteActivity(Object.keys(activities));
    }

    /**
     * Проверка какие действия Бизнес-процессов установлены в системе
     * 
     * @return void
     */
    var checkActivities = function() {
        BXRestAPISend('bizproc.activity.list')
            .then(
                answer => {
                    if (answer.error) {
                        showApplication();
                        return;
                    }

                    for (var code in activities) {
                        if (answer.result.indexOf(code) > -1) continue;

                        notExistActivityCodes.push(code);
                    }
                    deleteActivity(
                        answer.result.filter(code => activities[code] == undefined ),
                        () => showApplication(notExistActivityCodes.length < 1)
                    );
                }
            )
    }

    if (typeof SERVER_CONSTANTS.DOMAIN != 'undefined') {
        BX24.init(() => {
            BX24Auth = BX24.getAuth();
            checkActivities();
        });
    }
});