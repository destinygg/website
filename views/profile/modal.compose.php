<?php
namespace Destiny;
?>

<div class="modal fade" id="inbox-modal-delete" tabindex="-1" role="dialog" aria-labelledby="inbox-modal-delete-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="inbox-modal-delete-title">Delete confirmation</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div>Are you sure you?</div>
                <div>This cannot be undone.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger" id="deleteConversation">Delete</button>
            </div>
        </div>
    </div>
</div>

<div class="modal message-composition" id="compose" tabindex="-1" role="dialog" aria-labelledby="composeLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title mr-auto" id="composeLabel">New message</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span><span class="sr-only">Close</span>
                </button>
            </div>
            <div id="compose-form">
                <div class="modal-recipients">
                    <div class="modal-user-groups clearfix">
                        <input tabindex="1" id="compose-recipients" type="text" placeholder="Enter a recipient ..." autocomplete="false" autocorrect="off" spellcheck="false" />
                    </div>
                    <div class="recipient-container"></div>
                </div>
                <div class="modal-body">
                    <textarea id="compose-message" tabindex="3" autocomplete="false" autocorrect="off"></textarea>
                </div>
                <div class="modal-footer">
                    <button accesskey="s" id="modal-send-btn" tabindex="5" type="button" class="btn btn-primary" data-loading-text="Sending...">Send</button>
                    <button id="modal-close-btn" tabindex="3" type="button" class="btn btn-default">Cancel</button>
                    <span class="modal-message">To send press <code>ctrl + enter</code> or <code>alt + s</code></span>
                </div>
            </div>
        </div>
    </div>
</div>