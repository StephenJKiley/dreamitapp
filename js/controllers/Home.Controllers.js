define(['angular', 'lodash'], function(angular, _){

	'use strict';

	angular.module('Controllers')
		.controller('HomeCtrl', [
			'$scope',
			'$rootScope',
			'$location',
			'$filter',
			'$dialog',
			'$window',
			'$document',
			'AppIdeasServ',
			'UtilitiesServ',
			'IdeasServ',
			'SearchServ',
			'CachServ',
			function($scope, $rootScope, $location, $filter, $dialog, $window, $document, AppIdeasServ, UtilitiesServ, IdeasServ, SearchServ, CachServ){

				/**
				 * The default limit of app ideas to load on each scroll iteration
				 * and initial load. This may have to be changed in the future
				 * depending on browser screen width so to accomodate mobile
				 * screens and their connections
				 * @type {integer}
				 */
				var defaultLimit = 20;

				/**
				 * This counter offsets the items to be queried on each iteration of getIdeas
				 * If you reload based on new search criteria or tags, this needs to be reset to 0.
				 * @type {Integer}
				 */
				var counterOffset = 0;

				/**
				 * Upon instantiation or reinstantiation of the home controller, we need to clear any ideas in the AppIdeas
				 * service, since it is a singleton. This will make sure ideas aren't doubled up.
				 */
				AppIdeasServ.clearAppIdeas();

				/**
				 * This is assigned a reference to an array of app ideas to be repeated across the wall.
				 * Changes will to the array inside the service will be propagated across the application.
				 * @type {Array}
				 */
				$scope.appIdeas = AppIdeasServ.getAppIdeas();

				/**
				 * Setup comment cache, this will be passed into the Disqus Directive
				 * @type {Object}
				 */
				$scope.commentCache = CachServ.commentCache;

				/**
				 * This is to throttle the retrieving of idea items due to infinite scroll pagination.
				 * When set to true, the infinite scroll will stop activating the getIdeas.
				 * It will only be reset to false, once a response comes back from the resource query.
				 * @type {Boolean}
				 */
				$scope.ideasServiceBusy = false;

				/**
				 * This grabs app idea items from the service. It accepts a limit
				 * and tags parameter to filter the results. This will be passed
				 * to ng-repeat. It will maintain the counter offset.
				 * @param  {Integer} limit
				 * @param  {String} tags
				 * @return {Array}
				 */
				$scope.getIdeas = function(limit, tags, author, popular){

					$scope.ideasServiceBusy = true;

					var queryParameters = {
						limit: limit,
						offset: counterOffset
					};

					if(tags){
						queryParameters.tags = tags;
					}

					if(author){
						queryParameters.author = author;
					}

					if(popular){
						queryParameters.popular = popular;
					}

					IdeasServ.get(
						queryParameters,
						function(response){

							//increase the counterOffset
							counterOffset = counterOffset + limit;
							//concat doesn't preserve the reference, this push.apply() will
							$scope.appIdeas.push.apply($scope.appIdeas, response.content);
							$scope.ideasServiceBusy = false;

						},
						function(response){

							$scope.ideasServiceBusy = false;

						}
					);

				};

				/**
				 * When click on any tag links. This will set them as query parameters
				 * on the url, and then reload the items based on these tag parameters.
				 * This function should be called when any query parameters are detected.
				 * This only allows on tag to be used.
				 * @param  {String} tag Name of the tag
				 * @return {Void}
				 */
				$scope.tagAction = function(tag){

					SearchServ.searchTag(tag);
				
				};

				/////////////////////////////
				//  SEARCH EVENT HANDLING  //
				/////////////////////////////

				/**
				 * Gets a query parameter value according to the passed in type.
				 * It will be used by tags, author and popular ideas.
				 * @param  {String} type Query Parameter Key
				 * @return {String}
				 */
				var getSearchParam = function(type){

					return $location.search()[type];

				};

				/**
				 * This gets the limit query parameter and validates it.
				 * It then either sets the new limit, or defaults to the default limit.
				 * @return {Integer}
				 */
				var parseLimit = function(limit){

					if(!UtilitiesServ.empty(limit) && UtilitiesServ.isInteger(limit)){
						//limit is absolute valued and parsed as an integer
						limit = Math.abs(_.parseInt(limit));
					}else{
						limit = defaultLimit;
					}
					return limit;

				};

				$scope.$watch(function(){

						return $location.search();
					
					}, function(queryObject){

						//this sets up the initial parameters, but also reruns on each change of the $location.search()
						$scope.limit = parseLimit(queryObject.limit);
						$scope.tags = queryObject.tags;
						$scope.author = queryObject.author;
						$scope.popular = queryObject.popular;

						//only reload if there are tags or author or popular, but not limit
						//limit gets activated on the next scroll load
						if(
							$scope.tags 
							|| $scope.tags == '' 
							|| $scope.author 
							|| $scope.author == ''
							|| $scope.popular 
							|| $scope.popular == ''
						){
							//reset masonry, appIdeas and offset
							$scope.appIdeas = [];
							counterOffset = 0;
							$scope.getIdeas($scope.limit, $scope.tags, $scope.author, $scope.popular);
						}

					}, 
					true
				);

				//when this event is captured, we're forcing a total reload of the wall on default parameters
				$scope.$on('reloadWall', function(){

					$scope.limit = parseLimit();
					$scope.tags = undefined;
					$scope.author = undefined;
					$scope.popular = undefined;
					$scope.appIdeas = [];
					counterOffset = 0;
					$scope.getIdeas($scope.limit);
					
				});

				////////////////////////
				//  OVERLAY HANDLING  //
				////////////////////////

				//we want to cancel the state change to ideas, and instead launch an overlay
				//however we must keep the URL to the idea
				$scope.$on('$stateChangeStart', function(event, toState, toParams, fromState, fromParams){

					//smaller screen sizes should directly transition
					if(UtilitiesServ.checkMinimumOverlayWidth()){
						return;
					}

					//state change is prevented from home to ideas
					//but the URL is preserved
					if(fromState.name === 'home' && toState.name === 'idea'){
						event.preventDefault();
					}

				});

				/**
				 * Opens the overlay. This modifies the dialog options just as it is
				 * opening to inject the ideaId and locationParams. This also registers
				 * a closing callback when the overlay closes.
				 * @param  {Number} ideaId Id of the Idea
				 * @return {Void}
				 */
				$scope.openIdeaOverlay = function(ideaId){

					//smaller screen sizes should directly transition
					if(UtilitiesServ.checkMinimumOverlayWidth()){
						return;
					}

					//setting up the overlay options
					var dialog = $dialog.dialog({
						backdrop: false,
						keyboard: true,
						dialogClass: 'modal overlay_backdrop',
						templateUrl: 'idea_overlay.html',
						controller: 'IdeaOverlayCtrl'
					});

					//ideaId is to be injected to the overlay controller to get the correct idea
					//locationParamsAndHash is the current URL state before the overlay launches
					//it will be used when the overlay closes, and we want to return to original URL state
					dialog.options.resolve = {
						ideaId: function(){ return ideaId },
						locationParamsAndHash: function(){ 
							return {
								path: $location.path(),
								search: $location.search(),
								hash: $location.hash()
							};
						}
					};
					
					//closing callback will receive the previous locationParams through the overlay
					dialog.open().then(function(locationParamsAndHash){

						$rootScope.viewingOverlay = false;
						$location.path(locationParamsAndHash.path);
						$location.search(locationParamsAndHash.search);
						$location.hash(locationParamsAndHash.hash);

					});

				};

			}
		])
		.controller('IdeaOverlayCtrl', [
			'$scope',
			'$rootScope',
			'dialog',
			'ideaId',
			'locationParamsAndHash',
			'IdeasServ',
			'LikeServ',
			function($scope, $rootScope, dialog, ideaId, locationParamsAndHash, IdeasServ, LikeServ){

				$rootScope.viewingOverlay = true;

				$scope.closeOverlay = function(){
					//this can only pass in a single param
					dialog.close(locationParamsAndHash);
				};

				//and load in the appropriate data
				$scope.idea = {};
				IdeasServ.get(
					{
						id: ideaId
					},
					function(response){

						$scope.idea = response.content;

					},
					function(response){

						$scope.idea.errorMessage = response.data.content;

					}
				);

				$scope.likeAction = function(ideaId){

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

							});

						}
					);

				};

			}
		]);

});