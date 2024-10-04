app.directive('lkrMeeting', function($q, apiService, formatterService, $stateParams) {
    return {
        restrict: 'EA',
        templateUrl: 'directives/meeting.html',
        scope: {
            meeting: '=meeting',
            contentId : '@'

        },

        link: function(scope, element, attrs) {

            scope.spaceCode = $stateParams.spaceCode;

            scope.noComments = angular.isDefined(attrs.noComments);

            scope.$watch('meeting', function(val){
                if(val)
                {
                    scope.edit = {editing : '',
                        owner: scope.meeting.user_id == user.id,
                        newRecord : angular.isDefined(attrs.newRecord),
                        error : [],
                        meeting: {}
                    };
                    jQuery.extend(scope.edit.meeting, scope.meeting);
                }
            }, true);

            scope.fileEditorId = 'feNewMeeting'+scope.contentId+scope.spaceCode;

            scope.formatter = formatterService;

            scope.states = [
                {id: 0, caption: translations['NOT_STARTED'], icon:'fa-stop'},
                {id: 1, caption: translations['IN_PROGRESS'], icon:'fa-play'},
                {id: 2, caption: translations['SUSPENDED'], icon:'fa-pause'},
                {id: 3, caption: translations['COMPLETED'], icon: 'fa-check'},
                {id: 4, caption: translations['CANCELED'], icon: 'fa-times'}];

            scope.priorities = [
                {id: 0, caption: translations['LOW']},
                {id: 1, caption: translations['NORMAL']},
                {id: 2, caption: translations['HIGH']},
                {id: 3, caption: translations['CRITICAL']}];


            scope.editClick = function(fieldName)
            {
                scope.edit.meeting = JSON.parse(JSON.stringify(scope.meeting));
                scope.edit.error = {};
                scope.edit.editing = fieldName;
            }

            scope.archiveMeetingClick = function()
            {
                apiService.get(['meeting/archive', scope.meeting.id]).then(
                    function(res){

                        scope.$emit('meetingArchiveEvent', scope.meeting.id );
                        scope.$emit('meetingUpdateCounters');
                    });
            }

            scope.confirmDeleteClick = function(){
                apiService.delete(['meeting', scope.meeting.id]).then(
                    function(res){
                        scope.$emit('meetingDeleteEvent', scope.meeting.id);
                        scope.$emit('meetingUpdateCounters');
                    });
            }

            scope.cancelClick = function(){
                scope.edit.error = [];
                scope.$emit('meetingCancelEvent');
            }

            scope.postClick = function(){
                var payLoad = scope.meeting;
                if(scope.contentId != ''){
                    payLoad['content_id'] = scope.contentId;
                }

                if(angular.isDefined($stateParams.spaceCode) )
                {
                    payLoad['space_code'] = $stateParams.spaceCode;
                }

                apiService.post(['meeting'] , payLoad).then(
                    function(res){

                           scope.$emit('meetingInsertEvent', res);
                           scope.$emit('meetingUpdateCounters');

                    }, function(err){
                            scope.edit.error = err;

                    });
            }

            scope.updateRadio = function(fieldName, valueId)
            {
                if(scope.edit.newRecord)
                {
                    scope.meeting[fieldName] = valueId;

                } else {
                    apiService.put(['meeting',scope.meeting.id] ,{field:fieldName, value: valueId}).then(
                        function(res){
                            scope.edit.editing = '';

                            scope.meeting[fieldName] = valueId;
                            scope.$emit('meetingUpdateCounters');

                        }, function(err){
                            scope.edit.error = err;

                        });
                }
            }

            scope.updateField = function(fieldName) {
                if(angular.isUndefined(scope.edit.meeting[fieldName])) scope.edit.meeting[fieldName] = null;

                apiService.put(['meeting',scope.meeting.id] ,{field:fieldName, value: scope.edit.meeting[fieldName]}).then(
                function(res){
                    if(fieldName == 'assigned_to')
                    {
                        scope.meeting['assigned_to'] = res.assigned_to;
                        scope.meeting['assignments'] = res.assignments;
                        scope.meeting['my'] = res.my;
                        scope.meeting['delegated'] = res.delegated;


                    } else if(fieldName == 'due_date')  {
                        scope.meeting[fieldName] = scope.edit.meeting[fieldName];
                        scope.meeting['due_date_group'] = res.due_date_group;

                    } else  {
                        if(angular.isUndefined(scope.edit.meeting[fieldName] )){
                            scope.meeting[fieldName] = null;

                        } else {
                            scope.meeting[fieldName] = scope.edit.meeting[fieldName];
                        }
                    }
                    scope.$emit('meetingUpdateCounters');

                    scope.edit.editing = '';

                }, function(err){
                        scope.edit.error = err;

                });
            }
            scope.listAssignments = function(items){
                if(items && items.length > 0)
                   return _.pluck(items, 'full_name').join(', ');
            }

            scope.loadUsers = function(ini)
            {
                if(ini.length < 3) return;
                return apiService.get(['user'],{ini: ini});
            }

            scope.allowEdit = function(fieldName){
                if(fieldName == 'state')
                {
                    if(scope.meeting.user_id == user.id){
                        return true;
                    } else {
                        for(var i=0; i<scope.meeting.assigned_to.length; i++){
                            if(scope.meeting.assigned_to[i].id == user.id)
                            {
                                return true;
                            }
                        }
                        return false;

                    }
                } else {

                   return scope.meeting.user_id == user.id;
                }
            }

        }

    }})
