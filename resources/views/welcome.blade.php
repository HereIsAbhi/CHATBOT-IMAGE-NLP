<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Chat Assistant</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

            @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">
    </head>
    <body class="bg-gradient-to-br from-gray-900 via-blue-900 to-gray-900 min-h-screen flex items-center justify-center">
        <!-- Centered Chat Window -->
        <div class="w-[95%] max-w-[800px] mx-auto h-[600px] glass-morphism rounded-2xl overflow-hidden chat-window 
                    flex flex-col">
            <!-- Chat Header -->
            <div class="px-6 py-4 flex items-center justify-between border-b border-white/10">
                <div class="flex items-center space-x-3">
                    <div class="relative">
                        <div class="w-2.5 h-2.5 bg-emerald-500 rounded-full status-dot"></div>
                        <div class="absolute inset-0 bg-emerald-500 rounded-full opacity-50 animate-ping"></div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <img src="https://ui-avatars.com/api/?background=6366f1&color=fff&name=AI&bold=true" 
                             class="w-10 h-10 rounded-full" alt="AI Assistant">
                        <div>
                            <h3 class="text-white font-medium text-lg">AI Assistant</h3>
                            <div class="text-emerald-400 text-xs">Online</div>
                        </div>
                    </div>
                </div>
                <button class="text-white/70 hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Chat Messages Area -->
            <div class="chat-container p-6 space-y-6 overflow-y-auto flex-1" id="chat-messages">
                <!-- Welcome Message -->
                <div class="flex items-start space-x-3 message-animate">
                    <img src="https://ui-avatars.com/api/?background=6366f1&color=fff&name=AI&bold=true" 
                         class="w-10 h-10 rounded-full" alt="AI Assistant">
                    <div class="bg-white/10 rounded-2xl p-4 text-white shadow-lg max-w-[80%]">
                        <p class="text-lg">Hello! You can send me images for analysis. Just click the image upload button below! 📸</p>
                    </div>
                </div>

                <!-- User Message Example -->
                <div class="flex items-start space-x-3 flex-row-reverse message-animate">
                    <img src="https://ui-avatars.com/api/?background=3b82f6&color=fff&name=You&bold=true" 
                         class="w-10 h-10 rounded-full" alt="You">
                    <div class="bg-blue-600/50 rounded-2xl p-4 text-white shadow-lg max-w-[80%]">
                        <p class="text-lg">Hi! I'd like to ask about image recognition.</p>
                    </div>
                </div>

                <!-- Typing Indicator -->
                <div class="flex items-start space-x-3 message-animate">
                    <img src="https://ui-avatars.com/api/?background=6366f1&color=fff&name=AI&bold=true" 
                         class="w-10 h-10 rounded-full" alt="AI Assistant">
                    <div class="bg-white/10 rounded-2xl p-4 shadow-lg">
                        <div class="typing-indicator">
                            <div class="typing-dot"></div>
                            <div class="typing-dot"></div>
                            <div class="typing-dot"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chat Input Area -->
            <div class="p-6 border-t border-white/10">
                <form id="chat-form" class="flex items-center space-x-4" enctype="multipart/form-data">
                    @csrf
                    <input type="text" 
                           id="message-input"
                           class="flex-1 bg-white/10 text-white rounded-xl px-5 py-3 
                                  border border-white/10 focus:outline-none focus:ring-2 
                                  focus:ring-blue-500/50 focus:border-transparent 
                                  placeholder-white/50 text-lg"
                           placeholder="Type your message...">
                    
                    <!-- Image Upload Button -->
                    <label class="text-white/70 hover:text-white p-3 rounded-xl
                                transition-colors duration-200 hover:bg-white/10 cursor-pointer">
                        <input type="file" 
                               id="image-upload" 
                               name="image" 
                               accept="image/*" 
                               class="hidden" 
                               onchange="handleImageUpload(this)">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </label>

                    <button type="button" 
                            class="text-white/70 hover:text-white p-3 rounded-xl
                                   transition-colors duration-200 hover:bg-white/10">
                        <svg class="w-6 h-6 microphone-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"></path>
                        </svg>
                    </button>

                    <button type="submit" 
                            class="bg-blue-600/80 hover:bg-blue-600 text-white p-3 rounded-xl
                                   transition-colors duration-200 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                        </svg>
                    </button>
                </form>
            </div>
        </div>

        <!-- JavaScript for handling image uploads -->
        <script>
            let currentImageContext = null;

            function handleImageUpload(input) {
                if (input.files && input.files[0]) {
                    const formData = new FormData();
                    formData.append('image', input.files[0]);
                    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

                    // Show upload preview in chat
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const messageHtml = `
                            <div class="flex items-start space-x-3 flex-row-reverse message-animate">
                                <img src="https://ui-avatars.com/api/?background=3b82f6&color=fff&name=You&bold=true" 
                                     class="w-10 h-10 rounded-full" alt="You">
                                <div class="bg-blue-600/50 rounded-2xl p-4 text-white shadow-lg max-w-[80%]">
                                    <img src="${e.target.result}" class="rounded-lg max-w-full h-auto mb-2" alt="Uploaded image">
                                    <p class="text-sm text-white/70">Analyzing image...</p>
                                </div>
                            </div>
                        `;
                        document.getElementById('chat-messages').insertAdjacentHTML('beforeend', messageHtml);
                        scrollToBottom();
                    }
                    reader.readAsDataURL(input.files[0]);

                    // Send image to server
                    fetch('/upload-image', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Store the image context for future messages
                            currentImageContext = data.details;
                            
                            // Add AI response to chat
                            const responseHtml = `
                                <div class="flex items-start space-x-3 message-animate">
                                    <img src="https://ui-avatars.com/api/?background=6366f1&color=fff&name=AI&bold=true" 
                                         class="w-10 h-10 rounded-full" alt="AI Assistant">
                                    <div class="bg-white/10 rounded-2xl p-4 text-white shadow-lg max-w-[80%]">
                                        <p class="text-lg whitespace-pre-line">${data.message}</p>
                                    </div>
                                </div>
                            `;
                            document.getElementById('chat-messages').insertAdjacentHTML('beforeend', responseHtml);
                            scrollToBottom();
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showError('Failed to analyze image. Please try again.');
                    });
                }
            }

            // Handle form submission
            document.getElementById('chat-form').addEventListener('submit', function(e) {
                e.preventDefault();
                const messageInput = document.getElementById('message-input');
                const message = messageInput.value.trim();
                
                if (message) {
                    // Add user message to chat
                    const messageHtml = `
                        <div class="flex items-start space-x-3 flex-row-reverse message-animate">
                            <img src="https://ui-avatars.com/api/?background=3b82f6&color=fff&name=You&bold=true" 
                                 class="w-10 h-10 rounded-full" alt="You">
                            <div class="bg-blue-600/50 rounded-2xl p-4 text-white shadow-lg max-w-[80%]">
                                <p class="text-lg">${escapeHtml(message)}</p>
                            </div>
                        </div>
                    `;
                    document.getElementById('chat-messages').insertAdjacentHTML('beforeend', messageHtml);
                    messageInput.value = '';
                    scrollToBottom();

                    // Show typing indicator
                    const typingHtml = `
                        <div class="flex items-start space-x-3 message-animate" id="typing-indicator">
                            <img src="https://ui-avatars.com/api/?background=6366f1&color=fff&name=AI&bold=true" 
                                 class="w-10 h-10 rounded-full" alt="AI Assistant">
                            <div class="bg-white/10 rounded-2xl p-4 shadow-lg">
                                <div class="typing-indicator">
                                    <div class="typing-dot"></div>
                                    <div class="typing-dot"></div>
                                    <div class="typing-dot"></div>
                                </div>
                            </div>
                        </div>
                    `;
                    document.getElementById('chat-messages').insertAdjacentHTML('beforeend', typingHtml);
                    scrollToBottom();

                    // Send message to chatbot
                    fetch('/chat', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            message: message,
                            imageContext: currentImageContext
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        // Remove typing indicator
                        document.getElementById('typing-indicator')?.remove();

                        // Add AI response
                        const responseHtml = `
                            <div class="flex items-start space-x-3 message-animate">
                                <img src="https://ui-avatars.com/api/?background=6366f1&color=fff&name=AI&bold=true" 
                                     class="w-10 h-10 rounded-full" alt="AI Assistant">
                                <div class="bg-white/10 rounded-2xl p-4 text-white shadow-lg max-w-[80%]">
                                    <p class="text-lg whitespace-pre-line">${data.message}</p>
                                </div>
                            </div>
                        `;
                        document.getElementById('chat-messages').insertAdjacentHTML('beforeend', responseHtml);
                        scrollToBottom();
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        document.getElementById('typing-indicator')?.remove();
                        showError('Failed to send message. Please try again.');
                    });
                }
            });

            function showError(message) {
                const errorHtml = `
                    <div class="flex items-start space-x-3 message-animate">
                        <img src="https://ui-avatars.com/api/?background=6366f1&color=fff&name=AI&bold=true" 
                             class="w-10 h-10 rounded-full" alt="AI Assistant">
                        <div class="bg-red-500/50 rounded-2xl p-4 text-white shadow-lg max-w-[80%]">
                            <p class="text-lg">⚠️ ${message}</p>
                        </div>
                    </div>
                `;
                document.getElementById('chat-messages').insertAdjacentHTML('beforeend', errorHtml);
                scrollToBottom();
            }

            function scrollToBottom() {
                const chatMessages = document.getElementById('chat-messages');
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }

            function escapeHtml(unsafe) {
                return unsafe
                    .replace(/&/g, "&amp;")
                    .replace(/</g, "&lt;")
                    .replace(/>/g, "&gt;")
                    .replace(/"/g, "&quot;")
                    .replace(/'/g, "&#039;");
            }
        </script>
    </body>
</html>
