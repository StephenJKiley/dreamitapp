define(['angular'], function(angular){

	'use strict';

	angular.module('Controllers')
		.controller('IdeaCtrl', [
			'$scope',
			'$rootScope',
			'$state',
			'$location',
			'$dialog',
			'IdeasServ',
			'CommentsServ',
			'LikeServ',
			'AppIdeasServ',
			'dialog',
			function($scope, $rootScope, $state, $location, $dialog, IdeasServ, CommentsServ, LikeServ, AppIdeasServ, dialog){

				//if dialog is passed in, we're inside an overlay and we need the ideaId and locationParamsAndHash
				if(dialog){

					$rootScope.viewingOverlay = true;
					var ideaId = dialog.options.customOptions.ideaId;
					var ideaUrl = dialog.options.customOptions.ideaUrl;
					var locationParamsAndHash = dialog.options.customOptions.locationParamsAndHash;
					$scope.closeOverlay = function(){
						$rootScope.viewingOverlay = false;
						dialog.close(locationParamsAndHash);
					};

				//else we only need ideaId and ideaUrl
				}else{

					$rootScope.viewingOverlay = false;
					var ideaId = $state.params.ideaId;
					var ideaUrl = $state.params.ideaUrl;
					$scope.closeOverlay = angular.noop;

				}

				$scope.idea = {};

				IdeasServ.get(
					{
						id: ideaId
					},
					function(response){

						//if the url passed into stateParams was different, we're going to make sure its the correct url
						if(!dialog && ideaUrl !== response.content.titleUrl){
							$location.path('ideas' + '/' + ideaId + '/' + response.content.titleUrl);
						}

						$scope.idea = response.content;

					},
					function(response){

						$scope.notFoundError = response.data.content;

					}
				);

				/**
				 * Plus one or minus one like
				 * @param  {Integer} ideaId Id of the idea
				 * @return {Void}
				 */
				$scope.likeAction = function(ideaId){

					if(!$rootScope.loggedIn){
						return false;
					}

					LikeServ.update(
						{
							id: ideaId
						},
						false,
						function(response){
							LikeServ.get({
								id: ideaId
							}, function(response){
								$scope.idea.likes = response.content.likes;
								if(dialog){
									AppIdeasServ.replaceProperty(ideaId, 'likes', response.content.likes);
								}
							});
						}
					);

				};

				/**
				 * Handles the tag links, if it's in an overlay, it will implement the tag query parameter and close
				 * the overlay. This will happen at the home. If not in an overlay, it will just do nothing. And the
				 * link will redirect to the home.
				 * @param  {String} tag Tag string
				 * @return {Void}
				 */
				$scope.tagAction = function(tag){

					if(dialog){
						locationParamsAndHash.tags = tag;
						$scope.closeOverlay();
					}

				};

				$scope.contactAuthor = function(authorId, ideaId){

					var dialog = $dialog.dialog({
						backdrop: false,
						keyboard: false,
						dialogClass: 'modal',
						templateUrl: 'developer_contact.html',
						controller: 'DeveloperContactCtrl',
						customOptions: {
							ideaId: ideaId,
							ideaUrl: ideaUrl,
							ideaTitle: $scope.idea.title,
							author: $scope.idea.author,
							authorId: $scope.idea.authorId
						}
					});

					dialog.open();

				};

				//////////////////
				// EDIT OVERLAY //
				//////////////////

				$scope.loggedInAndOwns = function(){
					if(($rootScope.loggedIn && $scope.idea.authorId == $rootScope.user.id) || $rootScope.loggedInAdmin){
						return true;
					}
					return false;
				};

				$scope.openEditIdeaOverlay = function(){

					if(dialog){
						$scope.closeOverlay();
					}
					$state.transitionTo('editIdea', {ideaId: ideaId, ideaUrl: ideaUrl});

				};

			}
		])
		.controller('CommentsCtrl', [
			'$scope',
			'$rootScope',
			'CommentsServ',
			'AppIdeasServ',
			function($scope, $rootScope, CommentsServ, AppIdeasServ){

				var limit = 20,
					counterOffset = 0;

				$scope.comments = [];

				$scope.commentsServiceBusy = false;

				$scope.loggedInAndComments = false;

				if($rootScope.loggedIn){
					$scope.loggedInAndComments = true;
				}

				$scope.getComments = function(ideaId){

					$scope.commentsServiceBusy = true;

					var queryParameters = {
						limit: limit,
						offset: counterOffset,
						idea: ideaId
					};

					CommentsServ.get(
						queryParameters,
						function(response){

							//increase the counterOffset
							counterOffset = counterOffset + limit;
							$scope.comments.push.apply($scope.comments, response.content);
							$scope.commentsServiceBusy = false;
							$scope.loggedInAndComments = true;

						},
						function(response){

							$scope.commentsServiceBusy = false;

						}
					);

				};

				$scope.submitComment = function(ideaId){
					
					var newComment = {
						ideaId: ideaId,
						authorId: $rootScope.user.id,
						comment: $scope.comment
					};

					CommentsServ.save({}, newComment, function(response){

						$scope.successSubmit = 'Successfully added Comment!';
						CommentsServ.get({
							id: response.content
						}, function(response){

							//so that the query won't get the most recently added comment
							counterOffset++;
							$scope.comments.unshift(response.content);
							$scope.$parent.idea.commentCount++;
							//if we are in an overlay, we update AppIdeasServ
							if($rootScope.viewingOverlay){
								AppIdeasServ.replaceProperty(ideaId, 'commentCount', $scope.$parent.idea.commentCount);
							}

						}, function(response){

							$scope.validationErrors = ['Was not able to read the new comment. Try submitting again.'];

						});

					}, function(response){

						$scope.validationErrors = [];
						if(response.data.code = 'validation_error'){
							for(var key in response.data.content){
								$scope.validationErrors.push(response.data.content[key]); 
							}
						}else{
							$scope.validationErrors = [response.data.content];
						}

					});

				};

			}
		])
		.controller('DeveloperContactCtrl', [
			'$scope',
			'$rootScope',
			'$timeout',
			'EmailServ',
			'dialog',
			function($scope, $rootScope, $timeout, EmailServ, dialog){

				var author = dialog.options.customOptions.author,
					authorId = dialog.options.customOptions.authorId,
					ideaId = dialog.options.customOptions.ideaId,
					ideaUrl = dialog.options.customOptions.ideaUrl,
					ideaTitle = dialog.options.customOptions.ideaTitle;

				$scope.closeOverlay = function(){
					dialog.close();
				};

				//for the template
				$scope.author = dialog.options.customOptions.author;

				//bring authorEmail based on authorId
				var currentUser = $rootScope.user.username;
				var currentUserEmail = $rootScope.user.email;

				$scope.submitContact = function(){

					var newEmail = {
						toUser: authorId,
						fromEmail: currentUserEmail,
						message: $scope.contactMessage,
						authorName: author,
						senderName: currentUser,
						ideaId: ideaId,
						ideaUrl: ideaUrl,
						ideaTitle: ideaTitle
					};

					EmailServ.save({}, newEmail, function(response){

						$scope.successSubmit = 'Successfully sent message!';
						$timeout(function(){
							dialog.close();
						}, 1000);

					}, function(response){

						$scope.validationErrors = [];
						if(response.data.code == 'validation_error'){
							for(var key in response.data.content){
								$scope.validationErrors.push(response.data.content[key]); 
							}
						}else{
							$scope.validationErrors = [response.data.content];
						}

					});

				};

			}
		]);

});