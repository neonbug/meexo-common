var global      = require('./global');
var speakingurl = require('speakingurl');
var moment      = require('moment');
var pikaday     = require('pikaday');

module.exports = {};

var app_data = {};

var current_ajax_requests = {};

function initSlugs() {
	$('[data-type="slug"]').each(function(idx, item) {
		var generate_from = $('[data-name="' + item.dataset.slugGenerateFrom + '"]', $(item).closest('.tab'));
		generate_from = (generate_from.length == 0 ? null : $(generate_from.get(0)));
		if (generate_from.length == 0) return;
		
		$(item).change(function() {
			this.dataset.slugIsEmpty = (this.value.length == 0 ? 'true' : 'false');
			
			if (generate_from == null) return;
			
			if (this.dataset.slugIsEmpty == 'true')
			{
				updateSlug($(item), $(generate_from));
			}
			checkSlug($(item));
		});
		
		if (generate_from != null)
		{
			generate_from.keyup(function() {
				updateSlug($(item), generate_from);
				
				//TODO maybe delay calling checkSlug?
				checkSlug($(item));
			});
			updateSlug($(item), generate_from);
		}
	});
}

function updateSlug(slug_field, generate_from_field) {
	if (slug_field.get(0).dataset.slugIsEmpty == 'false') return;
	slug_field.val(speakingurl(generate_from_field.val()));
}

function checkSlug(slug_field) {
	var value = slug_field.val();
	var name = slug_field.attr('name');
	
	if (current_ajax_requests[name] != undefined)
	{
		current_ajax_requests[name].abort();
		current_ajax_requests[name] = undefined;
	}
	
	var field = $('.field[data-name="' + slug_field.attr('name') + '"]');
	var error_label = $('.error-label', field);
	
	if (value.length == 0)
	{
		error_label.html(app_data.trans.errors.slug_empty);
		markSlugField(slug_field, true);
	}
	else
	{
		var icon_div = $('.field[data-name="' + name + '"] .ui.icon.input');
		icon_div.addClass('loading');
		field.addClass('loading');
		
		var post_data = {
			value: value, 
			id_language: slug_field.data('id-language'), 
			id_item: app_data.config.id_item
		};
		
		current_ajax_requests[name] = $.post(app_data.config.check_slug_route, post_data, 
			function(data) {
			current_ajax_requests[name] = undefined;
			
			//TODO check for generic errors, like TokenMismatch (should catch it in Error handler of ajax response); do sth smart in that case .. like .. reload?
			
			error_label.html(app_data.trans.errors.slug_already_exists);
			markSlugField(slug_field, !data.valid);
		}, 'json');
	}
}

function markSlugField(slug_field, is_error) {
	var field = $('.field[data-name="' + slug_field.attr('name') + '"]');
	var icon_div = $('.field[data-name="' + slug_field.attr('name') + '"] .ui.icon.input');
	var icon = $('.field[data-name="' + slug_field.attr('name') + '"] .ui.icon.input .icon');
	
	icon_div.removeClass('loading');
	field.removeClass('loading');
	if (is_error)
	{
		field.addClass('error');
		icon.removeClass('checkmark').addClass('remove');
	}
	else
	{
		field.removeClass('error');
		icon.removeClass('remove').addClass('checkmark');
	}
}

var validation_selector = '.validation-required, .validation-int, .validation-image-required';
function initValidation() {
	$(validation_selector).change(function() {
		validateItem($(this));
	}).keyup(function() {
		validateItem($(this));
	});
}

function validateItems() {
	$(validation_selector).each(function(idx, item) {
		validateItem($(item));
	});
}

function validateItem(item) {
	var name        = item.attr('name');
	var field       = $('.field[data-name="' + name + '"]');
	var error_label = $('.error-label', field);
	
	var error = '';
	if (item.hasClass('validation-required'))
	{
		var value = item.val();
		if (value.length == 0)
		{
			error = app_data.trans.errors.validation_required;
		}
	}
	
	if (item.hasClass('validation-int') || item.hasClass('validation-float'))
	{
		var value = item.val();
		if (value.length > 0 && isNaN(value))
		{
			error = app_data.trans.errors.validation_number;
		}
	}
	
	if (item.hasClass('validation-image-required'))
	{
		if (item.val().length == 0)
		{
			var field_name = item.data('name');
			var image_remove_checkbox = $('.current-image-remove[data-name="' + field_name + '"]');
			if (image_remove_checkbox.length == 0) //no current image exists
			{
				error = app_data.trans.errors.validation_required;
			}
			else
			{
				if (image_remove_checkbox.get(0).checked) //current image is marked to be deleted
				{
					error = app_data.trans.errors.validation_required;
				}
			}
		}
	}
	
	if (error == '')
	{
		field.removeClass('error');
	}
	else
	{
		field.addClass('error');
		error_label.html(error);
	}
}

function initSaveButton() {
	$('form.add').submit(function(e) {
		//if we're still loading stuff, don't continue
		if ($('.field.loading').length > 0)
		{
			e.preventDefault();
			//TODO inform user why nothing is happening
			return;
		}
		
		validateItems();
		
		//if there are errors on the form, tell that to the user, and don't continue
		if ($('.field.error').length > 0)
		{
			e.preventDefault();
			
			$('.errors-modal').modal({
				blurring: true
			}).modal('show');
			
			return;
		}
		
		if ($('.preview-button').hasClass('loading'))
		{
			$('.preview-button').removeClass('loading');
		}
		else
		{
			$('.save-button').addClass('loading').attr('disabled', 'disabled');
		}
	});
	
	$('.preview-button').click(function() {
		$('.preview-button').addClass('loading');
	});
}

function initMessageClose() {
	$('.message .close').on('click', function() {
		$(this).parent().transition('fade down');
	});
}

function initRichEditors() {
	var is_mobile = ($(window).width() < 768);
	var remove_buttons = is_mobile ? 
		'Print,Preview,Save,Templates,NewPage,Find,Replace,SelectAll,Scayt,Form,Checkbox,Radio,TextField,Textarea,Select,Button,ImageButton,HiddenField,Language,BidiRtl,BidiLtr,About,Source,Cut,Copy,Paste,PasteText,PasteFromWord,Outdent,Indent,Blockquote,CreateDiv,Flash,SpecialChar,Smiley,PageBreak,Iframe,ShowBlocks'
		:
		'Print,Preview,Save,Templates,Find,Replace,SelectAll,Scayt,Form,Checkbox,Radio,TextField,Textarea,Select,Button,ImageButton,HiddenField,Language,BidiRtl,BidiLtr,About,Flash,NewPage';
	
	var file_browser_base_href = app_data.config.base_url + '/vendor/common/admin_assets/js/ckeditor/plugins/kcfinder/';
	
	$('textarea[data-type="rich_text"]').each(function(idx, el) {
		var config = {
			entities: false, 
			baseHref: app_data.config.base_url, 
			toolbarGroups: [
				{ name: 'document', groups: [ 'mode', 'document', 'doctools' ] },
				{ name: 'clipboard', groups: [ 'clipboard', 'undo' ] },
				{ name: 'editing', groups: [ 'find', 'selection', 'spellchecker', 'editing' ] },
				{ name: 'forms', groups: [ 'forms' ] },
				{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
				{ name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi', 'paragraph' ] },
				'/',
				{ name: 'links', groups: [ 'links' ] },
				{ name: 'insert', groups: [ 'insert' ] },
				{ name: 'styles', groups: [ 'styles' ] },
				{ name: 'colors', groups: [ 'colors' ] },
				{ name: 'tools', groups: [ 'tools' ] },
				{ name: 'others', groups: [ 'others' ] },
				{ name: 'about', groups: [ 'about' ] }
			], 
			removeButtons: remove_buttons, 
			filebrowserBrowseUrl: file_browser_base_href + 'browse.php?opener=ckeditor&type=files', 
			filebrowserImageBrowseUrl: file_browser_base_href + 'browse.php?opener=ckeditor&type=images', 
			filebrowserFlashBrowseUrl: file_browser_base_href + 'browse.php?opener=ckeditor&type=flash', 
			filebrowserUploadUrl: file_browser_base_href + 'upload.php?opener=ckeditor&type=files', 
			filebrowserImageUploadUrl: file_browser_base_href + 'upload.php?opener=ckeditor&type=images', 
			filebrowserFlashUploadUrl: file_browser_base_href + 'upload.php?opener=ckeditor&type=flash',
			height: el.dataset.height ?? 200,
		};
		
		if (el.dataset.extraPlugins !== undefined && el.dataset.extraPlugins != '')
		{
			var extra_plugins = JSON.parse(el.dataset.extraPlugins);
			var plugin_names = extra_plugins.map(function(plugin) { return plugin.plugin; });
			
			config.extraPlugins = plugin_names.join(',');
			
			for (var i=0; i<extra_plugins.length; i++)
			{
				if (extra_plugins[i].config !== undefined)
				{
					config[extra_plugins[i].plugin] = extra_plugins[i].config;
				}
			}
		}
		
		CKEDITOR.replace(el, config);
	});
}

function initCheckboxes() {
	$('.ui.checkbox').checkbox({
		onChange: function() {
			$('[name="' + this.dataset.name + '"]').val(this.checked ? '1' : '0');
		}
	});
}

function initTabs() {
	$('.menu .item').tab();
}

function initDatePicker() {
	moment.locale(window.navigator.language);
	
	$('[data-type="date"]').each(function(index, item) {
		var picker = new pikaday({
			field: item,
			firstDay: 1,
			format: app_data.config.formatter_date_pattern, 
			onSelect: function(date) {
				$('[name="' + item.dataset.dateRel + '"]').val(moment(date).format('YYYY-MM-DD')).trigger('change');
			}
		});
	});
}

function initMessages() {
	app_data.config.messages.forEach(function(message) {
		global.showToast('success', message);
	});
}

function initErrorMessages() {
	app_data.config.errors.forEach(function(message) {
		global.showToast('error', message);
	});
}

function initDropdowns() {
	$('.ui.dropdown').dropdown({
		placeholder: false
	});
}

function initMetaDescriptions() {
	var character_count_handler = function() {
		var character_count = 160 - this.value.length;
		var character_count_label = $('.character-count', $(this).closest('.field'));
		
		character_count_label.html(character_count);
		
		if (character_count >= 0)
		{
			character_count_label.removeClass('warning');
		}
		else
		{
			character_count_label.addClass('warning');
		}
	};
	
	var meta_description_input = $('.field-meta-description .field input');
	
	meta_description_input.on('change', character_count_handler);
	meta_description_input.on('keyup', character_count_handler);
	meta_description_input.change();
}

module.exports.init = function(trans, config) {
	app_data.trans = trans;
	app_data.config = config;
	
	$(document).ready(function() {
		initSlugs();
		initValidation();
		initSaveButton();
		initMessageClose();
		initRichEditors();
		initCheckboxes();
		initTabs();
		initDatePicker();
		initMessages();
		initErrorMessages();
		initDropdowns();
		initMetaDescriptions();
	});
};
