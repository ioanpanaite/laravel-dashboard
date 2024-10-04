app.directive("fileread", [function () {
    return {
        scope: {
            fileread: "="
        },
        link: function (scope, element, attributes) {
            element.bind("change", function (changeEvent) {
                var reader = new FileReader();
                reader.onload = function (loadEvent) {
                    scope.$apply(function () {
                        scope.fileread = loadEvent.target.result;
                    });
                };
                if (angular.isDefined(changeEvent.target.files[0])) {
                    reader.readAsDataURL(changeEvent.target.files[0]);
                }
            });
        }
    }
}]);
