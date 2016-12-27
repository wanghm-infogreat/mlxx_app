// Ionic Starter App

// angular.module is a global place for creating, registering and retrieving Angular modules
// 'starter' is the name of this angular module example (also set in a <body> attribute in index.html)
// the 2nd parameter is an array of 'requires'
// 'starter.services' is found in services.js
// 'starter.controllers' is found in controllers.js
angular.module('starter', ['ionic',
  'angular-md5',
  'starter.controller.account',
  'starter.controller.dash',
  'starter.controller.bbs',
  'starter.controller.bank',
  'starter.controller.user',
  'starter.controller.message',
  'starter.controller.friend',
  'starter.controller.student',
  'starter.service.common.http',
  'starter.service.account',
  'starter.service.dash',
  'starter.service.bbs',
  'starter.service.bank',
  'starter.service.user',
  'starter.service.message',
  'starter.service.friend',
  'starter.service.student',
  'ngCordova'
])

.run(function($ionicPlatform, $state, $rootScope, $ionicPopup, Message, Friend) {
  $ionicPlatform.ready(function() {
    // Hide the accessory bar by default (remove this to show the accessory bar above the keyboard
    // for form inputs)
    if (window.cordova && window.cordova.plugins && window.cordova.plugins.Keyboard) {
      cordova.plugins.Keyboard.hideKeyboardAccessoryBar(true);
      cordova.plugins.Keyboard.disableScroll(true);

    }
    if (window.StatusBar) {
      // org.apache.cordova.statusbar required
      StatusBar.styleDefault();
    }
    
    // 保存当前用户的user
    user = localStorage.getItem("user");
    // 保存当前用户的token
    token = localStorage.getItem("token");
  
    // 判断是否已经登录
    if (user != null && token != null) {
      // 迁移到公告页面
      $state.go('tab.dash');
    } else {
      // 迁移到登录页面
      $state.go('login');
    }
    
    // 设定全局函数
    $rootScope.showUser = function(user) {

      // 设定用户信息
      $rootScope.user = user

      // 自定义弹窗:显示用户信息
      var myPopup = $ionicPopup.show({
        templateUrl: './templates/common/user.html',
        title: '用户信息',
        scope: $rootScope,
        buttons: [
          { text: '关闭' },
          { text: '+好友',
            onTap: function(e) {
    			// 新加好友
    			Friend.add($rootScope.user.id);
            }
		  },
          {
            text: '发消息',
            type: 'button-positive',
            onTap: function(e) {
              $ionicPopup.show({
        		templateUrl: './templates/common/message.html',
        		title: '发送消息',
   		     	scope: $rootScope,
        		buttons: [
          		{ text: '关闭' },
          		{
            		text: '发送',
            		type: 'button-positive',
            		onTap: function(e) {
              		if (!$rootScope.message) {
                		//必须输入消息内容
                		e.preventDefault();
              		} else {
                		// 发送消息
						Message.send($rootScope.user.id, $rootScope.message).then(function(data) {
			        		// 清除画面的输入内容
      						$rootScope.message = "";
			    		});
              		}
            		}
          		}
        		]
      		});
            }
          }
        ]
      });
	  
	  // 显示用户popup
      myPopup.then(function(res){});
    }
  });
})

/**
 * 输出html内容
 */
.filter('trustHtml', function($sce) {
  return function (input) {
    return $sce.trustAsHtml(input);
  }
})

/**
 * 输出url路径
 */
.filter('trustUrl', function($sce) {
  return function (input) {
    return $sce.trustAsResourceUrl(input);
  }
})

/**
 * 控制键盘
 */
.directive('keyboardshow', function($rootScope, $ionicPlatform, $timeout, $cordovaKeyboard) {
    return {
        restrict: 'A',
        link: function(scope, element, attributes) {
            window.addEventListener('native.keyboardshow',function (e){              
             
              angular.element(element).css({
                'bottom':e.keyboardHeight + 'px'
              });
            });

            window.addEventListener('native.keyboardhide',function (e){
                angular.element(element).css({
                  'bottom': '49px'
                });
            });
        }
    };
})

/**
 * 路径配置
 */
.config(function($stateProvider, $urlRouterProvider, $httpProvider) {

  // Ionic uses AngularUI Router which uses the concept of states
  // Learn more here: https://github.com/angular-ui/ui-router
  // Set up the various states which the app can be in.
  // Each state's controller can be found in controllers.js
  $stateProvider

  // setup an login state
  .state('login', {
    url: '/login',
    templateUrl: 'templates/login.html',
    controller: 'AccountCtrl'
  })

  // setup an abstract state for the tabs directive
  .state('tab', {
    url: '/tab',
    abstract: true,
    templateUrl: 'templates/tabs.html'
  })

  // Each tab has its own nav history stack:

  .state('tab.dash', {
    url: '/dash',
    views: {
      'tab-dash': {
        templateUrl: 'templates/dash/tab-dash.html',
        controller: 'DashCtrl'
      }
    }
  })

  .state('tab.dash-comment', {
    url: '/dash/comment/:dashid',
    views: {
      'tab-dash': {
        templateUrl: 'templates/dash/comment.html',
        controller: 'DashCommentCtrl'
      }
    }
  })

  .state('tab.bbs', {
      url: '/bbs',
      views: {
        'tab-bbs': {
          templateUrl: 'templates/bbs/tab-bbs.html',
          controller: 'BbsGroupCtrl'
        }
      }
    })
    .state('tab.bbs-title', {
      url: '/bbs/:groupId',
      views: {
        'tab-bbs': {
          templateUrl: 'templates/bbs/bbs-title.html',
          controller: 'BbsTitleCtrl'
        }
      }
    })
    .state('tab.bbs-favorite', {
      url: '/bbs/favorite/list',
      views: {
        'tab-bbs': {
          templateUrl: 'templates/bbs/bbs-favorite.html',
          controller: 'BbsFavoriteCtrl'
        }
      }
    })
    .state('tab.bbs-comment', {
      url: '/bbs/comment/:titleId',
      views: {
        'tab-bbs': {
          templateUrl: 'templates/bbs/bbs-comment.html',
          controller: 'BbsCommentCtrl'
        }
      }
    })

  .state('tab.bank', {
    url: '/bank',
    views: {
      'tab-bank': {
        templateUrl: 'templates/bank/tab-bank.html',
        controller: 'BankCtrl'
      }
    }
  })

  .state('tab.user', {
    url: '/user',
    views: {
      'tab-user': {
        templateUrl: 'templates/user/tab-user.html',
        controller: 'UserCtrl'
      }
    }
  })

  .state('tab.userinfo', {
    url: '/user/info',
    views: {
      'tab-user': {
        templateUrl: 'templates/user/userinfo.html',
        controller: 'UserCtrl'
      }
    }
  })

  .state('tab.account', {
    url: '/user/account',
    views: {
      'tab-user': {
        templateUrl: 'templates/user/account.html',
        controller: 'UserCtrl'
      }
    }
  })

  .state('tab.msguser', {
    url: '/user/msguser',
    views: {
      'tab-user': {
        templateUrl: 'templates/user/message/user.html',
        controller: 'MessageUserCtrl'
      }
    }
  })

  .state('tab.message', {
    url: '/user/message/:userid',
    views: {
      'tab-user': {
        templateUrl: 'templates/user/message/message.html',
        controller: 'MessageCtrl'
      }
    }
  })

  .state('tab.new-message', {
    url: '/user/message/new',
    views: {
      'tab-user': {
        templateUrl: 'templates/user/message/new.html',
        controller: 'NewMessageCtrl'
      }
    }
  })

  .state('tab.friends', {
    url: '/user/friends',
    views: {
      'tab-user': {
        templateUrl: 'templates/user/friend/friends.html',
        controller: 'FriendCtrl'
      }
    }
  })

  .state('tab.friends-search', {
    url: '/user/friends/search',
    views: {
      'tab-user': {
        templateUrl: 'templates/user/friend/newfriend.html',
        controller: 'FriendSearchCtrl'
      }
    }
  })

  .state('tab.student', {
    url: '/user/student',
    views: {
      'tab-user': {
        templateUrl: 'templates/user/student/student.html',
        controller: 'StudentCtrl'
      }
    }
  })

  .state('tab.evaluation', {
    url: '/user/evaluation/:studentid',
    views: {
      'tab-user': {
        templateUrl: 'templates/user/student/evaluation.html',
        controller: 'EvaluationCtrl'
      }
    }
  });

  // if none of the above states are matched, use this as the fallback
  // $urlRouterProvider.otherwise('/login');

  // 设定全局http请求的拦截器
  $httpProvider.interceptors.push(HttpInterceptor);

});
