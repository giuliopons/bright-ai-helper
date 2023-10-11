function stripHtml(html)
{
let tmp = document.createElement("DIV");
tmp.innerHTML = html;
return tmp.textContent || tmp.innerText || "";
}


var flagSavedHappend = false;


( function( wp ) {



  

  wp.data.subscribe(function () {
    // Check if the post is being saved
    const isSaving = wp.data.select('core/editor').isSavingPost();
  
    if (isSaving) {
      flagSavedHappend=false;
      console.log('Post is being saved...');
    } else {
      if(flagSavedHappend==false) {
      console.log('saved');
      flagSavedHappend = true;
        wp.data.dispatch('core/notices').createNotice( 'error', 
        'Yahoooooo!',{
          isDismissible: true,
          type: 'snackbar'
      });      
      }
    
    }
  });




    var askHelpToAi = function( propsx ) {
        return wp.element.createElement(
            wp.editor.RichTextToolbarButton, {
                icon: 'sos atoi',
                title: 'AI Ask for help',
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

                            formData.append( 'action', 'getaitext' );
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
                            
                                function replaceBetween(ss,start, end, what) {
                                    return ss.substring(0, start) + what + ss.substring(end);
                                };

                                msg = "Error";

                                if(typeof(data.choices) == "object") {
                                  msg = data.choices[0].message.content;
                                }

                                html = replaceBetween(html, startIndex, endIndex, msg);

                                var value = wp.richText.create({
                                           html 
                                         });
                         
                                wp.data.dispatch( 'core/block-editor' ).updateBlock( blockUid, {
                                        attributes: {
                                          content: wp.richText.toHTMLString({value } )
                                        }
                                } );
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
            'bright-ai-helper/output', {
            title: 'Ask AI for help',
            tagName: 'span',    // not inserted
            className: 'ask-to-ai', // not used
            edit: askHelpToAi,
        }
    );


} )( window.wp );


