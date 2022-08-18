{
    el: selector.main,
    data: {
        activityInstalled: wasInstalled,
        activities: (() => {
                        var notInstalled = {};
                        if (notExistActivityCodes.length) {
                            notExistActivityCodes.forEach(code => notInstalled[code] = activities[code]);

                        } else {
                            notInstalled = activities;
                        }
                        return notInstalled;
                    })(),
    },

    methods: {

        /**
         * Обработчик нажатия кнопки "Удалить" для удаления установленных действий БП
         * 
         * @return void
         */
        removeActivities() {
            deleteActivities();
        },

        /**
         * Обработчик нажатия кнопки "Установить" для установки тех действий, которые
         * не были установлены
         *
         * @return void
         */
        addActivities() {
            addActivities();
        }
    }
}