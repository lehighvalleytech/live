/**
 * certainly not the best example of an angular app
 */

var lvtr = angular.module('lvtechradio', []);

lvtr.controller('FeedCtrl', ['$scope', '$http', function($scope, $http){
    $http.get('/feed/lvtech.json').success(function(data){
        angular.forEach()
        $scope.lvtech = data;
    });

    $http.get('/feed/developers.json').success(function(data){
        $scope.developers = data;
    });
}]);


/**
 * controler for the live player
 */
lvtr.controller('LiveCtrl', ['$scope', '$http', '$timeout', function($scope, $http, $timeout){

    //check if stream is online, until it's online
    var pollForStream = function(){
        $http.get('/live.json').success(function(data){
            console.log(data);
            if(data.online){
                $scope.onair = true;
                return;
            }

            $timeout(function(){
                pollForStream();
            }, 10000);
        });
    }

    pollForStream();

    var audio = new Audio();

    var pollPlay = function(){
        if(0 == audio.currentTime){
            audio.play();
            $timeout(function(){
                pollPlay();
            }, 1000);
        } else {
            $scope.playing = true;
            pollStatus();
        }
    }

    var lastTime = 0;
    var pollStatus = function(){
        if(!$scope.playing){
            return;
        }

        if((lastTime + 10) < audio.currentTime){
            $scope.stop();
            return;
        }

        lastTime = audio.currentTime;

        $timeout(function(){
            pollStatus();
        }, 1000);
    }

    $scope.play = function()
    {
        $scope.status = 'Buffering...';

        if (audio.canPlayType('audio/mpeg;')) {
            audio.setAttribute('src', $scope.stream);
        } else {
            audio.setAttribute('src', $scope.stream + 'ogg');
        }

        pollPlay();
    }

    $scope.stop = function()
    {
        $scope.status = 'Listen Live';
        audio.setAttribute('src', 'about:blank');
        $scope.playing = false;
    }

    $scope.up = function()
    {
        if(audio.volume < 1){
            audio.volume = Math.round((audio.volume + .1)*10)/10;
        }
    }

    $scope.down = function()
    {
        if(audio.volume > 0){
            audio.volume = Math.round((audio.volume - .1)*10)/10;
        }
    }

    $scope.stream = "http://a2sw.bytecost.com:8000/lvtechradio"; //should be configurable
    $scope.playing = false;
    $scope.status = 'Listen Live';
}]);