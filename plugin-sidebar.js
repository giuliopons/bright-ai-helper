( function ( wp ) {
    var registerPlugin = wp.plugins.registerPlugin;
    var PluginSidebar = wp.editPost.PluginSidebar;
    var el = wp.element.createElement;
    var TextControl = wp.components.TextControl;

    registerPlugin( 'my-plugin-sidebar', {
        render: function () {
            return el(
                PluginSidebar,
                {
                    name: 'my-plugin-sidebar',
                    icon: 'welcome-learn-more',
                    title: 'Bright AI Helper',
                },
                el(
                    'div',
                    { className: 'plugin-sidebar-content' },
                    el( 'p', { },'1. Generate a new title' )
                )
            );
        },
    } );
} )( window.wp );

