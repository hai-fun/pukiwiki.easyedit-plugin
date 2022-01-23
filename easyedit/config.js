/**
 * @license Copyright (c) 2003-2020, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

var url_path = "."+(document.currentScript ? document.currentScript.src : document.getElementsByTagName('script')[document.getElementsByTagName('script').length - 1].src).replace(new RegExp('^' + location.origin), '').replace(/[^\/]+$/, '')+"plugins/";
CKEDITOR.editorConfig = function( config ) 
{
    config.toolbar = [
		{ name: 'clipboard', items: ["Copy","Paste","Cut","Undo","Redo"] },
		{ name: 'document', items: ["Source","Scayt",'Maximize'] },
		{ name: 'about', items: ["About"] },
		'/',
		{ name: 'text', items: ["Bold", "Italic", "Strike","Underline","TextColor","BGColor","Subscript","Superscript","FontSize"] },
		{ name: 'other', items: ["Format","Link","Unlink", "Anchor", "Image", "Table", "HorizontalRule","SpecialChar",'NumberedList', 'BulletedList', 'Outdent', 'Indent', 'Blockquote', 'Comment', 'Note', 'PukiPlugin'] }
	];
    config.allowedContent = true;
    config.height = 250;
    config.extraPlugins = 'colorbutton,panelbutton,floatpanel,panel,button,richcombo,font,comment,note,pukiplugin';
    config.removePlugins = 'elementspath';
	config.format_tags = 'p;h2;h3;h4;pre';
	config.removeDialogTabs = 'image:advanced;link:advanced';
    config.startupShowBorders = false;
};