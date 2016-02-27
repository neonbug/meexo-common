/**
 * @license Copyright (c) 2003-2015, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
	// 
	config.filebrowserBrowseUrl = '../../../vendor/common/admin_assets/js/ckeditor/plugins/kcfinder/browse.php?opener=ckeditor&type=files';
	config.filebrowserImageBrowseUrl = '../../../vendor/common/admin_assets/js/ckeditor/plugins/kcfinder/browse.php?opener=ckeditor&type=images';
	config.filebrowserFlashBrowseUrl = '../../../vendor/common/admin_assets/js/ckeditor/plugins/kcfinder/browse.php?opener=ckeditor&type=flash';
	config.filebrowserUploadUrl = '../../../vendor/common/admin_assets/js/ckeditor/plugins/kcfinder/upload.php?opener=ckeditor&type=files';
	config.filebrowserImageUploadUrl = '../../../vendor/common/admin_assets/js/ckeditor/plugins/kcfinder/upload.php?opener=ckeditor&type=images';
	config.filebrowserFlashUploadUrl = '../../../vendor/common/admin_assets/js/ckeditor/plugins/kcfinder/upload.php?opener=ckeditor&type=flash';
};
