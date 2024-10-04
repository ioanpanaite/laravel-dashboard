app.directive("lkrPopUrl", function($compile, $parse, $sce) {
    return {
        restrict: 'EA',
        templateUrl: 'directives/pop-url.html',
        scope: {
            urlcontent: '=urlcontent'
        },

    link: function link(scope, element, attrs) {
        }
    };
});