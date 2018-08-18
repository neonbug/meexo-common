'use strict';

(function() {
	CKEDITOR.plugins.add('meexogallery', {
		requires: ['widget', 'dialog', 'richcombo'],
		icons: 'meexogallery', 
		
		onLoad: function() {
			CKEDITOR.addCss('.meexogallery-embed {padding: 8px 16px; background-color: #F2711C; border-top: 1px solid #ffffff; color: #ffffff;}');
		},

		init: function(editor) {
			var config = editor.config.meexogallery;
			
			CKEDITOR.dialog.add('meexogallery', this.path + 'dialogs/meexogallery.js');

			editor.widgets.add('meexogallery', {
				dialog: 'meexogallery',
				
				template:        '<div class="meexogallery-embed" data-id-gallery=""></div>',
				allowedContent:  'div[!data-id-gallery](!meexogallery-embed)',
				requiredContent: 'div[data-id-gallery](meexogallery-embed)',
				
				button: config.button_title,
				
				upcast: function(el) {
					return el.name == 'div' && el.hasClass('meexogallery-embed');
				},

				init: function() {
					this.setData('id_gallery',    this.element.data('id-gallery'));
					this.setData('gallery_title', this.element.getText().slice( 2, -2 ));
				},

				data: function(data) {
					this.element.setHtml('[[' + data.data.gallery_title + ']]');
					this.element.setAttribute('data-id-gallery', data.data.id_gallery);
				}
			});
		},
	});
})();
