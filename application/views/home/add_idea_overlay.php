<script type="text/ng-template" id="add_idea_overlay.html">
	<div class="add_idea_container" overlay-dir>
		<button class="idea_close" ng-click="closeOverlay()"><span class="fui-cross"></span></button>
		<form class="form-horizontal" ng-submit="submitIdea()" name="add_idea_form">
			<fieldset>
				<legend>Add a New Idea</legend>
				<div class="control-group">
					<label class="control-label" for="add_idea_title">Title:</label>
					<div class="controls">
						<input 
							type="text" 
							id="add_idea_title" 
							name="add_idea_title" 
							ng-model="addIdeaTitle" 
							ng-minlength="1" 
							ng-maxlength="30" 
							required
						></input>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="add_idea_short_description">Short Description:</label>
					<div class="controls">
						<input 
							type="text" 
							id="add_idea_short_description" 
							name="add_idea_short_description" 
							ng-model="addIdeaDescriptionShort" 
							ng-minlength="10" 
							ng-maxlength="280" 
							required
						></input>
						<span class="help-block">This will be shown on the Home page wall. Write up to 280 characters.</span>
					</div>
				</div>
				<div class="control-group">
					<button class="add_idea_image_button btn btn-info" type="button">Add an Image</button>
				</div>
				<div class="control-group">
					<label class="control-label" for="add_idea_description">Description:</label>
					<div class="controls">
						<textarea 
							id="add_idea_description" 
							name="add_idea_description" 
							ng-model="addIdeaDescription" 
							ng-minlength="10" 
							ng-maxlength="13500" 
							required
						></textarea>
						<span class="help-block">You are welcome to use <a href="http://net.tutsplus.com/tutorials/tools-and-tips/markdown-the-ins-and-outs/" target="_blank">Markdown</a>. Write up to 2000 words.</span>
					</div>
				</div>
				<div class="control-group">
					<p>Viewing Privacy:</p>
					<label class="radio inline">
						<input 
							type="radio" 
							name="add_idea_privacy" 
							ng-model="addIdeaPrivacy" 
							value="public" 
							ng-checked="true"
						></input>
						Public
					</label>
					<label class="radio inline">
						<input 
							type="radio" 
							name="add_idea_privacy" 
							ng-model="addIdeaPrivacy" 
							value="developers"
						></input>
						Developers Only
					</label>
				</div>
				<div class="form-actions">
					<button type="submit" class="btn btn-primary">Add Idea</button>
					<button type="button" class="btn" ng-click="closeOverlay()">Cancel</button>
				</div>
			</fieldset>
		</form>
	</div>
</script>