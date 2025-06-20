
<!DOCTYPE html>
<html lang='{{ str_replace('_', '-', app()->getLocale()) }}'>
    <head>
        <meta charset='utf-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1'>
        <meta name='csrf-token' content='{{ csrf_token() }}'>
        <meta name='author' content='Mark Jason Penote Espelita'>
        <meta name='keywords' content='keyword1, keyword2'>
        <meta name='description' content='Dolorem natus ab illum beatae error voluptatem incidunt quis. Cupiditate ullam doloremque delectus culpa. Autem harum dolorem praesentium dolorum necessitatibus iure quo. Et ea aut voluptatem expedita.'>

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link href='{{ url('assets/bootstrap/bootstrap.min.css') }}' rel='stylesheet'>
        <!-- FontAwesome for icons -->
        <link href='{{ url('assets/font-awesome/css/all.min.css') }}' rel='stylesheet'>
        <link rel='stylesheet' href='{{ url('assets/custom/style.css') }}'>
        <link rel='icon' href='{{ url('assets/logo.png') }}'>
    </head>
    <body class='font-sans antialiased'>

        <!-- Sidebar for Desktop View -->
        <div class='sidebar' id='mobileSidebar'>
            <div class='logo'>
                <img src='{{ url('assets/logo.png') }}' alt='' width='100%'>
            </div>

            @php
                $navLinks = [
                    [
                        'url' => 'dashboard',
                        'icon' => 'fas fa-tachometer-alt',
                        'label' => 'Dashboard',
                        'active' => request()->is('dashboard'),
                    ],
                    [
                        'url' => 'activity-logs',
                        'icon' => 'fas fa-bars',
                        'label' => 'Logs',
                        'active' => request()->is('activity-logs', 'create-logs', 'show-logs/*', 'edit-logs/*', 'delete-logs/*', 'logs-search*'),
                    ],
                    // [
                    //     'url' => 'todos',
                    //     'icon' => 'fas fa-bars',
                    //     'label' => 'Todos',
                    //     'active' => request()->is('todos', 'create-todos', 'trash-todos', 'show-todos/*', 'edit-todos/*', 'delete-todos/*', 'todos-search*'),
                    // ],
                ];
        @endphp

        @foreach ($navLinks as $link)
            <a href="{{ url($link['url']) }}" class="{{ $link['active'] ? 'active' : '' }}">
                <i class="{{ $link['icon'] }}"></i> {{ $link['label'] }}
            </a>
        @endforeach

        <a href="release-notes.html">
            <i class="fas fa-file-alt"></i> Release Notes
        </a>

        <a href="{{ url('user/profile') }}">
            <i class="fas fa-user"></i> {{ Auth::user()->name }}
        </a>

        </div>

        <!-- Top Navbar -->
        <nav class='navbar navbar-expand-lg navbar-dark'>
            <div class='container-fluid'>
                <button class='navbar-toggler' type='button' data-bs-toggle='collapse' data-bs-target='#navbarNav'
                    aria-controls='navbarNav' aria-expanded='false' aria-label='Toggle navigation' onclick='toggleSidebar()'>
                    <i class='fas fa-bars'></i>
                </button>
            </div>
        </nav>

        <x-main-notification />

        <div class='content'>
            @yield('content')

            <!-- Toggle Button -->
            <button id="toggleButton">
                <i class="fas fa-user"></i>
            </button>

            <!-- Dark background overlay -->
            <div id="overlay" style="display: none;"></div>

            <!-- Styles -->
            <style>
                body {
                    margin: 0;
                    font-family: Arial, sans-serif;
                }

                /* SIDEBAR */
                #friendsSidebar {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    max-width: 30%;
                    height: 100vh;
                    background: #ffffff;
                    border-right: 1px solid #e0e0e0;
                    padding: 1rem;
                    overflow-y: auto;
                    display: none;
                    z-index: 1001;
                    box-shadow: 4px 0 12px rgba(0, 0, 0, 0.05);
                }

                .friend {
                    padding: 12px 16px;
                    margin-bottom: 10px;
                    background: #f8f9fa;
                    border-radius: 12px;
                    display: flex;
                    align-items: center;
                    gap: 12px;
                    cursor: pointer;
                    transition: all 0.2s ease-in-out;
                    border: 1px solid transparent;
                    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
                }

                .friend:hover {
                    background: #e9f5ff;
                    border-color: #007bff;
                    transform: translateY(-2px);
                }

                /* TOGGLE FAB */
                #toggleButton {
                    position: fixed;
                    bottom: 6rem;
                    right: 2rem;
                    width: 56px;
                    height: 56px;
                    background: linear-gradient(135deg, #005f9f, #0084ff);
                    color: white;
                    border: none;
                    border-radius: 50%;
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 22px;
                    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
                    transition: background 0.3s, transform 0.2s;
                }

                #toggleButton:hover {
                    transform: scale(1.1);
                    background: linear-gradient(135deg, #0072ce, #009bff);
                }

                /* OVERLAY */
                #overlay {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100vw;
                    height: 100vh;
                    background-color: rgba(0, 0, 0, 0.4);
                    backdrop-filter: blur(3px);
                    z-index: 1000;
                    display: none;
                }

                /* CHATBOX */
                .chatbox {
                    width: 100%;
                    max-width: 400px;
                    height: 50vh;
                    background: white;
                    border: none;
                    border-radius: 0;
                    box-shadow: none;
                    display: flex;
                    flex-direction: column;
                }

                .chatbox-header {
                    background: #0C8EFD;
                    color: white;
                    padding: 12px 15px;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    font-weight: bold;
                }

                .chatbox-body {
                    padding: 10px;
                    flex: 1;
                    overflow-y: auto;
                }

                .chatbox-footer {
                    padding: 8px;
                    border-top: 1px solid #ccc;
                }

                .chatbox-footer textarea {
                    width: 100%;
                    padding: 12px;
                    border: 1px solid #ddd;
                    border-radius: 8px;
                    font-size: 16px;
                }

                .mine .msg-container {
                    display: flex;
                    flex-direction: column;
                    align-items: flex-end; /* Align timestamp to the right */
                    max-width: 80%;
                }

                /* MESSAGES */
                .chat-msg {
                    display: flex;
                    margin: 10px 0;
                }

                .chat-msg.mine {
                    justify-content: flex-end;
                }

                .chat-msg.other {
                    justify-content: flex-start !important;
                }

                .chat-msg .bubble {
                    display: inline-block;
                    padding: 10px 14px;
                    border-radius: 18px;
                    position: relative;
                    font-size: 14px;
                    color: #000;
                    word-break: break-word;
                    max-width: 80%; /* still useful for long messages */
                    width: fit-content;
                }

                .chat-msg.other .bubble {
                    background-color: #cacaca;
                    align-self: flex-start; /* ensure it's not stretched */
                }

                .chat-msg.other .bubble::after {
                    content: "";
                    position: absolute;
                    top: 10px;
                    left: -6px;
                    width: 0;
                    height: 0;
                    border-top: 8px solid transparent;
                    border-right: 8px solid #cacaca;
                    border-bottom: 8px solid transparent;
                }

                .chat-msg.mine .bubble {
                    background: linear-gradient(135deg, #0084ff, #44bef1);
                    color: white;
                }

                .chat-msg.mine .bubble::after {
                    content: "";
                    position: absolute;
                    top: 10px;
                    right: -6px;
                    width: 0;
                    height: 0;
                    border-top: 8px solid transparent;
                    border-left: 8px solid #44bef1;
                    border-bottom: 8px solid transparent;
                }

                .timestamp {
                    font-size: 11px;
                    margin-top: 5px;
                    color: #000000;
                    text-align: right;
                }

                /* File upload */

                .preview-box {
                    margin-bottom: 10px;
                }

                .preview-box img {
                    max-width: 100px;
                    margin-right: 10px;
                }

                .file-icon {
                    font-size: 18px;
                    cursor: pointer;
                    margin-right: 10px;
                    color: #444;
                }

                /* MEDIA QUERY FOR MOBILE */
                @media (max-width: 768px) {
                    #friendsSidebar {
                        width: 100%;
                        max-width: none;
                        padding: 0.8rem;
                    }

                    .chatbox {
                        width: 100vw;
                        height: 90vh;
                        border-radius: 0;
                    }

                    #chatboxes-container {
                        right: 0 !important;
                    }

                    #toggleButton {
                        width: 50px;
                        height: 50px;
                        bottom: 6rem;
                        right: 1rem;
                        font-size: 20px;
                    }

                    .chatbox-footer input {
                        padding: 10px;
                        font-size: 14px;
                    }

                    .chat-msg .bubble {
                        font-size: 13px;
                        max-width: 85%;
                    }
                }
            </style>

            <!-- Make sure this comes BEFORE the script -->
            <x-side-chat :chats="App\Models\Chats::all()" />

            <!-- Chatbox Container -->
            <div id="chatboxes-container" style="position: fixed; bottom: 0; right: 90px; display: flex; gap: 10px; z-index: 1200;"></div>

        </div>

        {{-- apex charts --}}

        <script src="{{ url('assets/apexcharts/apexcharts.min.js') }}"></script>

        <!-- Bootstrap JS and dependencies -->
        <script src='{{ url('assets/bootstrap/bootstrap.bundle.min.js') }}'></script>

        <!-- jQuery CDN -->
        <script src="{{ url('assets/jquery/jquery.min.js') }}"></script>
        <script src="{{ url('assets/sweetalert/sweetalert.min.js') }}"></script>

        <!-- Script should be AFTER x-side-chat -->
        <script>
            $(document).ready(function () {
                $('#toggleButton').click(function () {
                    $('#friendsSidebar').fadeToggle(200);
                    $('#overlay').fadeToggle(200);
                });

                $('#overlay').click(function () {
                    $('#friendsSidebar').fadeOut(200);
                    $('#overlay').fadeOut(200);
                });

                $('.friend').click(function () {
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
                                    console.log(messageResponse);

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
                                                    <button data-id="${chatId}" class="delete-chat" title="Delete Conversation"
                                                            style="background:none;border:none;color:white;font-size:16px;"><i class="fa fa-trash"></i></button>
                                                    <button class="close-chat" title="Close"
                                                            style="background:none;border:none;color:white;font-size:16px;"><i class="fa fa-times"></i></button>
                                                </div>

                                                <div class="chatbox-body">
                                                    ${messageHtml}
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

                                        // Send Button

                                        $(`#chatbox-${chatId} .send-btn`).click(function () {
                                            let chatInput = $(`#chatbox-${chatId} .chat-input`).val();
                                            let usersId = auth_id;
                                            let hasAttachments = selectedFiles.length > 0 ? 1 : 0;

                                            // First: send message
                                            $.post('/send-chat', {
                                                'hasAttachments': hasAttachments,
                                                'message': chatInput,
                                                'chats_id': chatId,
                                                'senders_id': usersId,
                                                "_token": $('meta[name="csrf-token"]').attr('content')
                                            }, function (res) {
                                                const messageId = res.message_id;

                                                // If no files, we're done
                                                if (!hasAttachments) {
                                                    clearChatInput(chatId);
                                                    return;
                                                }

                                                // Else, upload files linked to that message
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
                                                        console.log('Files uploaded:', uploadRes);
                                                        clearChatInput(chatId);
                                                    },
                                                    error: function (err) {
                                                        console.error('File upload failed:', err);
                                                    }
                                                });

                                            }).fail(function (err) {
                                                console.error('Message failed:', err);
                                            });
                                        });

                                        function clearChatInput(chatId) {
                                            $(`#chatbox-${chatId} .chat-input`).val("");
                                            $('#file-preview-' + chatId).empty();
                                            selectedFiles = [];
                                        }

                                        // end of the sent button
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

                // Delegate close event
                $(document).on('click', '.close-chat', function () {
                    $(this).closest('.chatbox').remove();
                });

                // Delegate close event
                $(document).on('click', '.delete-chat', function () {
                    let chatId = $(this).attr('data-id');

                    Swal.fire({
                        title: "Do you want to delete this chat? It will be gone forever, and the other person might be confused.",
                        showDenyButton: true,
                        showCancelButton: true,
                        confirmButtonText: "Yes",
                        denyButtonText: `Don't delete`
                    }).then((result) => {
                        /* Read more about isConfirmed, isDenied below */
                        if (result.isConfirmed) {

                            Swal.fire({
                                title: 'Deleting...',
                                text: '',
                                showConfirmButton: false,
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                            });

                            $.post('/delete-chat', {
                                chatId: chatId,
                                _token: $('meta[name="csrf-token"]').attr('content')
                            }, function (res) {
                                $("#chatbox-" + chatId).remove();
                                Swal.fire("Deleted!", "", "success");
                            }).fail(err => {
                                $("#chatbox-" + chatId).remove();
                                Swal.fire("Something went wrong!", "", "danger");
                            })

                        } else if (result.isDenied) {
                            Swal.fire("Deletion cancelled", "", "info");
                        }
                    });
                });

                // unsent chat

                $(document).on('click', '.unsent-chat', function () {
                    let messageId = $(this).attr('data-id');

                    Swal.fire({
                        title: "Do you want to unsent this message?",
                        showDenyButton: true,
                        showCancelButton: true,
                        confirmButtonText: "Yes",
                        denyButtonText: `Don't delete`
                    }).then((result) => {
                        /* Read more about isConfirmed, isDenied below */
                        if (result.isConfirmed) {

                            Swal.fire({
                                title: 'Unsending...',
                                text: '',
                                showConfirmButton: false,
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                            });

                            $.post('/unsent-message', {
                                messageId: messageId,
                                _token: $('meta[name="csrf-token"]').attr('content')
                            }, function (res) {
                                Swal.fire("Unsent!", "", "success");
                            }).fail(err => {
                                $("#chatbox-" + chatId).remove();
                                Swal.fire("Something went wrong!", "", "danger");
                            })

                        } else if (result.isDenied) {
                            Swal.fire("Cancelled", "", "info");
                        }
                    });
                });
            });
        </script>

        <!-- Custom JavaScript -->
        <script src="{{ url('assets/custom/script.js') }}"></script>
        <script>
            function toggleSidebar() {
                document.getElementById('mobileSidebar').classList.toggle('active');
                document.getElementById('sidebar').classList.toggle('active');
            }
        </script>

        <script src="{{ url('assets/angular/angular.min.js') }}"></script>

        <script>
            (function() {
                const style = document.createElement('style');
                style.textContent = `
                        .chat-toggle-btn {
                            position: fixed;
                            bottom: 20px;
                            right: 20px;
                            z-index: 1000;
                            background: #1D001D;
                            color: white;
                            border: none;
                            border-radius: 50%;
                            width: 60px;
                            height: 60px;
                            font-size: 24px;
                            cursor: pointer;
                            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
                        }
                        .chatbox-wrapper {
                            position: fixed;
                            bottom: 90px;
                            right: 20px;
                            width: 100%;
                            max-width: 30%;
                            height: 60%;
                            background: white;
                            border-radius: 15px;
                            box-shadow: 0 0 20px rgba(0,0,0,0.2);
                            display: none;
                            flex-direction: column;
                            z-index: 1000;
                            overflow: hidden;
                        }
                        .chatbox-header {
                            color: white;
                            padding: 10px;
                            font-weight: bold;
                            text-align: center;
                        }
                        .chatbox-messages {
                            flex: 1;
                            overflow-y: auto;
                            padding: 10px;
                            display: flex;
                            flex-direction: column;
                        }
                        .chatbox-message {
                            margin: 5px 0;
                            padding: 8px 12px;
                            border-radius: 10px;
                            max-width: 80%;
                            white-space: pre-wrap;
                        }
                        .chatbox-message.user {
                            background: #0C8EFD;
                            align-self: flex-end;
                            color: #fff;
                        }
                        .chatbox-message.bot {
                            background: #e2e3e5;
                            align-self: flex-start;
                        }
                        .chatbox-footer {
                            display: flex;
                            padding: 10px;
                            gap: 5px;
                            border-top: 1px solid #eee;
                            background: white;
                        }
                        .chatbox-footer input {
                            flex: 1;
                            padding: 6px 10px;
                            border: 1px solid #ccc;
                            border-radius: 5px;
                        }
                        .chatbox-footer button {
                            background: #0C8EFD;
                            color: white;
                            border: none;
                            padding: 6px 12px;
                            border-radius: 5px;
                            cursor: pointer;
                        }
                        .chatbox-typing {
                            font-style: italic;
                            color: gray;
                            padding: 4px 10px;
                        }
                        .dropdown {
                            padding: 10px;
                        }
                        .dropdown-menu.show {
                            display: block;
                            position: static;
                            float: none;
                        }
                        .dropdown-item {
                            cursor: pointer;
                        }

                        @media (max-width: 480px) {
                            .chatbox-wrapper {
                            max-width: 95vw;
                            height: 80vh;

                            bottom: 80px;
                            right: 10px;
                            border-radius: 10px;
                            }
                            .chat-toggle-btn {
                            width: 50px;
                            height: 50px;
                            font-size: 20px;
                            bottom: 20px;
                            right: 15px;
                            }
                        }
                        `;
                document.head.appendChild(style);

                const chatButton = document.createElement('button');
                chatButton.className = 'chat-toggle-btn';
                chatButton.innerHTML = '&#128172;';
                document.body.appendChild(chatButton);

                const chatContainer = document.createElement('div');
                chatContainer.className = 'chatbox-wrapper';

                const faqData = [{
                        question: "How to add an item?",
                        response: "How to add an item \n\n1. Navigate to the Items page \n2. Click the 'Add Item' button on the top-right corner of the page and fill out the required fields. \n\nRemember: Determine first whether the item has a serial number or not before proceeding."
                    },
                    {
                        question: "How to edit/update an item?",
                        response: "How to edit/update an item \n\n1. Click the edit icon. \nNote: Items with serial numbers and those without are handled differently. If you want to decrease the quantity of an item with a serial number, click the specific serial number you want to remove—its quantity will decrease. For items without a serial number, simply update their quantity."
                    },
                    {
                        question: "How to delete an item?",
                        response: "To delete an item, use the delete icon next to it."
                    },
                    {
                        question: "Where to find settings?",
                        response: "Settings can be found under the top-right user menu."
                    }
                ];

                // Generate dropdown HTML from JSON
                const dropdownHTML = faqData.map(item =>
                    `<li><a class="dropdown-item" href="#" data-question="${item.question}" data-response="${item.response}">${item.question}</a></li>`
                ).join('');

                // Insert HTML into chat container
                chatContainer.innerHTML = `
                        <div ng-app="chatApp" ng-controller="ChatController" style="display: flex; flex-direction: column; height: 100%;">
                            <div class="chatbox-header">Librify / GPT-3 [Experimental]</div>
                            <div class="chatbox-messages" id="chatboxMessages">
                            <div ng-if="!beginConversation" class="no-chat-placeholder text-center text-muted my-3">
                                <i class="fas fa-robot fa-2x d-block mb-2"></i>
                                <small>
                                    This is the Librify Automated Chatbot together with the GPT-3 model. <br>
                                    For system-related questions, click "Ask question about the system." <br>
                                    For a general AI conversation, simply type your message below. <br>
                                    Note: GPT-3 does not have knowledge about our specific system.
                                </small>
                            </div>
                            <div ng-repeat="msg in messages" class="chatbox-message" ng-class="msg.sender">[[ msg.text ]]</div>
                            <div class="chatbox-typing" ng-show="isTyping">Bot is typing...</div>
                            </div>
                            <div class="dropdown">
                            <input class="form-control mb-2" id="dropdownSearch" type="text" placeholder="Ask question about the system...">
                            <ul class="dropdown-menu show w-100" id="customDropdown" style="display:none;">
                                ${dropdownHTML}
                            </ul>
                            </div>
                            <form ng-submit="sendMessage()" class="chatbox-footer">
                            <input type="text" ng-model="userInput" placeholder="Send a message to GPT-3..." required>
                            <button type="submit">Send</button>
                            </form>
                        </div>
                        `;



                document.body.appendChild(chatContainer);

                chatButton.addEventListener('click', () => {
                    chatContainer.style.display = chatContainer.style.display === 'flex' ? 'none' : 'flex';
                    const msgBox = document.getElementById('chatboxMessages');
                    setTimeout(() => {
                        msgBox.scrollTop = msgBox.scrollHeight;
                    }, 100);
                });

                const app = angular.module('chatApp', []);

                app.config(['$interpolateProvider', function($interpolateProvider) {
                    $interpolateProvider.startSymbol('[[');
                    $interpolateProvider.endSymbol(']]');
                }]);

                app.controller('ChatController', ['$scope', '$http', '$timeout', function($scope, $http, $timeout) {
                    $scope.messages = [];
                    $scope.userInput = '';
                    $scope.isTyping = false;

                    $scope.beginConversation = false;

                    const scrollToBottom = () => {
                        $timeout(() => {
                            const container = document.getElementById('chatboxMessages');
                            container.scrollTop = container.scrollHeight;
                        }, 50);
                    };

                    $scope.sendMessage = function() {

                        $scope.beginConversation = true;

                        const input = $scope.userInput.trim();
                        if (!input) return;

                        $scope.messages.push({
                            text: input,
                            sender: 'user'
                        });
                        $scope.userInput = '';
                        $scope.isTyping = true;
                        scrollToBottom();

                        $http.get('https://text.pollinations.ai/' + encodeURIComponent(input), {
                            headers: {
                                'Content-Type': 'text/plain'
                            }
                        }).then(function(response) {
                            const fullText = response.data;
                            let typedText = '';
                            let i = 0;

                            function typeChar() {
                                if (i < fullText.length) {
                                    typedText += fullText.charAt(i);
                                    $timeout(() => {
                                        if ($scope.messages[$scope.messages.length - 1].sender !== 'bot') {
                                            $scope.messages.push({
                                                text: '',
                                                sender: 'bot'
                                            });
                                        }
                                        $scope.messages[$scope.messages.length - 1].text = typedText;
                                        scrollToBottom();
                                        i++;
                                        typeChar();
                                    }, 20);
                                } else {
                                    $scope.isTyping = false;
                                }
                            }

                            typeChar();
                        }).catch(function(error) {
                            $scope.isTyping = false;
                            $scope.messages.push({
                                text: 'Error: Something went wrong, please check your internet connection and try again.',
                                sender: 'bot'
                            });
                            scrollToBottom();
                        });
                    };
                }]);

                angular.bootstrap(chatContainer, ['chatApp']);

                // Dropdown search & select logic
                const dropdownSearch = document.getElementById('dropdownSearch');
                const dropdownItems = document.querySelectorAll('#customDropdown .dropdown-item');

                dropdownSearch.addEventListener('input', function() {
                    document.getElementById('customDropdown').style.display = 'block';
                    const filter = this.value.toLowerCase();
                    dropdownItems.forEach(item => {
                        const text = item.textContent.toLowerCase();
                        item.style.display = text.includes(filter) ? 'block' : 'none';
                    });
                });

                dropdownItems.forEach(item => {
                    item.addEventListener('click', function(e) {

                        const scopeElBeginConversation = document.querySelector('[ng-controller="ChatController"]');
                        if (scopeElBeginConversation) {
                            const scope = angular.element(scopeElBeginConversation).scope();
                            scope.$apply(() => {
                                scope.beginConversation = true;
                            });
                        }

                        e.preventDefault();
                        document.getElementById('customDropdown').style.display = 'none';

                        const question = this.getAttribute('data-question');
                        const response = this.getAttribute('data-response');

                        const scopeEl = document.querySelector('[ng-controller="ChatController"]');
                        if (scopeEl) {
                            const scope = angular.element(scopeEl).scope();
                            scope.$apply(() => {
                                scope.messages = scope.messages || [];
                                scope.messages.push({
                                    text: question,
                                    sender: 'user'
                                });
                                scope.isTyping = true;
                            });

                            let typedText = '';
                            let i = 0;

                            function typeChar() {
                                if (i < response.length) {
                                    typedText += response.charAt(i);
                                    setTimeout(() => {
                                        scope.$apply(() => {
                                            const messages = scope.messages;
                                            if (!messages.length || messages[messages.length - 1].sender !== 'bot') {
                                                messages.push({
                                                    text: '',
                                                    sender: 'bot'
                                                });
                                            }
                                            messages[messages.length - 1].text = typedText;
                                            scope.isTyping = true;

                                            const container = document.getElementById('chatboxMessages');
                                            if (container) container.scrollTop = container.scrollHeight;
                                            i++;
                                            typeChar();
                                        });
                                    }, 20);
                                } else {
                                    // Delay slightly to ensure DOM updates settle before marking as done
                                    setTimeout(() => {
                                        scope.$apply(() => {
                                            scope.isTyping = false;
                                        });
                                    }, 50);
                                }
                            }

                            typeChar();

                        }

                        dropdownSearch.value = '';
                    });
                });

            })();
        </script>
    </body>
</html>
