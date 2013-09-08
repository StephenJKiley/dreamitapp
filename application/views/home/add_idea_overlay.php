<script type="text/ng-template" id="add_idea_overlay.html">
	<div class="add_idea_container" overlay-dir>
		<button class="idea_close" ng-click="closeOverlay()"><span class="fui-cross"></span></button>
		<form class="form-horizontal" ng-submit="submitIdea()" name="add_idea_form">
			<fieldset>
				<legend>Add a New Idea</legend>
				<ul class="validation_errors" ng-show="validationErrors">
					<li ng-repeat="error in validationErrors">{{error}}</li>
				</ul>
				<div 
					class="control-group" 
					ng-class="{error: 
						add_idea_form.add_idea_title.$invalid 
						&& add_idea_form.add_idea_title.$dirty}"
				>
					<label class="control-label" for="add_idea_title">Title:</label>
					<div class="controls">
						<input 
							type="text" 
							id="add_idea_title" 
							class="input-xxlarge" 
							name="add_idea_title" 
							ng-model="addIdeaTitle" 
							ng-minlength="2" 
							ng-maxlength="30" 
							required 
						/>
						<span class="help-inline" ng-show="add_idea_form.add_idea_title.$error.required">Required</span>
						<span class="help-inline" ng-show="add_idea_form.add_idea_title.$error.minlength">Title is too short.</span>
						<span class="help-inline" ng-show="add_idea_form.add_idea_title.$error.maxlength">Title is too long.</span>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label">Add Image:</label>
					<div class="controls">
						<span class="input-xxlarge uneditable-input">{{addIdeaImage}}</span>
					</div>
					<div class="controls">
						<button 
							class="add_idea_image_button btn btn-info" 
							type="button" 
							file-picker-store-dir 
							file-picker-api-key="{{dreamItAppConfig.apiKeys.filePickerApiKey}}"
							file-picker-options="{
								mimetype: 'image/*',
								maxSize: 500*1024
							}" 
							file-store-options="{
								location: 'S3'
							}"
							file-picker-success="uploadImage(InkBlobs)"
						>
							Upload
						</button>
					</div>
				</div>
				<div 
					class="control-group" 
					ng-class="{error: 
						add_idea_form.add_idea_short_description.$invalid 
						&& add_idea_form.add_idea_short_description.$dirty}"
				>
					<label class="control-label" for="add_idea_short_description">Short Description:</label>
					<div class="controls">
						<textarea 
							id="add_idea_short_description" 
							class="input-block-level" 
							name="add_idea_short_description" 
							ng-model="addIdeaDescriptionShort" 
							ng-minlength="10" 
							ng-maxlength="280" 
							required
						></textarea>
						<span class="help-block" ng-show="add_idea_form.add_idea_short_description.$error.required">Required</span>
						<span class="help-block" ng-show="add_idea_form.add_idea_short_description.$error.minlength">Short description is too short.</span>
						<span class="help-block" ng-show="add_idea_form.add_idea_short_description.$error.maxlength">Short description is too long.</span>
						<span class="help-block">This will be shown on the Home page wall. Write up to 280 characters.</span>
					</div>
				</div>
				<div 
					class="control-group" 
					ng-class="{error: 
						add_idea_form.add_idea_description.$invalid 
						&& add_idea_form.add_idea_description.$dirty}"
				>
					<label class="control-label" for="add_idea_description">Description:</label>
					<div class="controls">
						<textarea 
							id="add_idea_description" 
							class="input-block-level" 
							name="add_idea_description" 
							rows="10" 
							ng-model="addIdeaDescription" 
							ng-minlength="10" 
							ng-maxlength="13500" 
							required
						></textarea>
						<span class="help-block" ng-show="add_idea_form.add_idea_description.$error.required">Required</span>
						<span class="help-block" ng-show="add_idea_form.add_idea_description.$error.minlength">Description is too short.</span>
						<span class="help-block" ng-show="add_idea_form.add_idea_description.$error.maxlength">Description is too long.</span>
						<span class="help-block">You are welcome to use <a href="http://net.tutsplus.com/tutorials/tools-and-tips/markdown-the-ins-and-outs/" target="_blank">Markdown</a>. Write up to 2000 words.</span>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="add_idea_tags">Tags:</label>
					<div class="controls">
						<input 
							type="text" 
							id="add_idea_tags" 
							class="input-xxlarge" 
							name="add_idea_tags" 
							ui-select2="addIdeaTagsOptions" 
							ng-model="addIdeaTags" 
						/>
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
						/>
						Public
					</label>
					<label class="radio inline">
						<input 
							type="radio" 
							name="add_idea_privacy" 
							ng-model="addIdeaPrivacy" 
							value="developers" 
						/>
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