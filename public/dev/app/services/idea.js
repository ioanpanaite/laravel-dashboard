app.factory('ideaFactory', function($q, $http,  $cacheFactory, $state, apiService) {
    var currentIdea = null;
    var ideas = [];
    return {
        current: function()
        {
            return currentIdea;
        },
        invalidateIdeaCache : function()
        {
            if(currentIdea)
                $cacheFactory.get('$http').remove(apiUrl+'/idea/'+currentIdea.code);

        },
        ideaList : function(data)
        {
            if(angular.isDefined(data))
            {
                ideas = data;
            } else {
                return ideas;
            }
        },

        loadIdeas: function(invalidateCache)
        {
            if(invalidateCache)  $cacheFactory.get('$http').remove(apiUrl+'/idea');
            var deferred = $q.defer();
            apiService.getCached('idea').then(
                function(res){
                    ideas = res;
                    deferred.resolve(res);
                }, function(err){
                    deferred.reject(err);
                }
            )
            return deferred.promise;
        },
        loadTags : function(){
            if(currentIdea)
            {
                apiService.get(['tag', currentIdea.code]).then(function(res){
                    currentIdea.tags = res;
                });
            }
        },
        accessDesc : function(){
            if(currentIdea)
                return currentIdea.access == 'PU' ? translations['PUBLIC'] : translations['PRIVATE'];
        },
        access : function(){
            if(currentIdea)
                return currentIdea.access;
        },
        title : function()
        {
            if(currentIdea)
                return currentIdea.title;
        },

        purpose_of_idea : function()
        {
            if(currentIdea)
                return currentIdea.purpose_of_space;
        },

        stpes_of_development : function()
        {
            if(currentIdea)
                return currentIdea.stpes_of_development;
        },

        planning_estimation : function()
        {
            if(currentIdea)
                return currentIdea.planning_estimation;
        },

        active : function(){
            if(currentIdea)
                return currentIdea.active;

        },
        options : function()
        {
            if(currentIdea)
                return currentIdea.options;
        },
        tasks: function(){
            if(currentIdea)
                return currentIdea.options.features.indexOf('tasks')>=0;
        },
        featuresDesc : function()
        {
            if(currentIdea)
            {
                var features = angular.copy(currentIdea.options.features);
                for(var i=0; i<features.length; i++){
                    if( translations[features[i].toUpperCase()] ) features[i] = translations[features[i].toUpperCase()] ;

                }
                return features.join(', ');
            }

        },
        members: function()
        {
            if(currentIdea)
                return currentIdea.users;

        },
        tags: function()
        {
            if(currentIdea)
                return currentIdea.tags;
        },

        description : function(){
            if(currentIdea)
                return currentIdea.description;
        },

        isDefault : function()
        {
            if(currentIdea)
                return currentIdea.default_idea;
        },
        code: function()
        {
            if(currentIdea)
                return currentIdea.code;
        },
        role: function()
        {
            if(currentIdea)
                return currentIdea.role;
        },

        isMine: function(id)
        {
            return id == user.id;
        },

        isLoaded: function() {
            return currentIdea !== null;
        },

        setCurrent: function(ideaCode) {
            var deferred = $q.defer();
            currentIdea = null;

            apiService.getCached(['idea', ideaCode]).then(
                function(res){
                    currentIdea = res;
                    deferred.resolve(res);
                }, function(err){
                    //$state.go('home');
                });

            return deferred.promise;

        }


    }
});

