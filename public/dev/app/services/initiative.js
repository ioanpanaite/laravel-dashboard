app.factory('initiativeFactory', function($q, $http,  $cacheFactory, $state, apiService) {
    var currentInitiative = null;
    var initiatives = [];
    return {
        current: function()
        {
            return currentInitiative;
        },
        invalidateInitiativeCache : function()
        {
            if(currentInitiative)
                $cacheFactory.get('$http').remove(apiUrl+'/initiative/'+currentInitiative.code);

        },
        initiativeList : function(data)
        {
            if(angular.isDefined(data))
            {
                initiatives = data;
            } else {
                return initiatives;
            }
        },

        loadInitiatives: function(invalidateCache)
        {
            if(invalidateCache)  $cacheFactory.get('$http').remove(apiUrl+'/initiative');
            var deferred = $q.defer();
            apiService.getCached('initiative').then(
                function(res){
                    initiatives = res;
                    deferred.resolve(res);
                }, function(err){
                    deferred.reject(err);
                }
            )
            return deferred.promise;
        },
        loadTags : function(){
            if(currentInitiative)
            {
                apiService.get(['tag', currentInitiative.code]).then(function(res){
                    currentInitiative.tags = res;
                });
            }
        },
        accessDesc : function(){
            if(currentInitiative)
                return currentInitiative.access == 'PU' ? translations['PUBLIC'] : translations['PRIVATE'];
        },
        access : function(){
            if(currentInitiative)
                return currentInitiative.access;
        },
        title : function()
        {
            if(currentInitiative)
                return currentInitiative.title;
        },

        purpose_of_initiative : function()
        {
            if(currentInitiative)
                return currentInitiative.purpose_of_initiative;
        },

        stpes_of_development : function()
        {
            if(currentInitiative)
                return currentInitiative.stpes_of_development;
        },

        planning_estimation : function()
        {
            if(currentInitiative)
                return currentInitiative.planning_estimation;
        },

        active : function(){
            if(currentInitiative)
                return currentInitiative.active;

        },
        options : function()
        {
            if(currentInitiative)
                return currentInitiative.options;
        },
        tasks: function(){
            if(currentInitiative)
                return currentInitiative.options.features.indexOf('tasks')>=0;
        },
        featuresDesc : function()
        {
            if(currentInitiative)
            {
                var features = angular.copy(currentInitiative.options.features);
                for(var i=0; i<features.length; i++){
                    if( translations[features[i].toUpperCase()] ) features[i] = translations[features[i].toUpperCase()] ;

                }
                return features.join(', ');
            }

        },
        members: function()
        {
            if(currentInitiative)
                return currentInitiative.users;

        },
        tags: function()
        {
            if(currentInitiative)
                return currentInitiative.tags;
        },

        description : function(){
            if(currentInitiative)
                return currentInitiative.description;
        },

        isDefault : function()
        {
            if(currentInitiative)
                return currentInitiative.default_initiative;
        },
        code: function()
        {
            if(currentInitiative)
                return currentInitiative.code;
        },
        role: function()
        {
            if(currentInitiative)
                return currentInitiative.role;
        },

        isMine: function(id)
        {
            return id == user.id;
        },

        isLoaded: function() {
            return currentInitiative !== null;
        },

        setCurrent: function(initiativeCode) {
            var deferred = $q.defer();
            currentInitiative = null;

            apiService.getCached(['initiative', initiativeCode]).then(
                function(res){
                    currentInitiative = res;
                    deferred.resolve(res);
                }, function(err){
                    //$state.go('home');
                })

            return deferred.promise;

        }


    }
});
