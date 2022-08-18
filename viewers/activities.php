<script id="activity-list-component" data-props="installed, activities" type="text/vue-component">
    <div class="test-activity-list">
        <div class="test-activity-list-title" v-if="installed"><?=$langValues['BP_ACTIVITIES_INSTALLED_TITLE']?></div>
        <div class="test-activity-list-title" v-else><?=$langValues['BP_ACTIVITIES_EMPTY_TITLE']?></div>
        <div class="test-activity-list-data">
            <div class="test-activity-unit" v-for="activity in activities">
                <span>{{activity.NAME.<?=LANG?>}}</span>
            </div>
        </div>
        <div class="test-activity-list-buttons">
            <div class="test-button test-activity-list-button test-activity-list-remove-button"
                v-on:click="$emit('remove-activities')"
                v-if="installed"><?=$langValues['ACTIVITY_LIST_REMOVE_BUTTON']?></div><!--
            --><div class="test-button test-activity-list-button test-activity-list-install-button"
                v-on:click="$emit('add-activities')"
                v-else><?=$langValues['ACTIVITY_LIST_INSTALL_BUTTON']?></div>
        </div>
    </div>
</script>

<div id="activities">
    <activity-list
        v-on:remove-activities="removeActivities"
        v-on:add-activities="addActivities"
        v-bind:installed="activityInstalled"
        v-bind:activities="activities"></activity-list>
</div>