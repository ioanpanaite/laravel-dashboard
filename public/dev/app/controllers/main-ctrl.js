app.controller('mainCtrl', function($scope, $state, spaceFactory, formatterService, $modal, apiService,$stateParams, $timeout ){

    $scope.formatter = formatterService;
    $scope.sp = {spaceSearch : ''};
    $scope.userProfile = {};
    $scope.activePage = 0;
    $scope.msg = {privateMessage : ''};
    $scope.msgCount = 0;
    $scope.spaceFactory = spaceFactory;
	$scope.initiatives = [];
	$scope.spaces = [];
	$scope.ideas = [];
	$scope.questions = [];
	$scope.followers = [];
    $scope.following = [];    
    $scope.features = [
        {value: 'drive', text: translations['DRIVE']},
        {value:'wikis', text:translations['WIKIS']},
        {value:'tasks', text: translations['TASKS']},
        {value:'chat', text: translations['CHAT']},
    ];

    $scope.loadInitiatives = function()
    {
        apiService.get('initiative').then(
            function(res){
                $scope.initiatives = res;
                $scope.spaceFactory.spaceList(res);

            })
    }
    $scope.loadSpaces = function()
    {
        apiService.get('space').then(
            function(res){
                $scope.spaces = res;
                $scope.spaceFactory.spaceList(res);

            })
    }
    $scope.loadIdeas = function()
    {
        apiService.get('idea').then(
            function(res){
                $scope.ideas = res;
                $scope.spaceFactory.spaceList(res);

            })
    }
	
    $scope.loadQuestions = function()
    {
        apiService.get('question').then(
            function(res){
                $scope.questions = res;
                $scope.spaceFactory.spaceList(res);

            })
    }
	
    $scope.loadFollowers = function()
    {
        apiService.get('followers').then(
            function(res){
                $scope.followers = res;
                $scope.spaceFactory.spaceList(res);

            })
    }
	
    $scope.loadFollowing = function()
    {
        apiService.get('following').then(
            function(res){
                $scope.following = res;
                $scope.spaceFactory.spaceList(res);

            })
    }

    $scope.gotoIniciative = function(code)
    {
        $state.go('initiative.stream', {initiativeCode: code});
    };

    $scope.gotoIdea = function(code)
    {
        $timeout(function(){

            $state.go('idea.stream', {ideaCode: code});
        }, 200);
    };
	
    $scope.followMe = function(id)
    {
        apiService.post('follow', {user_id: id,status:"follow"}).then(
            function(res){
                $scope.msg = {privateMessage : res};
				window.location.reload();
            }
        )
	}
	
    $scope.unfollowMe = function(id)
    {
        apiService.post('follow', {user_id: id,status:"unfollow"}).then(
            function(res){
                $scope.msg = {privateMessage : res};
				window.location.reload();
            }
        )
	}
	
/* 	$scope.userid =  $stateParams['userid'];
	
    apiService.get(['profile/initiatives',  27]).then(
        function(res){
            $scope.contents = res;
        }
    ) */



    $scope.loadInitiatives();
    $scope.loadSpaces();
    $scope.loadIdeas();
    $scope.loadQuestions();
    $scope.loadFollowers();
    $scope.loadFollowing();
	
    $scope.search = { text : ''};

    $scope.doSearch = function()

    {

        if($scope.search.text != ''){
            $state.go('search', {query: $scope.search.text});
            $scope.search = { text : ''};
        }
    }

    $scope.sendMessageClick = function(){

        if($scope.msg.privateMessage == '') return;

        apiService.post('message', {to_id: $scope.userProfile.id, body: $scope.msg.privateMessage}).then(
            function(res){
                $scope.msg = {privateMessage : ''};

            }
        )
    }

    $scope.$on('peopleDialogEvent', function(e,id){

        apiService.getCached(['profile', id], null, true).then(
            function(res){
                $scope.userProfile = res;

                var peopleModal = $modal({template: 'partials/modal-people.html', scope: $scope, animation: 'am-fade-and-slide-top', show: true});

            }
        )
    })

    $scope.$on('messageCountChanged', function(){
        $scope.mainPool();
    })
	

    $scope.gotoSpace = function(code)
    {
        $scope.spaceSearch = '';
        $timeout(function(){
            $scope.sp = {spaceSearch : ''};

            $state.go('space.stream', {spaceCode: code});
        }, 200);
    }


    $scope.gotoPage = function(page)
    {
        $timeout(function(){
            $state.go(page);
        });
    }

    $scope.$on('initiativeListChanged', function(e){
        $scope.loadInitiatives();
    });
	
    $scope.$on('ideaListChanged', function(e){
        $scope.loadIdeas();
    });

    $scope.$on('questionListChanged', function(e) {
        $scope.loadQuestions();
    });

    $scope.getLogoutUrl = function()
    {
        return siteUrl + '/logout';
    }


    $scope.mainPool = function()
    {
        apiService.get('mainpool').then(
            function(res)
            {

                if(res.msgs > $scope.msgCount)
                    // sndBell.play();

                $scope.msgCount = res.msgs;

                $timeout($scope.mainPool, 60000);
            }
        )

    }

    $scope.mainPool();

    $scope.createSpaceClick = function()
    {
        $scope.editSpace = {title: "", description: '', options:{ features: ['drive', 'wikis', 'tasks', 'chat']}, purpose_of_space: '', space_video: '', space_paper: '', stpes_of_development: '', planning_estimation:'', access: 'PU'};
        $scope.error = null;
        $scope.spaceForm = $modal({template: 'partials/modal-space-edit.html', title: 'New project', scope: $scope, animation: 'am-fade-and-slide-top', show: true});
    }
    
    $scope.createIdeaClick = function()
    {
        $scope.editIdea = {title: "", description: '', purpose_of_idea: '', idea_video: '', idea_paper: '', stpes_of_development: '', planning_estimation:'',options:{ features: ['drive', 'wikis', 'tasks', 'chat']}, access: 'PU', terms:''};
        $scope.error = null;
        $scope.ideaForm = $modal({template: 'partials/modal-idea-edit.html', title: 'New idea', scope: $scope, animation: 'am-fade-and-slide-top', show: true});
    }
	
    $scope.createInitiativeClick = function()
    {
        $scope.editInitiative = {title: "", description: '', purpose_of_initiative: '', initiative_video: '', initiative_paper: '', stpes_of_development: '', planning_estimation:'', options:{ features: ['drive', 'wikis', 'tasks', 'chat']}};
        $scope.error = null;
        $scope.initiativeForm = $modal({template: 'partials/modal-initiative-edit.html', title: 'New Initiative', scope: $scope, animation: 'am-fade-and-slide-top', show: true});
    }
	
    $scope.createQuestionClick = function()
    {
        $scope.editQuestion = {title: "", description: '', sector: '', reward: '', options:{ features: ['drive', 'wikis', 'tasks', 'chat']}};
        $scope.error = null;
        $scope.questionForm = $modal({template: 'partials/modal-question-edit.html', title: 'New Question', scope: $scope, animation: 'am-fade-and-slide-top', show: true});
    }

    $scope.editSpaceOkClick = function()
    {
        apiService.post(['space'], $scope.editSpace).then(
            function(res){
                $scope.spaceForm.hide();
                $scope.$emit('spaceListChanged');
                //document.location.href= '#/space/'+res;
                $state.go('space.stream', {spaceCode: res});
                $scope.loadSpaces();
            }, function(err){
                $scope.error = err;
            }
        )
    }
    
    $scope.editInitiativeOkClick = function()
    { 
        apiService.post(['initiative'], $scope.editInitiative).then(
            function(res){
                $scope.initiativeForm.hide();
                $scope.$emit('initiativeListChanged');
                //document.location.href= '#/initiative/'+res;
                $state.go('initiative.stream', {initiativeCode: res});

            }, function(err){
                $scope.error = err;
            }
        )
    }
    
    $scope.editQuestionOkClick = function()
    { 
        apiService.post(['question'], $scope.editQuestion).then(
            function(res){
                $scope.questionForm.hide();
                $scope.$emit('questionListChanged');
                //document.location.href= '#/initiative/'+res;
                $state.go('idea.stream', {ideaCode: res});

            }, function(err){
                $scope.error = err;
            }
        )
    }
});