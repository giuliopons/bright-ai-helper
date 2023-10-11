( function( wp ) {

    var askHelpToAiSummarize = function( propsx ) {
        return wp.element.createElement(
            wp.editor.RichTextToolbarButton, {
                icon: 'sos atoi',
                title: 'AI Summarize',
                onClick: function() {
                    
                    if(typeof(propsx)=='object') {

                        const post_id = wp.data.select("core/editor").getCurrentPostId();
                        var startIndex = wp.data.select('core/block-editor').getSelectionStart().offset;
                        var endIndex = wp.data.select('core/block-editor').getSelectionEnd().offset;
                        var html = wp.data.select('core/block-editor').getSelectedBlock().attributes.content;
                        var html = stripHtml(html);
                        var blockUid = wp.data.select('core/block-editor').getSelectedBlock().clientId;


                        if (Math.abs(startIndex - endIndex) > 10 ) {

                          jQuery('body').append("<span class='waitingAI'></span>");

                            s= html.substring(startIndex,endIndex);

                            var formData = new FormData();

                            formData.append( 'action', 'summarize' );
                            formData.append( 'prompt', s );
                            formData.append( 'idpost', post_id );

                            const response = fetch( ajaxurl, {
                                method: 'POST',
                                body: formData,
                                headers: {
                                    'Accept': 'application/json',
                                }
                            }).then( res => res.json() )
                            .then( data => {
                            
                                msg = "Error";

                                if(typeof(data.choices) == "object") {
                                  msg = data.choices[0].message.content;
                                }

                                var index = wp.data.select('core/editor').getBlocks().map(function(block) {     
                                    return block.clientId == blockUid; }).indexOf(true) + 1;
                                const newBlock = wp.blocks.createBlock( "core/paragraph", {
                                    content: msg,
                                });
                                wp.data.dispatch("core/editor").insertBlocks(newBlock, index);
                                  

                                if (window.getSelection) {
                                    if (window.getSelection().empty) {  // Chrome
                                      window.getSelection().empty();
                                    } else if (window.getSelection().removeAllRanges) {  // Firefox
                                      window.getSelection().removeAllRanges();
                                    }
                                  } else if (document.selection) {  // IE?
                                    document.selection.empty();
                                  }
                                  jQuery('body .waitingAI').remove();
                            } )
                            .catch( err => console.log( err ) );
                        } else {

                          wp.data.dispatch('core/notices').createNotice( 'error', 
                          'Your prompt is too short, try to be more verbose',{
                            isDismissible: true,
                            type: 'snackbar'
                        })

                        }
                    }
                },
                isActive: propsx.isActive,
            }
        );
    }

    wp.richText.registerFormatType(
            'bright-ai-helper-summarize/output', {
            title: 'Summmarize',
            tagName: 'span',    // not inserted
            className: 'summarize', // not used
            edit: askHelpToAiSummarize,
        }
    );


} )( window.wp );


