/**
 * @license Copyright (c) 2003-2019, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

CKEDITOR.editorConfig = function( config ) {
  // console.log('CKEDITOR.editorConfig');

  // 不清除空的 i 标签和 span 标签
  CKEDITOR.dtd.$removeEmpty.i = 0;
  CKEDITOR.dtd.$removeEmpty.span = 0;
  CKEDITOR.dtd.$removeEmpty.a = 0;

  CKEDITOR.dtd['a']['div'] = 1;
  CKEDITOR.dtd['a']['p'] = 1;
  CKEDITOR.dtd['a']['ul'] = 1;
  CKEDITOR.dtd['a']['ol'] = 1;

  config.fillEmptyBlocks = false;
  config.allowedContent = true;
  config.image_previewText = ' ';
  // config.filebrowserImageBrowseUrl = 'media.select';
  config.coreStyles_bold = {
      element: 'b',
      overrides: 'strong',
  };

  config.toolbarGroups = [
      { name: 'document', groups: [ 'mode', 'document', 'doctools' ] },
      { name: 'clipboard', groups: [ 'clipboard', 'undo' ] },
      { name: 'styles', groups: [ 'styles' ] },
      { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
      { name: 'links', groups: [ 'links' ] },
      { name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi', 'paragraph' ] },
      { name: 'insert', groups: [ 'insert' ] },
      { name: 'forms', groups: [ 'forms' ] },
      { name: 'tools', groups: [ 'tools' ] },
      // [ 'name': 'others', 'groups': [ 'others' ] ],
      // [ 'name': 'editing', 'groups': [ 'find', 'selection', 'spellchecker', 'editing' ] ],
      // [ 'name': 'colors', 'groups': [ 'colors' ] ],
      // [ 'name': 'about', 'groups': [ 'about' ] ],
  ];

  config.removeButtons = 'Underline,Styles,Strike,Italic,Indent,Outdent,Blockquote,About,SpecialChar,HorizontalRule,Scayt,Cut,Copy,Paste,PasteText,PasteFromWord';

};
