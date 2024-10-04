app.directive("transferClick", [function () {
    return {
        link: function (scope, element, attributes) {
            element.bind("click", function (clickEvent) {
                angular.element(attributes.transferClick).click();
            });
        }
    }
}]);
