app.controller('meetingCtrl', function($scope, $state, $stateParams, $previousState, apiService ){

    $scope.meetings = []
    $scope.$parent.selected = 3;

    $scope.showingArchived = false;

    $scope.spaceCode = $stateParams.spaceCode;

    $scope.menuIndex = 0;

    $scope.loadTasks = function(archived){

        var param = angular.isDefined($stateParams.spaceCode) ? {space_code: $stateParams.spaceCode} : {};

        if(angular.isDefined(archived)) param['archived'] = 1;

        apiService.get(['meeting'], param).then(
            function(data){
                $scope.meetings = data;
                $scope.getCounters();
            }
        )
    }


    $scope.menuClick = function(index)
    {
        $scope.menuIndex = index;
        var filters = [{}, {my:true}, {delegated:true}];

        if(index < 3)
        {
            if($scope.showingArchived) $scope.loadTasks();
            $scope.showingArchived = false;
            $scope.filter = filters[index];
        } else {
            $scope.loadTasks(true);
            $scope.showingArchived = true;
            $scope.filter= {};
        }
        $scope.$broadcast('meetingMenuChanged');

    }
    $scope.newMeetingClick = function(){
        $scope.$broadcast('newMeetingClickEvent');

    }

    $scope.archivedClick = function()
    {

        $scope.loadTasks(true);
        $scope.showingArchived = true;
        $scope.filter = {};
    }

    $scope.$on('meetingUpdateCounters', function(data) {
        $scope.getCounters();
    });

    $scope.getCounters = function(){
        var param = angular.isDefined($stateParams.spaceCode) ? {space_code: $stateParams.spaceCode} : null;

        apiService.get(['meeting/counters'], param).then(
            function(res){
                $scope.counters = res;
            }
        )
    }

    $scope.loadTasks();


})