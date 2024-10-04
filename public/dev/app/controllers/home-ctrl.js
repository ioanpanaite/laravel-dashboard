app.controller('homeCtrl', function ($window, $scope, $state, $upload, $cacheFactory, $timeout, $sce, spaceFactory, formatterService, $modal, apiService, usSpinnerService) {
    $scope.formatter = formatterService;

    $scope.spaces = [];
    $scope.urlState = 0;
    $scope.urlContent = null;
    $scope.attachFileId = false;

    /**
     * News Post
     */
    $scope.newsContent = "";
    $scope.submitNews = function () {
        if ($scope.newsContent) {
            var imageUrl = $scope.urlContent != null ? $scope.urlContent.imageURL : false;
            apiService.post(['news'], { news: $scope.newsContent, urlImage: imageUrl, fileId: $scope.attachFileId }).then(function (res) {
                $scope.newsContent = "";
                $scope.attachFileId = false;
                $scope.newscontent = res; // This is defferent variable.
            }, function (err) {
                $scope.error = err;
            });
        }
    }

    $scope.$watch(function () {
        return $scope.newsContent;
    }, function (newVal, oldVal) {
        if (newVal == oldVal) { return; }
        if (newVal == "") {
            $scope.urlState = 0;
            $scope.urlContent = null;
        }
        $scope.newsContent = newVal;
    }, true);

    $scope.canCreateSpace = function () {
        return user.cs == 1;
    }

    $scope.editIdeaOkClick = function () {
        apiService.post(['idea'], $scope.editIdea).then(
            function (res) {
                $scope.ideaForm.hide();
                $scope.$emit('ideaListChanged');
                document.location.href = '#/idea/' + res;
                //$state.go('idea.stream', {ideaCode: res});

            }, function (err) {
                $scope.error = err;
            }
        )
    }

    $scope.onFileSelect = function ($files) {

        var file = $files[0];
        usSpinnerService.spin('avatarSpinner');

        $upload.upload({
            url: apiUrl + '/profile/avatar',
            file: file
        })
            .success(function (data, status, headers, config) {

                $timeout(function () {
                    var av = Math.floor((Math.random() * 1000000) + 1);

                    $scope.profile.avatar = $scope.avatar = formatterService.getAvatar($scope.profile.id) + '?' + av;
                    usSpinnerService.stop('avatarSpinner');
                    //location.reload(true);
                }, 1000)
            }).error(function () {
                usSpinnerService.stop('avatarSpinner');

            })
    }

    $scope.zoomClick = function (contentId) {
        $state.go('post', { contentId: contentId });
    }

    $scope.testClick = function () {
        spaceFactory.loadSpaces().then(
            function (res) {
                $scope.activity = res;
            });

    }

    $scope.joinClick = function (code) {

        apiService.put(['space', code, 'join']).then(
            function () {
                $state.go('space.stream', { spaceCode: code });
                $scope.$emit('spaceListChanged');
            });
    }

    $scope.icons = ['fa fa-comment', 'fa fa-link', 'fa fa-table', 'fa fa-bar-chart', 'fa fa-check-square', 'fa fa-calendar', 'fa fa-map-marker'];

    $scope.getLogoutUrl = function () {
        return siteUrl + '/logout';
    }

    $scope.gotoSpace = function (code) {
        $timeout(function () {
            $state.go('space.stream', { spaceCode: code });

        });
    }

    $scope.loadSpaces = function () {
        apiService.get('home').then(
            function (res) {
                var color = $('.btn-info').css("background-color");
                $scope.spaces = res.spaces;
                $scope.join = res.join;
                $timeout(function () {
                    angular.forEach(res.spaces, function (space) {
                        //8ED2F3
                        $('#spark_' + space.code).sparkline(space.counter, {
                            disableTooltips: true, disableHighlight: true, type: 'bar',
                            barColor: color,
                            height: '40px', barWidth: '15', barSpacing: '2'
                        });

                    });


                })
            }
        )
    }

    $scope.loadContent = function () {
        apiService.get('home/content').then(
            function (res) {
                $scope.contents = res;
            }
        )
    }

    $scope.loadNewsPosts = function () {
        apiService.get('home/newscontent').then(
            function (res) {
                $scope.newscontent = res;
            }
        )
    }

    $scope.getURLContent = function (data) {
        if (data.images.length > 0) {
            if (data.images[0].url) {
                var imageUrl = (data.images[0].mime == "image/jpeg" || data.images[0].mime == "image/png") ? data.images[0].url : false;
                if (imageUrl && data.images[0].width > 150)
                    return {title: data.title, imageURL: imageUrl, url: data.imageWidth.url};
                else
                    return {title: data.title, imageURL: data.providerIcon, url: data.imageWidth.url};
            }
        } else {
            return {title: data.title, imageURL: data.providerIcon, url: data.imageWidth.url};
        }
    }

    $scope.loadNewsURLPosts = function ($url) {
        apiService.get('home/newsurlcontent', { url: $url }).then(
            function (res) {
                $scope.urlContent = $scope.getURLContent(res);
            }
        )
    }

    $scope.handleURL = function () {
        if ($scope.newsContent != null && $scope.urlState == 0) {
            var urlRegex = /(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig;
            $scope.newsContent.replace(urlRegex, function (val) {
                $scope.urlState = 1;
                $scope.loadNewsURLPosts(val);
            });
        }
    }

    $scope.urlClose = function () {
        $scope.urlState = 0;
        $scope.urlContent = null;
        $scope.iframeContent = null;
    }

    
    $scope.onNewsFileSelect = function ($files) {

        usSpinnerService.spin('fileAttachSpinner');
        var file = $files[0];
        $scope.attachFileId = false;

        $upload.upload({
            url: apiUrl + '/news/attachfile',
            file: file
        })
        .success(function (data, status, headers, config) {
            $timeout(function () {
                $scope.attachFileId = data.id;
                usSpinnerService.stop('fileAttachSpinner');
                //location.reload(true);
            }, 500)
        }).error(function () {
            usSpinnerService.stop('fileAttachSpinner');

        })
    }

    $scope.events = [];
    if (!h_cal) {
        apiService.get(['event'], { view: 'short' }).then(
            function (res) {
                $scope.events = res;
            }
        )
    }

    $scope.meetings = null;

    apiService.get('meeting/counters').then(
        function (data) {
            $scope.meetings = data;
        }
    )

    $scope.tasks = null;

    apiService.get('task/counters').then(
        function (data) {
            $scope.tasks = data;

        }
    )

    $scope.usersactivities = null;

    apiService.get('home/usersactivities').then(
        function (data) {
            $scope.usersactivities = data;

        }
    )

    $scope.loadSpaces();
    // $scope.loadContent();
    $scope.loadNewsPosts();

})
