app.controller('projectsCtrl', function($scope, apiService) {
    
    $scope.projects = {};

    apiService.get('projects').then(function(res) {
        $scope.projects = res;
    })
})

app.filter('projectsFilter', function(){
    return function(dataArray, searchTerm){
        if(!dataArray ) return;
        if( !searchTerm){
            return dataArray
        }else{

            var term=searchTerm.toLowerCase();
            return dataArray.filter(function(item){

                result = item.full_name.toLowerCase().indexOf(term) > -1;
                if(item.title)
                    result = result || item.title.toLowerCase().indexOf(term) > -1;
                    
                if(item.description)
                    result = result || item.description.toLowerCase().indexOf(term) > -1;

                return result;
            });
        }
    }
});