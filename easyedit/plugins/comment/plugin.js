CKEDITOR.plugins.add( 'comment', {
    icons: 'comment',
    init: function( editor ) {
        editor.addCommand( 'insertComment', new CKEDITOR.dialogCommand( 'commentDialog' ) );
        editor.ui.addButton( 'Comment', {
            label: 'Comment',
            command: 'insertComment',
            toolbar: "insert"
        });
    }
});
CKEDITOR.dialog.add( 'commentDialog', function( editor ) {
    return {
        title: 'コメント',
        minWidth: 400,
        minHeight: 200,
        contents: [
            {
                id: 'comment_input',
                label: 'コメント',
                elements: [
                    {
                        type: 'text',
                        id: 'comment_id',
                        label: 'コメント'
                    }                
                ]
            }
        ],
        onOk: function() {
            var dialog = this;
            editor.insertHtml('<span style="color:#355e3b" class="comment"><img src="../'+url_path+'comment/icons/comment.png" />'+dialog.getValueOf( 'comment_input', 'comment_id' )+'</span>');
        }
    };
});