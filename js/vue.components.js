var VueComponentParams = {};

$('*[type="text/vue-component"]').each((unitNum, unitObj) => {
    var componentSelector = ($(unitObj).attr('id') || '').replace(/\-component$/i, '');
    if (!componentSelector) return;

    var paramSelector = componentSelector.replace(/\W(\w)/g, (...parts) => parts[1].toUpperCase() );
    var params = VueComponentParams[paramSelector] ? VueComponentParams[paramSelector] : {};
    var props = $(unitObj).data('props');
    if (params.props instanceof Object) {
        props = params.props;

    } else {
        props = props ? props.trim().split(/\s*,\s*/) : [];
    }

    var componentData = {
        ...params,
        props: props,
        template: $(unitObj).html().trim().replace(/\s+/g, ' ')
    };

    Vue.component(componentSelector, componentData);
});