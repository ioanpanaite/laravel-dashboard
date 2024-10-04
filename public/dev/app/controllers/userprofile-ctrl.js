app.controller('userprofileCtrl', function($scope,  formatterService, $upload, apiService,usSpinnerService, $stateParams, $timeout, $state, $modal, consts, $sce){

    $scope.wrongImageExtension = consts.WRONG_IMAGE_EXTENSION;
    $scope.wrongVideoExtension = consts.WRONG_VIDEO_EXTENSION;
    $scope.formatter = formatterService;
    $scope.icons = [ 'fa fa-comment', 'fa fa-link', 'fa fa-table', 'fa fa-bar-chart', 'fa fa-check-square', 'fa fa-calendar', 'fa fa-map-marker'];
    $scope.privateMessage = '';

    $scope.userid =  $stateParams['userid'];

    apiService.get(['profile',  $scope.userid]).then(
        function(res){
            $scope.profile = res;
            $scope.avatar = formatterService.getAvatar($scope.profile.id);
            if (angular.isDefined(res.custom_fields)) {
                for(var i = 0; i<res.custom_fields.length; i++)
                {
                    if(res.custom_fields[i].type == 'date')
                        $scope.profile['custom_'+res.custom_fields[i].name] = moment($scope.profile['custom_'+res.custom_fields[i].name]).format('L');
                }
            }
        }
    );

    $scope.limitToImages = consts.NUMBER_OF_PREVIEWS_IN_WINDOW;
    $scope.moreImages = function() {
        $scope.limitToImages += consts.NUMBER_OF_PREVIEWS_IN_WINDOW;
    };

    $scope.isShowMoreImagesButton = function() {
        return $scope.limitToImages < $scope.profile.imagePreviews.length && $scope.profile.imagePreviews.length > consts.NUMBER_OF_PREVIEWS_IN_WINDOW
    };

    $scope.isImageValid = true;
    $scope.onFileSelect = function($files){
        if (!isImagesValid($files)) {
            $scope.isImageValid = false;
            return false;
        }
        $scope.isImageValid = true;
        var file = $files[0];
        usSpinnerService.spin('avatarSpinner');

        $upload.upload({
            url: apiUrl + '/image',
            file: file})
            .success(function(data, status, headers, config) {
                $timeout(function() {
                    var av = Math.floor((Math.random() * 1000000) + 1);
                    usSpinnerService.stop('avatarSpinner');
                    location.reload(true);
                },1000)
            }).error(function(){
                usSpinnerService.stop('avatarSpinner');
            })
    };


    function isImagesValid(files) {
        for (var i=0; i<files.length; i++) {
            if (files[i].type != "image/jpeg" && files[i].type != "image/png" && files[i].type != "image/gif") {
                return false;
            }
        }
        return true;
    }

    $scope.isVideoValid = true;
    $scope.onVideoSelect = function($files){
        if (!isVideosValid($files)) {
            $scope.isVideoValid = false;
            return false;
        }
        $scope.isVideoValid = true;
        var file = $files[0];
        usSpinnerService.spin('avatarSpinner');

        $upload.upload({
            url: apiUrl + '/video',
            file: file})
            .success(function(data, status, headers, config) {

                $timeout(function() {
                    var av = Math.floor((Math.random() * 1000000) + 1);

                    usSpinnerService.stop('avatarSpinner');
                    //location.reload(true);
                },1000)
            }).error(function(){
                usSpinnerService.stop('avatarSpinner');
            })
    };

    function isVideosValid(files) {
        for (var i=0; i<files.length; i++) {
            if (isNotAllowedVideoFile(files[i])) {
                return false;
            }
        }
        return true;
    }

    function isNotAllowedVideoFile(file) {
        return file.type != "video/mp4" && file.name.substr(-4) != ".mkv" && file.name.substr(-4) != ".flv";
    }

    apiService.get(['profile/activity',  $scope.userid]).then(
        function(res){
            $scope.contents = res;
        }
    );

    $scope.gotoSpace = function(code)
    {
        $state.go('space.stream', {spaceCode: code});
    };

    $scope.zoomClick = function(contentId){
        $state.go('post', {contentId: contentId});
    };

    $scope.sendMessageClick = function(){

        if($scope.privateMessage == '') return;

        apiService.post('message', {to_id: $scope.userid, body: $scope.privateMessage}).then(
            function(res){
                $scope.privateMessage = '';

            }
        )
    };
	
    $scope.sendReviewClick = function(){

        if($scope.review == '') return;

        apiService.post('review', {to_id: $scope.userid, content: $scope.review}).then(
            function(res){
                $scope.review = '';

            }
        )
    };
	
    $scope.sendCommentClick = function(){

        if($scope.comment == '') return;

        apiService.post('comment', {to_id: $scope.userid, content: $scope.comment}).then(
            function(res){
                $scope.comment = '';

            }
        )
    };


    $scope.openVideosModal = function () {
        $scope.spaceForm = $modal({
            template: 'partials/modal-user-videos.html',
            title: 'User videos',
            scope: $scope,
            animation: 'am-fade-and-slide-top',
            show: true
        });
    };

    $scope.openImagesModal = function () {
        $scope.spaceForm = $modal({
            template: 'partials/modal-user-images.html',
            title: 'User images',
            scope: $scope,
            animation: 'am-fade-and-slide-top',
            show: true
        });
    };

    $scope.openImageModal = function (url) {
        $scope.singleImageUrl = url;
        $scope.spaceForm = $modal({
            template: 'partials/modal-user-image.html',
            title: 'User image',
            scope: $scope,
            animation: 'am-fade-and-slide-top',
            show: true
        });
    };

    $scope.isMyPage = function(){
        return angular.isDefined($scope.profile) ? $scope.profile.is_follow_show == 0 : false;
    };

    $scope.firmInformation = [
        {
            label: "Trade name of organization",
            text: "Test name"
        },
        {
            label: "Trade registration number",
            text: "Test number"
        },
        {
            label: "Country",
            text: "Test country"
        },
        {
            label: "Website address",
            text: "www.test.com"
        },
        {
            label: "Headquarters address",
            text: "Test address"
        },
        {
            label: "Businesses core know how",
            text: "Test"
        },
        {
            label: "Machinery Equipment an other fixed",
            text: "Test name"
        },
        {
            label: "ISO certification",
            text: "Test"
        },
        {
            label: "Other qualifications and certificates",
            text: "Test"
        },
        {
            label: "Add References and examples of previous completed jobs",
            text: "Test"
        },
        {
            label: "Market sectors catering to",
            text: "Test"
        },
        {
            label: "Type of job orders looking for on Cocreation space",
            text: "Test"
        },
        {
            label: "Preferred working method",
            text: "Test"
        }
    ];

    // Video Link upload
    $scope.videoLink = '';
    $scope.videoLinkUpload = function() {
        apiService.post('video', {link: $scope.videoLink}).then(function(res) {
            $scope.videoLink = '';
            $scope.profile.videoPreviews = res;
        });
    };

    // VIDEO PLAYER READY TO INCLUDE

    //$scope.config = {
    //    preload: "none",
    //    sources: [
    //        {src: $sce.trustAsResourceUrl("http://static.videogular.com/assets/videos/videogular.mp4"), type: "video/mp4"},
    //        {src: $sce.trustAsResourceUrl("http://static.videogular.com/assets/videos/videogular.webm"), type: "video/webm"},
    //        {src: $sce.trustAsResourceUrl("http://static.videogular.com/assets/videos/videogular.ogg"), type: "video/ogg"}
    //    ],
    //    tracks: [
    //        {
    //            src: "http://www.videogular.com/assets/subs/pale-blue-dot.vtt",
    //            kind: "subtitles",
    //            srclang: "en",
    //            label: "English",
    //            default: ""
    //        }
    //    ],
    //    theme: {
    //        url: "http://www.videogular.com/styles/themes/default/latest/videogular.css"
    //    }
    //};

    //<videogular vg-theme="config.theme.url">
    //    <vg-media vg-src="config.sources"
    //    vg-tracks="config.tracks"
    //    vg-native-controls="true">
    //    </vg-media>
    //</videogular>

    // VIDEO PLAYER READY TO INCLUDE

});




