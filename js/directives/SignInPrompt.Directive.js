define(['angular'], function(angular){

	'use strict';

	angular.module('Directives')
		.directive('signInPromptDir', [
			'$timeout',
			function($timeout){
				return {
					link: function(scope, element, attributes){

						var trigger;

						attributes.$observe('signInPromptDir', function(value){
							value = (value == 'true');
							trigger = value;
							if(!value){
								element.tooltip({
									title: attributes.signInPromptMessage,
									trigger: 'manual'
								});
							}else{
								element.tooltip('destroy');
							}
						});

						element.bind('click', function(){

							if(!trigger){
								element.tooltip('show');
								$timeout(function(){
									element.tooltip('hide');
								}, 1000);
								return false;
							}

						});

					}
				};
			}
		]);

});