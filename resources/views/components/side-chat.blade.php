<!-- Sidebar -->
<!-- Sidebar -->
<div id="friendsSidebar" style="padding: 1rem">

    <style>
        #closeSidebarBtn {
            padding: 8px 12px;
            font-size: 24px;
        }
    </style>

    <!-- Close Button -->
    <button id="closeSidebarBtn"
        style="position: absolute; top: 1rem; right: 1rem; background: none; border: none; font-size: 20px; color: #666; cursor: pointer;"
        title="Close Sidebar">
        &times;
    </button>

    <h3 style="margin-bottom: 1rem;">Custom Chats</h3>

    <b style="display: block; margin-bottom: 0.5rem;">Recent Chats</b>

    <div>
        <input type="text" class="form-control mb-2 search-people-recent-chat" placeholder="Search People...">
    </div>

    <div class="chat-history">
        <div style="display: flex; justify-content: center; align-items: center;">
            <div class="spinner-border"></div>
        </div>
    </div>

    <hr style="margin: 1rem 0;">

    <b style="display: block; margin-bottom: 0.5rem;">New Chat</b>

    <div>
        <input type="text" class="form-control mb-2 search-people-new-chat" placeholder="Search People...">
    </div>

    @foreach (App\Models\User::whereNot('name', 'Group Chat')->whereNot('id', Auth::user()->id)->get() as $user)
    @php
        $hasPhoto = $user->profile_photo_path;
        $initials = '';
        if (!$hasPhoto) {
            $names = explode(' ', trim($user->name));
            $initials = strtoupper(substr($names[0], 0, 1) . substr(end($names), 0, 1));
        }
    @endphp

    <div class="friend" data-id="{{ $user->id }}" data-name="{{ $user->name }}"
        style="display: flex; align-items: center; gap: 10px; background: #fff; border: 1px solid #ddd; padding: 8px; margin-bottom: 8px; border-radius: 5px; cursor: pointer;">
        
        @if ($hasPhoto)
            <img src="{{ url('storage/' . $user->profile_photo_path) }}"
                alt="{{ $user->name }}"
                style="width: 36px; height: 36px; border-radius: 50%; object-fit: cover;">
        @else
            <div style="
                width: 36px;
                height: 36px;
                border-radius: 50%;
                background: linear-gradient(to bottom, #2196F3, #1976D2);
                color: white;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: bold;
                font-size: 14px;
                font-family: sans-serif;
            ">
                {{ $initials }}
            </div>
        @endif

        <span style="font-size: 14px;">{{ $user->name }}</span>
    </div>

    @endforeach

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="{{ url('assets/pollinator/pollinator.min.js') }}"></script>
    <script src="{{ url('assets/pollinator/polly.js') }}"></script>
    <script>
        $(document).ready(function () {

            $(".search-people-new-chat").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $(".friend").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });

            // Close sidebar on button click
            $('#closeSidebarBtn').on('click', function () {
                $('#friendsSidebar').hide(); // Or use fadeOut() if you prefer animation
                $('#overlay').hide(); // Optional: also hide overlay if you use one
            });

            const polling = new PollingManager({
                url: `/chat-history`, // API to fetch data
                delay: 5000, // Poll every 5 seconds
                failRetryCount: 3, // Retry on failure
                onSuccess: (chats) => {
                    $('.chat-history').html("");

                    chats.forEach(chat => {
                        const fontWeight = chat.seen ? 'normal' : 'bold';
                        const italicPreview = chat.latest_message ? `<i>"${chat.latest_message}"</i>` : '';

                        // change
                        // Get initials from name
                        function getInitials(name) {
                            const names = name.trim().split(' ');
                            const first = names[0]?.[0] || '';
                            const last = names.length > 1 ? names[names.length - 1]?.[0] || '' : '';
                            return (first + last).toUpperCase();
                        }

                        console.log(chat);

                        // Determine avatar HTML
                        const avatarHtml = chat.receiver_profile_picture
                            ? `<img src="/storage/${chat.receiver_profile_picture}"
                                    alt="${chat.receiver_name}"
                                    style="width: 36px; height: 36px; border-radius: 50%; object-fit: cover;">`
                            : `<div style="
                                    width: 36px;
                                    height: 36px;
                                    border-radius: 50%;
                                    background: linear-gradient(to bottom, #2196F3, #1976D2);
                                    color: white;
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                    font-weight: bold;
                                    font-size: 14px;
                                    font-family: sans-serif;
                                ">
                                    ${getInitials(chat.receiver_name)}
                                </div>`;

                        // Append the chat history entry
                        $('.chat-history').append(`
                            <div class="chat-history-user"
                                data-id="${chat.receiver_id}"
                                data-name="${chat.chat_name}"
                                style="
                                    display: flex;
                                    align-items: center;
                                    gap: 10px;
                                    background: #fff;
                                    border: 1px solid #ddd;
                                    padding: 8px;
                                    margin-bottom: 8px;
                                    border-radius: 5px;
                                    cursor: pointer;
                                    font-weight: ${fontWeight};
                                ">

                                ${avatarHtml}
                                <span style="font-size: 14px;">${(chat.receiver_name === "Group Chat") ? chat.chat_name : chat.receiver_name}</span> - ${italicPreview}
                            </div>
                        `);

                        // end change
                    });

                    $(".search-people-recent-chat").on("keyup", function() {
                        var value = $(this).val().toLowerCase();
                        $(".chat-history-user").filter(function() {
                        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                        });
                    });
                },
                onError: (error) => {
                    console.error("Error fetching data:", error);
                    // Your custom error handling logic
                }
            });

            // Start polling
            polling.start();

            // $.get('/chat-history', function (chats) {

            // });

            // ✅ Fix: Use event delegation
            $(document).on('click', '.chat-history-user', function () {
                const name = $(this).data('name');
                const friend_id = $(this).data('id'); // ✅ get friend ID

                // Prevent duplicate chatboxes
                if ($('#chatbox-' + friend_id).length > 0) return;

                let auth_id = null; // This is the current logged-in user's ID

                $.get('/user', function (res) {
                    auth_id = res.id;

                    // create new chat

                    $.get('/initialize-chat/' + auth_id + '/' + friend_id, function (res) {

                        const polling = new PollingManager({
                            url: `/fetch-messages/${res}`, // API to fetch data
                            delay: 5000, // Poll every 5 seconds
                            failRetryCount: 3, // Retry on failure
                            onSuccess: (messageResponse) => {
                                console.log((messageResponse.chat.isGroup === 1) ? 'is group' : 'not group');

                                const chatId = messageResponse.chatId;
                                const chatboxSelector = `#chatbox-${chatId}`;

                                // Build messages HTML
                                let messageHtml = '';
                                messageResponse.messages.forEach(msg => {
                                    const isMine = msg.users_id === auth_id;

                                    const timestamp = new Date(msg.created_at).toLocaleString('en-US', {
                                        weekday: 'long',
                                        year: 'numeric',
                                        month: 'long',
                                        day: 'numeric',
                                        hour: 'numeric',
                                        minute: '2-digit',
                                        hour12: true,
                                    });

                                    function formatMessage(text) {
                                        // Convert URLs to clickable links
                                        const urlPattern = /(\bhttps?:\/\/[^\s<>"]+[^\s<>"'.])/gi;
                                        let linkedText = text.replace(urlPattern, (url) => {
                                            return `<a href="${url}" target="_blank" rel="noopener noreferrer">${url}</a>`;
                                        });

                                        // Convert newlines (\n) into <br> tags
                                        return linkedText.replace(/\n/g, '<br>');
                                    }

                                    // Handle attachments (images or files)
                                    let attachmentsHtml = '';
                                    if (msg.files && msg.files.length > 0) {
                                        msg.files.forEach(file => {
                                            const ext = file.path.split('.').pop().toLowerCase();
                                            const isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext);
                                            const fileUrl = `/storage/${file.path}`;

                                            if (isImage) {
                                                attachmentsHtml += `
                                                    <div class="chat-attachment">
                                                        <a href="${fileUrl}">
                                                            <img src="${fileUrl}" alt="${file.original_name}" style="max-width: 100%; margin-top: 5px; border-radius: 8px;">
                                                        </a>
                                                    </div>`;
                                            } else {
                                                attachmentsHtml += `
                                                    <div class="chat-attachment">
                                                        <a href="${fileUrl}" target="_blank">
                                                            ${file.original_name}
                                                        </a>
                                                    </div>`;
                                            }
                                        });
                                    }

                                    
                                    console.log(msg)

                                    // Append the message with optional attachments
                                    messageHtml += `
                                        <div class="chat-msg ${isMine ? 'mine' : 'other'}">
                                            <div class="msg-container">
                                                <div class="bubble">
                                                    ${formatMessage(msg.message)}
                                                    ${attachmentsHtml}
                                                </div>

                                                <div class="timestamp">
                                                    ${timestamp}
                                                    ${(isMine && msg.message !== 'Unsent a message')
                                                        ? `<i class="fa fa-trash unsent-chat text-danger" data-id="${msg.id}"></i>`
                                                        : ''}
                                                </div>

                                            </div>
                                        </div>
                                    `;
                                });

                                // If chatbox already exists, update messages only
                                if ($(chatboxSelector).length > 0) {
                                    $(`${chatboxSelector} .chatbox-body`).html(messageHtml);
                                } else {
                                    // Otherwise, create a new chatbox
                                    const chatbox = `
                                        <div class="chatbox" id="chatbox-${chatId}">
                                            <div class="chatbox-header">
                                                <span>${messageResponse.chat.name}</span>
                                                <button data-bs-toggle="modal" data-bs-target="#chatFiles" data-id="${chatId}" class="show-chat-files" title="Show Files"
                                                        style="background:none;border:none;color:white;font-size:16px;"><i class="fa fa-file"></i></button>
                                                <button data-id="${chatId}" class="delete-chat" title="Delete Conversation"
                                                        style="background:none;border:none;color:white;font-size:16px;"><i class="fa fa-trash"></i></button>
                                                <button class="close-chat" title="Close"
                                                        style="background:none;border:none;color:white;font-size:16px;"><i class="fa fa-times"></i></button>
                                            </div>

                                            <div class="chatbox-body" id="chatbox-scrollable">
                                                ${messageHtml}
                                                <div id="latest-message"></div>
                                            </div>

                                            <div class="preview-box" id="file-preview-${chatId}"></div>

                                            <div class="chatbox-footer">
                                                <label class="file-icon">
                                                    <i class="fas fa-paperclip"></i>
                                                    <input type="file" id="file-input-${chatId}" multiple style="display: none;">
                                                </label>

                                                <textarea class="chat-input" placeholder="Type a message…" rows="1"></textarea>

                                                <button class="send-btn" title="Send" id="send-btn">
                                                    <i class="fas fa-paper-plane"></i>
                                                </button>
                                            </div>
                                        </div>
                                    `;

                                    $('#chatboxes-container').append(chatbox);

                                    // File Preview with Remove Option

                                    let selectedFiles = [];

                                    // Handle file selection
                                    $('#file-input-' + chatId).on('change', function (e) {
                                        selectedFiles = Array.from(e.target.files);
                                        renderFilePreviews();
                                    });

                                    // Render file previews
                                    function renderFilePreviews() {
                                        $('#file-preview-' + chatId).empty();

                                        selectedFiles.forEach((file, index) => {
                                            const isImage = file.type.startsWith('image/');
                                            const fileItem = $('<div>')
                                                .addClass('file-item')
                                                .attr('data-index', index)
                                                .css({
                                                    display: 'inline-block',
                                                    position: 'relative',
                                                    marginRight: '10px',
                                                    marginBottom: '10px'
                                                });

                                            if (isImage) {
                                                const reader = new FileReader();
                                                reader.onload = function (e) {
                                                    fileItem.append(`<img src="${e.target.result}" alt="${file.name}" style="max-width:80px;">`);
                                                    fileItem.append(`<span class="remove-file" style="position:absolute;top:0;right:0;background:#ff4444;color:#fff;border-radius:50%;width:18px;height:18px;line-height:18px;text-align:center;font-size:12px;cursor:pointer;">&times;</span>`);
                                                    $('#file-preview-' + chatId).append(fileItem);
                                                };
                                                reader.readAsDataURL(file);
                                            } else {
                                                fileItem.append(`<div>${file.name}</div>`);
                                                fileItem.append(`<span class="remove-file" style="margin-left:10px;color:red;cursor:pointer;">[Remove]</span>`);
                                                $('#file-preview-' + chatId).append(fileItem);
                                            }
                                        });
                                    }

                                    // Handle file removal
                                    $(document).on('click', '.remove-file', function () {
                                        const index = $(this).closest('.file-item').data('index');
                                        selectedFiles.splice(index, 1);
                                        renderFilePreviews();
                                    });

                                    // 🔥 Scroll to latest message *after* it's in the DOM
                                    setTimeout(() => {
                                        document.querySelector(`#chatbox-${chatId} #latest-message`)?.scrollIntoView({ behavior: 'smooth' });
                                    }, 100); // slight delay ensures DOM is rendered

                                    // send button

                                    $(`#chatbox-${chatId} .send-btn`).click(function () {
                                        let chatInput = $(`#chatbox-${chatId} .chat-input`).val();
                                        let usersId = auth_id;
                                        let hasAttachments = selectedFiles.length > 0 ? 1 : 0;

                                        // First: Send the message
                                        $.post('/send-chat', {
                                            'message': chatInput,
                                            'hasAttachments': hasAttachments,
                                            'chats_id': chatId,
                                            'senders_id': usersId,
                                            '_token': $('meta[name="csrf-token"]').attr('content')
                                        }, function (messageResponse) {
                                            // Optional: get the new message ID if needed
                                            let messageId = messageResponse.message_id ?? null;

                                            if (hasAttachments && messageId) {
                                                // Upload files AFTER message is saved
                                                const formData = new FormData();
                                                formData.append('chats_id', chatId);
                                                formData.append('messages_id', messageId);
                                                formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

                                                selectedFiles.forEach(file => {
                                                    formData.append('files[]', file);
                                                });

                                                $.ajax({
                                                    url: '/upload-files',
                                                    method: 'POST',
                                                    data: formData,
                                                    contentType: false,
                                                    processData: false,
                                                    success: function (uploadRes) {
                                                        appendChatToUI(chatInput, chatId, uploadRes.files || []);
                                                        clearChatUI(chatId);
                                                    },
                                                    error: function (err) {
                                                        console.log('File upload failed:', err);
                                                    }
                                                });
                                            } else {
                                                // No files to upload
                                                appendChatToUI(chatInput, chatId, []);
                                                clearChatUI(chatId);
                                            }
                                        }).fail(function (err) {
                                            console.log('Message send failed:', err);
                                        });
                                    });

                                    // Append message + optional attachments to UI
                                    function appendChatToUI(message, chatId, attachments = []) {
                                        let filePreviewHtml = '';

                                        attachments.forEach(att => {
                                            if (att.path.match(/\.(jpg|jpeg|png|gif)$/i)) {
                                                filePreviewHtml += `<img style="width: 100%;" src="/storage/${att.path}" style="max-width: 100px; display: block; margin-top: 5px;">`;
                                            } else {
                                                filePreviewHtml += `<div><a href="/storage/${att.path}" target="_blank">${att.original}</a></div>`;
                                            }
                                        });

                                        $(`#chatbox-${chatId} .chatbox-body`).append(`
                                            <div class="chat-msg mine">
                                                <div class="msg-container" style="width: 100%">
                                                    <div class="bubble d-flex align-items-center">
                                                        <div class="spinner-border me-2" style="width: 10px; height: 10px;"></div>
                                                        <span>Sending...</span>
                                                    </div>
                                                    <div class="timestamp">just now</div>
                                                </div>
                                                <div id="latest-message"></div>
                                            </div>
                                        `);

                                        setTimeout(() => {
                                            document.querySelector(`#chatbox-${chatId} #latest-message`)?.scrollIntoView({ behavior: 'smooth' });
                                        }, 50);
                                    }

                                    // Clear input and preview
                                    function clearChatUI(chatId) {
                                        $(`#chatbox-${chatId} .chat-input`).val("");
                                        $('#file-preview-' + chatId).empty();
                                        selectedFiles = [];
                                    }

                                    // end sent button
                                }
                            },
                            onError: (error) => {
                                console.error("Error fetching data:", error);
                                // Your custom error handling logic
                            }
                        });

                        // Start polling
                        polling.start();

                    }).fail(err => {
                        console.log(err)
                    });

                })
            });
        });
    </script>

</div>
