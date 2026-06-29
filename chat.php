<?php
require_once 'classes/Database.php';
require_once 'classes/User.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userObj = new User();
$currentUser = $userObj->getById($_SESSION['user_id']);
$friends = $userObj->getFriends($_SESSION['user_id']);
$userId = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evis Chat</title>
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/chat.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body>
    <div class="dashboard" x-data="chatApp(<?php echo $userId; ?>)" x-init="init()">
        <!-- Sidebar -->
        <aside class="sidebar" :class="{'mobile-open': sidebarOpen}">
            <div class="sidebar-header">
                <h2>Chats</h2>
            </div>
            <div class="search-bar">
                <input type="text" placeholder="Search conversations..." x-model="searchQuery">
            </div>
            <div class="chat-list" id="friendList">
                <template x-for="friend in filteredFriends" :key="friend.id">
                    <div class="chat-item" :class="{'active': friend.id === activeFriendId}"
                         @click="openChat(friend.id, friend.name)">
                        <div class="chat-item-avatar" x-text="friend.name.charAt(0).toUpperCase()"></div>
                        <div class="chat-item-info">
                            <div class="chat-item-top">
                                <span class="chat-item-name" x-text="friend.name"></span>
                            </div>
                            <span class="chat-item-preview" x-text="friend.name"></span>
                        </div>
                    </div>
                </template>
            </div>
            <div class="sidebar-footer">
                <div class="user-info">
                    <div class="chat-item-avatar"><?php echo strtoupper(substr($currentUser['name'] ?? 'U', 0, 1)); ?></div>
                    <div>
                        <span class="user-name"><?php echo htmlspecialchars($currentUser['name'] ?? 'User', ENT_QUOTES); ?></span>
                        <span class="user-status">Online</span>
                    </div>
                </div>
                <a href="logout.php"><button class="logout-btn">Logout</button></a>
            </div>
            <button class="mobile-close" @click="sidebarOpen = false">&times;</button>
        </aside>

        <!-- Main Chat -->
        <main class="main-chat">
            <div class="chat-header" x-show="activeFriendId">
                <div class="header-info">
                    <div class="avatar" x-text="activeFriendName ? activeFriendName.charAt(0).toUpperCase() : ''"></div>
                    <div>
                        <h2 x-text="activeFriendName"></h2>
                        <span class="status">Online</span>
                    </div>
                </div>
                <div class="header-actions">
                    <button class="icon-btn mobile-back" @click="sidebarOpen = true">&#8592;</button>
                </div>
            </div>

            <div class="chat-messages" x-show="activeFriendId" x-ref="messagesContainer">
                <template x-for="message in messages" :key="message.id">
                    <div class="message" :class="message.sender_id == userId ? 'sent' : 'received'">
                        <template x-if="message.message">
                            <p x-text="message.message"></p>
                        </template>
                        <template x-if="message.audio_url">
                            <audio :src="message.audio_url" controls @play="playAudio($event)"></audio>
                        </template>
                        <span class="time" x-text="formatTime(message.timestamp)"></span>
                    </div>
                </template>
            </div>

            <div class="empty-state" x-show="!activeFriendId">
                <div class="empty-icon">💬</div>
                <h3>Select a chat</h3>
                <p>Choose a friend from the sidebar to start chatting</p>
            </div>

            <div class="chat-input" x-show="activeFriendId">
                <button class="record-btn" :class="{'recording': isRecording}" @click="toggleRecording()">
                    <svg x-show="!isRecording" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2a3 3 0 0 0-3 3v6a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3z"/>
                        <path d="M19 10v1a7 7 0 0 1-14 0v-1H3v1a9 9 0 0 0 8 8.94V21h-2v2h6v-2h-2v-1.06A9 9 0 0 0 21 11v-1h-2z"/>
                    </svg>
                    <svg x-show="isRecording" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 24 24">
                        <rect x="6" y="4" width="4" height="16" rx="1"/>
                        <rect x="14" y="4" width="4" height="16" rx="1"/>
                    </svg>
                </button>
                <input type="text" placeholder="Type a message..." x-model="messageText" @keydown.enter="sendMessage()">
                <button class="send-btn" @click="sendMessage()">&#10148;</button>
            </div>
        </main>
    </div>

    <audio id="persistentAudio" style="display:none"></audio>

    <script>
    function chatApp(userId) {
        return {
            userId: userId,
            messages: [],
            activeFriendId: null,
            activeFriendName: '',
            messageText: '',
            searchQuery: '',
            refreshInterval: null,
            sidebarOpen: false,
            isRecording: false,
            mediaRecorder: null,
            audioChunks: [],
            recordingStream: null,
            friends: <?php echo json_encode($friends); ?>,

            get filteredFriends() {
                if (!this.searchQuery) return this.friends;
                const q = this.searchQuery.toLowerCase();
                return this.friends.filter(f => f.name.toLowerCase().includes(q));
            },

            init() {
                this.sidebarOpen = window.innerWidth < 768;
            },

            openChat(friendId, friendName) {
                this.activeFriendId = friendId;
                this.activeFriendName = friendName;
                this.fetchMessages();
                this.startAutoRefresh();
                if (window.innerWidth < 768) {
                    this.sidebarOpen = false;
                }
            },

            fetchMessages() {
                if (!this.activeFriendId) return;
                fetch(`get_messages.php?friend_id=${this.activeFriendId}`)
                    .then(r => r.json())
                    .then(data => {
                        this.messages = data;
                        this.$nextTick(() => {
                            const el = this.$refs.messagesContainer;
                            if (el) el.scrollTop = el.scrollHeight;
                        });
                    })
                    .catch(() => {});
            },

            startAutoRefresh() {
                if (this.refreshInterval) clearInterval(this.refreshInterval);
                this.refreshInterval = setInterval(() => this.fetchMessages(), 5000);
            },

            sendMessage() {
                const text = this.messageText.trim();
                if (!text) return;
                const formData = new FormData();
                formData.append('receiver_id', this.activeFriendId);
                formData.append('message', text);
                fetch('send_message.php', { method: 'POST', body: formData })
                    .then(r => r.json())
                    .then(() => {
                        this.messageText = '';
                        this.fetchMessages();
                    })
                    .catch(() => {});
            },

            toggleRecording() {
                if (this.isRecording) {
                    this.stopRecording();
                } else {
                    this.startRecording();
                }
            },

            async startRecording() {
                try {
                    this.recordingStream = await navigator.mediaDevices.getUserMedia({ audio: true });
                    const mimeType = MediaRecorder.isTypeSupported('audio/webm;codecs=opus')
                        ? 'audio/webm;codecs=opus'
                        : 'audio/webm';
                    this.mediaRecorder = new MediaRecorder(this.recordingStream, { mimeType });
                    this.audioChunks = [];
                    this.isRecording = true;

                    this.mediaRecorder.ondataavailable = (e) => {
                        if (e.data.size > 0) {
                            this.audioChunks.push(e.data);
                        }
                    };

                    this.mediaRecorder.onstop = () => {
                        if (this.audioChunks.length === 0) {
                            this.cleanupRecording();
                            return;
                        }
                        const blob = new Blob(this.audioChunks, { type: this.mediaRecorder.mimeType });
                        this.audioChunks = [];
                        const formData = new FormData();
                        formData.append('receiver_id', this.activeFriendId);
                        formData.append('audio', blob, 'recording.' + (mimeType.includes('opus') ? 'webm' : 'webm'));
                        fetch('send_message.php', { method: 'POST', body: formData })
                            .then(r => r.json())
                            .then(() => {
                                this.fetchMessages();
                                this.cleanupRecording();
                            })
                            .catch(() => this.cleanupRecording());
                    };

                    this.mediaRecorder.start();
                } catch (e) {
                    console.error('Microphone access denied:', e);
                    this.isRecording = false;
                }
            },

            stopRecording() {
                if (this.mediaRecorder && this.mediaRecorder.state !== 'inactive') {
                    this.mediaRecorder.stop();
                }
            },

            cleanupRecording() {
                if (this.recordingStream) {
                    this.recordingStream.getTracks().forEach(t => t.stop());
                    this.recordingStream = null;
                }
                this.mediaRecorder = null;
                this.isRecording = false;
            },

            playAudio(event) {
                const persistent = document.getElementById('persistentAudio');
                if (persistent.src !== event.target.src) {
                    persistent.src = event.target.src;
                }
                persistent.play();
            },

            formatTime(timestamp) {
                if (!timestamp) return '';
                const parts = timestamp.split(/[- :]/);
                if (parts.length === 6) {
                    const d = new Date(parts[0], parts[1] - 1, parts[2], parts[3], parts[4], parts[5]);
                    if (!isNaN(d.getTime())) {
                        return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                    }
                }
                return timestamp;
            }
        };
    }
    </script>
</body>
</html>
