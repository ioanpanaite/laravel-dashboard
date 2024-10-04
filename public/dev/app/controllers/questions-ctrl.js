app.controller('questionsCtrl', function($scope, apiService) {
    
    $scope.questions = {};

    apiService.post('questions').then(function(res) {
        $scope.questions = res;
    })
})

app.filter('questionsFilter', function(){
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

                if(item.sector)
                    result = result || item.sector.toLowerCase().indexOf(term) > -1;
                
                if(item.reward)
                    result = result || item.reward.toLowerCase().indexOf(term) > -1;

                return result;
            });
        }
    }
});