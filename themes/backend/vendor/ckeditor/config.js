/**
 * @license Copyright (c) 2003-2020, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

CKEDITOR.editorConfig = function( config ) {
  // 不清除空的 i 标签和 span 标签
  CKEDITOR.dtd.$removeEmpty.i = 0;
  CKEDITOR.dtd.$removeEmpty.span = 0;
  CKEDITOR.dtd.$removeEmpty.a = 0;

  CKEDITOR.dtd.a.div = 1;
  CKEDITOR.dtd.a.p = 1;
  CKEDITOR.dtd.a.ul = 1;
  CKEDITOR.dtd.a.ol = 1;

  config.fillEmptyBlocks = false;
  config.allowedContent = true;
  config.image_previewText = ' ';
  config.coreStyles_bold = {
    element: 'b',
    overrides: 'strong',
  };
  config.entities = false;

  config.toolbarGroups = [
    { name: 'styles', groups: [ 'styles' ] },
    { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
    { name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi', 'paragraph' ] },
    { name: 'links', groups: [ 'links' ] },
    { name: 'insert', groups: [ 'insert' ] },
    { name: 'tools', groups: [ 'tools' ] },
    { name: 'document', groups: [ 'mode', 'document', 'doctools' ] }
  ];

  config.removeButtons = 'Underline,Strike,Outdent,Indent,Styles,Blockquote,HorizontalRule,SpecialChar';
};
