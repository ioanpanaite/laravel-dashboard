<?php
  $public =  $_ENV['ROOTED_PUBLIC'] ? 'public/' : '';
  $ran = rand(1,999999);
?>
<!DOCTYPE html>
<html lang="en" ng-app="app" ng-cloak>
  <head>
    <meta charset="utf-8">
    <title><% Config::get('app.app_title', 'Linkr')%></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
      <%HTML::style($public.'build/'.env('THEME', 'default').'.min.css?'.$ran)%>
      <%HTML::style($public.'build/font-awesome.css')%>

      @if(file_exists($public.('build/custom.css')))
      <%HTML::style($public.'build/custom.css')%>
      @endif

  </head>
  <style>
        .aside-chat-panel {
            max-height: calc(100% - 75px);
            height: calc(100% - 75px);
            overflow-y: scroll !important;
            max-width: 400px;
            padding: 5px;
        }
        .search-bar {
            width: 70px;
        }
        .aside-btn-group > button {
            margin-left: -4px;
        }
        .url_iframe {
            height: 428px; 
            max-width: 100% !important; 
            display: block; 
            padding: 5px !important;
        }
        .url_iframe > div > iframe {
            width: 494px !important;
        }

        @media (min-width: 768px) { 
            .nav>li>a {
                padding: 12px 3px !important;
            }
            .navbar-nav>li {
                padding-left: 1px !important;
            }
        }
        @media (min-width: 1300px) { 
            .search-bar {
                width: 120px;
            }   
        }
        @media (min-width: 1400px) { 
            .search-bar {
                width: 175px;
            }   
        }
  </style>
<body ng-controller="mainCtrl">
<div id="mask" data-ng-show="false">
    <div id="loader">
    </div>
</div>

<div growl></div>
<header class="navbar navbar-inverse navbar-fixed-top bs-docs-nasv" role="banner">
    <div class="container-fluid row">
        <div class="navbar-header">

            <button class="navbar-toggle hidden-lg"
                    type="button"
                    data-template="partials/aside.html"
                    data-animation="am-fade-and-slide-left" data-placement="left" title="Menu" data-container="body" bs-aside="">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a href="./" class="navbar-brand"><% Config::get('app.app_title', 'Linkr')%></a>


        </div>
        <nav class="collapse navbar-collapse bs-navbar-collapse" role="navigation">
            <ul class="nav navbar-nav hidden-sm hidden-md">
                <li>
                    <a ui-sref="home"><i class="fa fa-home"></i> {{'HOME' | translate}}</a>
                </li>
                <li>
                    <a href="#/userprofile/<?php echo Auth::id(); ?>" class="ng-binding"><i class="fa fa-user"></i> {{'MY_DISPLAY_PROFILE' | translate}}</a>
                </li>
                <li>
                    <a href="javascript:void(0)"
                    data-template="partials/aside-spaces.html"
                    data-animation="am-fade-and-slide-left" data-placement="left"
                    data-container="body" bs-aside=""><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABIAAAASCAYAAABWzo5XAAAABmJLR0QA/wD/AP+gvaeTAAABAklEQVQ4ja2SLU5DQRhFzxTEwzSIGjCVJCALK8Ah+NF4QtgDSdeBIHQFVMIO2AAChyFFECDBkEByMAOZTub1vYZe9+Z998x3bwYWpJB+qOfAQc3sB3AaQnhopKpf1utOfVI32oBmaUe9Up/Vzf+ABmqnFawBtB9nltSR+ph687JtzJ+aQ/jzLxf+fwLHwBmwC9zHs19VwNbMG2KEV7WvXqoTtcpmqthR/fZJHzfqS0NnU6BSR5MQwro6jPGOCtHGQK+pozX1FtgGusBJAbSamzoF0Fs0j+sqKKm00QowAPrAO3BR2GgP6KWmhb2jPNr3HJyp2TzaEDgk27Qgges5Lm2vH/OZIyGxtkVLAAAAAElFTkSuQmCC"> {{'SPACES' | translate}}
                    </a>
                </li>
                <li>
                    <a href="javascript:void(0)"
                    data-template="partials/aside-questions.html"
                    data-animation="am-fade-and-slide-left" data-placement="left"
                    data-container="body" bs-aside=""><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABIAAAASCAYAAABWzo5XAAAABmJLR0QA/wD/AP+gvaeTAAABAklEQVQ4ja2SLU5DQRhFzxTEwzSIGjCVJCALK8Ah+NF4QtgDSdeBIHQFVMIO2AAChyFFECDBkEByMAOZTub1vYZe9+Z998x3bwYWpJB+qOfAQc3sB3AaQnhopKpf1utOfVI32oBmaUe9Up/Vzf+ABmqnFawBtB9nltSR+ph687JtzJ+aQ/jzLxf+fwLHwBmwC9zHs19VwNbMG2KEV7WvXqoTtcpmqthR/fZJHzfqS0NnU6BSR5MQwro6jPGOCtHGQK+pozX1FtgGusBJAbSamzoF0Fs0j+sqKKm00QowAPrAO3BR2GgP6KWmhb2jPNr3HJyp2TzaEDgk27Qgges5Lm2vH/OZIyGxtkVLAAAAAElFTkSuQmCC"> {{'MY_KNOW_HOW_QUESTIONS' | translate}}
                    </a>
                </li>
                <li>
                    <a href="javascript:void(0)"
                    data-template="partials/aside-initiatives.html"
                    data-animation="am-fade-and-slide-left" data-placement="left"
                    data-container="body" bs-aside=""><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABIAAAASCAYAAABWzo5XAAAABmJLR0QA/wD/AP+gvaeTAAABAklEQVQ4ja2SLU5DQRhFzxTEwzSIGjCVJCALK8Ah+NF4QtgDSdeBIHQFVMIO2AAChyFFECDBkEByMAOZTub1vYZe9+Z998x3bwYWpJB+qOfAQc3sB3AaQnhopKpf1utOfVI32oBmaUe9Up/Vzf+ABmqnFawBtB9nltSR+ph687JtzJ+aQ/jzLxf+fwLHwBmwC9zHs19VwNbMG2KEV7WvXqoTtcpmqthR/fZJHzfqS0NnU6BSR5MQwro6jPGOCtHGQK+pozX1FtgGusBJAbSamzoF0Fs0j+sqKKm00QowAPrAO3BR2GgP6KWmhb2jPNr3HJyp2TzaEDgk27Qgges5Lm2vH/OZIyGxtkVLAAAAAElFTkSuQmCC"> {{'INITIATIVES' | translate}}
                    </a>
                </li>          
                @IF(!env('HIDE_MEETINGS', false))
                <li>
                    <a ui-sref="meetings"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABIAAAASCAYAAABWzo5XAAAABmJLR0QA/wD/AP+gvaeTAAABZElEQVQ4jb2QMUtcQRhFz7wIWwjZtUkg1iktDMIiGOvElCGQf5B0WqVJwE4I+APsrRQjGzAQUipIirBrF4U0WpliV8EmoO6xmY2z4+6zEPyqmbnn3e/eB4BaVb+pZ+qaWmHIqBV1PbJb6sNU/GT/vCsxep+xHwGKqI9mfH4v067v6rh6EDc01VpJojG1Fdl99UkO1NQZ9VFJmh5bjezjXFhUz+OWy17vISbzCdtVP/eEZ/Ehna46O8BkUr3w5kyhNtTfajsTW+qDxCSouxlzqh6qXwpgGmgA3WT5D2ACWFEn49syUAe2E+4c2ACmCuA78CJrsQcsAK+AproKjAAfgJ8ZOwd8RX0bY/5LIi/FOoX6XK0nFZeyf6n6cgT4BWzGKk8j3wEIIXSBnSxBJzkfxu9bRQjhTwjhNfA3AdoMn9ToKITwJoRwXAwByozag87FbcAtRv+X39XofhIJnJQYdSLTZ3oFV7SCKyZaKt0AAAAASUVORK5CYII="> {{'MEETINGS' | translate}}</a>
                </li>
                @ENDIF                
                @IF(!env('HIDE_TASKS', false))
                <li>
                    <a ui-sref="tasks"><i class="fa fa-tasks"></i> {{'TASKS' | translate}}</a>
                </li>
                @ENDIF
                @IF(!env('HIDE_CALENDAR', false))
                <li>
                    <a ui-sref="calendar"><i class="fa fa-calendar"></i> {{'CALENDAR' | translate}}</a>
                </li>
                @ENDIF
                @IF(!env('HIDE_PEOPLE', false))
                <li>
                    <a ui-sref="people"><i class="fa fa-users"></i> {{'PEOPLE' | translate}}</a>
                </li>                
                @ENDIF
                <li>
                    <a ui-sref="questions"><i class="fa fa-question-circle"></i> {{'QUESTIONS' | translate}}</a>
                </li>
                <li>
                    <a ui-sref="projects"><i class="fa fa-sliders" style="transform: rotate(90deg)"></i> {{'PROJECTS' | translate}}</a>
                </li>

            </ul>
            <ul class="nav navbar-nav navbar-right">
                <li>
                    <span us-spinner="{width:2, length:4, shadow:on, color:'#fff', hwaccel:on,radius:7}" spinner-key="appSpinner"></span>
                </li>

                <li class="search-bar">
                    <input type="search" class="form-control" id='search-box' placeholder=" {{'SEARCH' | translate}}" ng-model="search.text" ng-enter="doSearch()">

                </li>
                <li>
                    <a ui-sref="messages" style="padding-left: 0 !important;padding-right: 0 !important;">&nbsp;<i class="fa fa-envelope"></i>&nbsp;<span ng-if='msgCount>0' class="badge badge-white">{{msgCount}}</span></a>
                </li>
                <li>
                    <a class="dropdown-toggle" style="padding-left: 0 !important;padding-right: 0 !important;" data-toggle="dropdown" href="javascript:void(0)"><i class='fa fa-user'></i> <span class="caret"></span></a>
                    <ul class="dropdown-menu">
                        @if(Auth::user()->admin)
                        <li><a ui-sref="admin">{{'SITE_ADMIN' | translate}}</a></li>
                        @endif
                        <li><a ui-sref="profile">{{'MY_PROFILE' | translate}}</a></li>
                        <li class="divider"></li>
                        <li><a href="<%URL::to('/logout')%>">{{'LOGOUT' | translate}}</a></li>
                    </ul>
                </li>
            </ul>
        </nav>
    </div>
</header>
<div ui-view></div>
<%HTML::script($public.'build/lib.min.js')%>
@if(App::environment()=='local')
<!-- DEV MODE--------------------------------- -->
<%HTML::script($public.'dev/app/util.js')%>
<%HTML::script($public.'dev/app/app.js')%>
<%HTML::scripts($public.'dev/app/controllers')%>
<%HTML::scripts($public.'dev/app/directives')%>
<%HTML::scripts($public.'dev/app/services')%>
<%HTML::scripts($public.'dev/app/filters')%>
<!-- END DEV MODE--------------------------------- -->
@else
<%HTML::script($public.'build/app.min.js')%>
@endif

<%HTML::script($public.'dev/app/app.js')%>
<%HTML::script($public.'dev/app/controllers/admin-ctrl.js')%>
<%HTML::script($public.'dev/app/controllers/calendar-ctrl.js')%>
<%HTML::script($public.'dev/app/controllers/home-ctrl.js')%>
<%HTML::script($public.'dev/app/controllers/main-ctrl.js')%>
<%HTML::script($public.'dev/app/controllers/messages-ctrl.js')%>
<%HTML::script($public.'dev/app/controllers/people-ctrl.js')%>
<%HTML::script($public.'dev/app/controllers/questions-ctrl.js')%>
<%HTML::script($public.'dev/app/controllers/post-ctrl.js')%>
<%HTML::script($public.'dev/app/controllers/profile-ctrl.js')%>
<%HTML::script($public.'dev/app/controllers/single-task-ctrl.js')%>
<%HTML::script($public.'dev/app/controllers/space-admin-ctrl.js')%>
<%HTML::script($public.'dev/app/controllers/space-files-ctrl.js')%>
<%HTML::script($public.'dev/app/controllers/space-stream-ctrl.js')%>
<%HTML::script($public.'dev/app/controllers/space-wiki-ctrl.js')%>
<%HTML::script($public.'dev/app/controllers/space-ctrl.js')%>
<%HTML::script($public.'dev/app/controllers/meeting-ctrl.js')%>
<%HTML::script($public.'dev/app/controllers/tasks-ctrl.js')%>
<%HTML::script($public.'dev/app/controllers/userprofile-ctrl.js')%>
<%HTML::script($public.'dev/app/controllers/idea-ctrl.js')%>
<%HTML::script($public.'dev/app/controllers/initiative-ctrl.js')%>
<%HTML::script($public.'dev/app/controllers/initiative-stream-ctrl.js')%>
<%HTML::script($public.'dev/app/controllers/initiative-files-ctrl.js')%>
<%HTML::script($public.'dev/app/controllers/initiative-wiki-ctrl.js')%>
<%HTML::script($public.'dev/app/controllers/idea-stream-ctrl.js')%>
<%HTML::script($public.'dev/app/controllers/idea-files-ctrl.js')%>
<%HTML::script($public.'dev/app/controllers/idea-wiki-ctrl.js')%>
<%HTML::script($public.'dev/app/controllers/search-ctrl.js')%>
<%HTML::script($public.'dev/app/controllers/projects-ctrl.js')%>

<%HTML::script($public.'dev/app/directives/chart-editor.js')%>
<%HTML::script($public.'dev/app/directives/chart-viewer.js')%>
<%HTML::script($public.'dev/app/directives/column.js')%>
<%HTML::script($public.'dev/app/directives/comments.js')%>
<%HTML::script($public.'dev/app/directives/confirm-popup.js')%>
<%HTML::script($public.'dev/app/directives/content.js')%>
<%HTML::script($public.'dev/app/directives/editable-text.js')%>
<%HTML::script($public.'dev/app/directives/event-editor.js')%>
<%HTML::script($public.'dev/app/directives/event-viewer.js')%>
<%HTML::script($public.'dev/app/directives/field.js')%>
<%HTML::script($public.'dev/app/directives/field-radio.js')%>
<%HTML::script($public.'dev/app/directives/field-text.js')%>
<%HTML::script($public.'dev/app/directives/field-textarea.js')%>
<%HTML::script($public.'dev/app/directives/file-viewer.js')%>
<%HTML::script($public.'dev/app/directives/likes.js')%>
<%HTML::script($public.'dev/app/directives/link-viewer.js')%>
<%HTML::script($public.'dev/app/directives/location-editor.js')%>
<%HTML::script($public.'dev/app/directives/location-viewer.js')%>
<%HTML::script($public.'dev/app/directives/message-item.js')%>
<%HTML::script($public.'dev/app/directives/morris-chart.js')%>
<%HTML::script($public.'dev/app/directives/poll-editor.js')%>
<%HTML::script($public.'dev/app/directives/poll-viewer.js')%>
<%HTML::script($public.'dev/app/directives/pop.js')%>
<%HTML::script($public.'dev/app/directives/pop-url.js')%>
<%HTML::script($public.'dev/app/directives/table-editor.js')%>
<%HTML::script($public.'dev/app/directives/table-viewer.js')%>
<%HTML::script($public.'dev/app/directives/meeting.js')%>
<%HTML::script($public.'dev/app/directives/meeting-list.js')%>
<%HTML::script($public.'dev/app/directives/task.js')%>
<%HTML::script($public.'dev/app/directives/task-list.js')%>
<%HTML::script($public.'dev/app/directives/fileread.js')%>
<%HTML::script($public.'dev/app/directives/transfer-click.js')%>

<%HTML::script($public.'dev/app/services/api.js')%>
<%HTML::script($public.'dev/app/services/content.js')%>
<%HTML::script($public.'dev/app/services/formatter.js')%>
<%HTML::script($public.'dev/app/services/services.js')%>
<%HTML::script($public.'dev/app/services/space.js')%>
<%HTML::script($public.'dev/app/services/star.js')%>
<%HTML::script($public.'dev/app/services/consts.js')%>
<%HTML::script($public.'dev/app/services/initiative.js')%>
<%HTML::script($public.'dev/app/services/idea.js')%>

<%HTML::script($public.'dev/app/filters/joinby.js')%>

<%HTML::script($public.'build/app.tpls.min.js')%>
<%HTML::script('lang')%>

<%HTML::script($public.'build/ng-youtube-embed.min.js')%>

<script type="text/javascript">
    var user = {id:<%Auth::user()->id%>, fullname:'<%addslashes(Auth::user()->full_name)%>', cs: <% Auth::user()->create_spaces;%>};
    var apiUrl = "<%URL::to('api/v1');%>";
    var siteUrl = "<%URL::to('/');%>";
    var assetsUrl = "<%URL::to($public.'assets');%>";
    var token = "<%csrf_token() %>";
    var modules = [];
    var hm = <% intval( env('HIDE_MODERATORS', false)); %>;
    var showPhone = <% intval( env('PEOPLE_SHOW_PHONE', false)); %>;
    var showOrg = <% intval( env('PEOPLE_SHOW_ORG', false)); %>;
    var profileFields = <% json_encode(["facebook"=> env('PROFILE_FACEBOOK', true), "google"=> env('PROFILE_GOOGLE', true),
        "linkedin"=> env('PROFILE_LINKEDIN', true), "twitter"=> env('PROFILE_TWITTER', true), "github"=> env('PROFILE_GITHUB', true), "skype"=> env('PROFILE_SKYPE', true)]);
    %>;
    var locale = "<%Config::get('app.locale')%>";
    var snd = new Audio("<%URL::to($public.'assets/sounds/click.mp3');%>");
    var sndBell = new Audio("<%URL::to($public.'assets/sounds/bell.mp3');%>");
    var appVersion = "<%Config::get('app.version')%>";
    $(document).delegate('*[data-toggle="lightbox"]', 'click', function(event) {
        event.preventDefault();
        $(this).ekkoLightbox();
    });
</script>
<%HTML::script('modules')%>

</body>
</html>