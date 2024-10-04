app.directive('lkrMeetingList', function($timeout, formatterService, $stateParams) {
    return {
        restrict: 'EA',
        templateUrl: 'directives/meeting-list.html',
        scope: {
            meetings: '=meetings',
            contentId: '@',
            filter: '='
        },

        link: function(scope, element, attrs) {
            scope.newMeetingPanel = -1;
            scope.spaceCode = $stateParams.spaceCode;
            scope.prepareNewMeeting = function()
            {
                scope.newMeeting = {user_id: user.id, priority:1, state:0, space_code:scope.spaceCode, files:[], error:[]};

            }

            scope.$on('meetingMenuChanged', function(ev){
                scope.newMeetingPanel = -1;
            })

            scope.list = {field : 'due_date_group'};


            scope.getGroupText = function(meeting) {

                if(scope.list.field == '') return;

                if(scope.list.field == 'state') {

                    var states =[translations['NOT_STARTED'], translations['IN_PROGRESS'], translations['SUSPENDED'], translations['COMPLETED'], translations['CANCELED']];
                    return states[ meeting.state];
                }

                if(scope.list.field == 'priority') {

                    var prs =[translations['LOW'], translations['NORMAL'], translations['HIGH'], translations['CRITICAL'] ];
                    return prs[ meeting.priority];
                }

                if(scope.list.field == 'due_date_group') {

                    var dd = [translations['OVERDUE'], translations['TODAY'], translations['TOMORROW'], translations['NEXT_7_DAYS'], translations['LATER'], translations['SOMEDAY']];
                    return dd[meeting.due_date_group];
                }
                if(scope.list.field == 'space') {

                    return meeting.space;
                }

                return "";

            }


            scope.formatter = formatterService;

            scope.$on('meetingInsertEvent', function(e, meeting){

                scope.meetings.push(meeting);
                scope.newMeetingPanel = -1;

            });

            scope.prepareNewMeeting();

            scope.$on('newMeetingClickEvent', function(){
                scope.newMeetingClick();
            })

            scope.newMeetingClick = function(){
                if(scope.newMeetingPanel == -1)
                {
                    scope.prepareNewMeeting();
                    scope.newMeetingPanel = 0;

                } else {
                    scope.newMeetingPanel = -1;

                }
            }

            scope.$on('meetingDeleteEvent', function(e, meetingId)
            {
                scope.meetingListPanel = -1;
                $timeout(function(){
                    var i = findWithAttr(scope.meetings, 'id', meetingId);
                    if(i >= 0) scope.meetings.splice(i,1);
                },100)
            })

            scope.$on('meetingArchiveEvent', function(e, meetingId){
                scope.meetingListPanel = -1;
                $timeout(function(){
                    var i = findWithAttr(scope.meetings, 'id', meetingId);
                    if(i >= 0) scope.meetings.splice(i,1);
                },100)
            })

            scope.$on('meetingCancelEvent', function(){
                scope.newMeetingPanel = -1;

            } )

        }

    }})