
/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

/**
 * @fileOverview Definition for Meexo Gallery plugin dialog.
 *
 */

'use strict';

CKEDITOR.dialog.add('meexogallery', function(editor) {
	var general_tab_label = editor.lang.common.generalTab,
		validNameRegex = /^[^\[\]\<\>]+$/;
	
	var config = editor.config.meexogallery;
	
	return {
		title: config.title,
		minWidth: 300,
		minHeight: 80,
		contents: [
			{
				id: 'meexo-gallery-dialog',
				label: general_tab_label,
				title: general_tab_label,
				elements: [
					{
						id: 'id_gallery',
						type: 'select',
						style: 'width: 100%;',
						label: config.select_title,
						'default': config.loading,
						required: true,
						items: [ [ config.loading ] ],
						setup: function(widget) {
							this.setValue(widget.data.id_gallery);
							
							var self = this;
							
							$.get(config.list_api_url, function(data) {
								var select = $('#' + self.domId + ' select');
								select.get(0).options.length = 0;
								for (var i=0; i<data.length; i++)
								{
									select.get(0).options[select.get(0).options.length] = 
										new Option(data[i].title, data[i].id_gallery);
								}
								
								if (widget.data.id_gallery != '')
								{
									self.setValue(widget.data.id_gallery);
								}
							}, 'json');
						},
						commit: function(widget) {
							widget.setData('id_gallery', this.getValue());
							
							var select = $('#' + this.domId + ' select');
							widget.setData('gallery_title', select.get(0).options[select.get(0).selectedIndex].label);
						},
					}
				]
			}
		]
	};
});
