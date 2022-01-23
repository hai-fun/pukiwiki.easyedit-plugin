CKEDITOR.plugins.add( 'note', {
    icons: 'note',
    init: function( editor ) {
        editor.addCommand( 'insertNote', new CKEDITOR.dialogCommand( 'noteDialog' ) );
        editor.ui.addButton( 'Note', {
            label: 'Note',
            command: 'insertNote',
            toolbar: "insert"
        });
    }
});
CKEDITOR.dialog.add( 'noteDialog', function( editor ) {
    return {
        title: '注釈',
        minWidth: 400,
        minHeight: 200,
        contents: [
            {
                id: 'note_input',
                label: '注釈',
                elements: [
                    {
                        type: 'text',
                        id: 'note_id',
                        label: '注釈'
                    }                
                ]
            }
        ],
        onOk: function() {
            var dialog = this;
            editor.insertHtml('<span class="note"><img alt="Note" src="../'+url_path+'note/icons/note2.png" />'+dialog.getValueOf( 'note_input', 'note_id' )+'</span>');
        }
    };
});