<script type="text/ng-template" id="idea.html">
	<article class="idea_container container" pull-down-to-window-dir ng-show="!idea.errorMessage">
		<header class="idea_header">
			<h1 class="idea_heading"><a ng-href="ideas/{{idea.id}}/{{idea.titleUrl}}">{{idea.title}}</a></h1>
		</header>
		<div class="idea_content">
			<div class="idea_inner_content">
				<img class="idea_main_image" ng-src="{{idea.image}}" image-centering-dir image-centering-limit="40px" />
				<div class="idea_description" ng-bind-html="idea.descriptionParsed"></div>
			</div>
			<aside class="idea_meta gradient">
				<div class="idea_author">
					<img class="idea_author_image" ng-src="{{idea.authorAvatar + '?s=184&d=mm'}}" />
					<div class="idea_aside_text">
						<span class="idea_author_name">
							<a ng-href="{{'users/' + idea.authorId + '/' + idea.authorUrl}}">{{idea.author}}</a>
						</span>
						<ul class="idea_author_additonal_information">
							<li>{{idea.authorType}}</li>
							<li>Links:</li>
							<li ng-repeat="link in idea.authorProfileLinks">
								<a ng-href="{{link}}">{{link}}</a>
							</li>
						</ul>
					</div>
				</div>
				<hr />
				<div class="idea_idea_data idea_aside_text">
					<ul>
						<li>Submitted: {{idea.date}}</li>
						<li>Feedback: 42</li>
						<li>Likes: {{idea.likes}}</li>
						<li>Tags: 
							<ul class="idea_tags_list">
								<li ng-repeat="tag in idea.tags">
									<a ng-href="?tags={{tag}}" ng-click="tagAction(tag)">{{tag}}</a>
								</li>
							</ul>
						</li>
					</ul>
				</div>
				<hr />
				<div class="idea_actions idea_aside_text">
					<ul>
						<li><a ng-click="likeAction(idea.id)"><span class="fui-heart"></span>Like</a></li>
						<li><a ng-click="contactAuthor(idea.id)"><span class="fui-mail"></span>Contact</a></li>
						<li>
							<a 
								ng-href="http://www.addthis.com/bookmark.php?v=300&pubid={{dreamItAppConfig.apiKeys.addThis}}" 
								add-this-dir 
								add-this-config="{
									ui_click: true,
									services_exclude: 'print'
								}" 
								add-this-url = "{{baseUrl + 'ideas/' + idea.id + '/' + idea.titleUrl}}" 
								add-this-title = "{{idea.title}}" 
								add-this-description = "{{idea.descriptionShort}}" 
							>
								<span class="fui-plus"></span>Share
							</a>
						</li>
						<li><a anchor-scroll-dir="feedback"><span class="fui-chat"></span>Give Feedback</a></li>
					</ul>
				</div>
			</aside>
		</div>
		<section id="feedback" class="idea_comments">
			<h2>Feedback</h2>
			<div 
				disqus-thread-dir 
				disqus-shortname="{{dreamItAppConfig.apiKeys.disqusShortname}}" 
				disqus-identifier="{{idea.id}}" 
				disqus-title="{{idea.title}}" 
				disqus-url="{{baseUrl + 'ideas/' + idea.id + '/' + idea.titleUrl}}" 
				disqus-developer="true"
			></div>
		</section>
	</article>
	<article class="idea_container container" pull-down-to-window-dir ng-show="idea.errorMessage">
		<header class="page-header">
			<div class="alert alert-error alert-block">
				<h1>404 Error!</h1>
				{{idea.errorMessage}}
			</div>
		</header>
	</article>
</script>