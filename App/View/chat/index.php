<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Chat</title>
  <link rel="stylesheet" href="/styles.css">
</head>
<body class="chat-body">
  <div class="messenger">

    <aside class="sidebar">
      <div class="sidebar-header">
        <div class="user-profile">
          <div class="user-avatar" style="background:#6366f1"><?= strtoupper($user['name'][0]) ?></div>
          <span class="user-name"><?= htmlspecialchars($user['name']) ?></span>
        </div>
        <a href="/logout" class="logout-btn" title="Sign out">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
            <polyline points="16 17 21 12 16 7"/>
            <line x1="21" y1="12" x2="9" y2="12"/>
          </svg>
        </a>
      </div>
      <div class="search-box">
        <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <input type="text" class="search-input" id="searchInput" placeholder="Search contacts...">
        <button class="add-contact-btn" id="addContactBtn" title="Add contact">+</button>
      </div>
      <nav class="contact-list" id="contactList"></nav>
    </aside>

    <main class="main-panel">
      <div class="chat-header" id="chatHeader">
        <div class="chat-header-left">
          <button class="back-btn" id="backBtn" title="Back to contacts">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
          </button>
          <div class="contact-avatar" id="chatAvatar" style="background:#6366f1">S</div>
          <div class="chat-header-info">
            <span class="chat-contact-name" id="chatContactName">Select a contact</span>
            <span class="chat-contact-status" id="chatContactStatus"></span>
          </div>
        </div>
      </div>

      <div class="messages-container" id="messagesContainer">
        <div class="empty-state" id="emptyState">
          <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
          </svg>
          <p>Select a conversation</p>
          <span>Choose a contact to start chatting</span>
        </div>
      </div>

      <div class="voice-recording-bar" id="voiceBar">
        <div class="waveform">
          <span></span><span></span><span></span><span></span><span></span>
          <span></span><span></span><span></span><span></span><span></span>
          <span></span><span></span><span></span><span></span><span></span>
          <span></span><span></span><span></span><span></span><span></span>
        </div>
        <span class="recording-label">Recording...</span>
        <button class="stop-recording-btn" id="stopRecordingBtn">
          <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><rect x="3" y="3" width="10" height="10" rx="2" fill="currentColor"/></svg>
        </button>
      </div>

      <div class="input-area" id="inputArea">
        <button class="action-btn voice-btn" id="voiceBtn" title="Voice message">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z"/>
            <path d="M19 10v2a7 7 0 0 1-14 0v-2"/>
            <line x1="12" y1="19" x2="12" y2="22"/>
          </svg>
        </button>
        <div class="input-wrapper">
          <input type="text" class="text-input" id="textInput" placeholder="Aa">
          <button class="emoji-btn" title="Emoji">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/>
            </svg>
          </button>
        </div>
        <button class="action-btn send-btn" id="sendBtn" title="Send">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
          </svg>
        </button>
      </div>
    </main>

  </div>

  <script>
    let currentContact = null;
    let lastMessageId = 0;
    let mediaRecorder = null;
    let audioChunks = [];
    const userId = <?= (int) $user['id'] ?>;

    const contactList = document.getElementById('contactList');
    const messagesContainer = document.getElementById('messagesContainer');
    const emptyState = document.getElementById('emptyState');
    const chatHeader = document.getElementById('chatHeader');
    const chatContactName = document.getElementById('chatContactName');
    const chatContactStatus = document.getElementById('chatContactStatus');
    const chatAvatar = document.getElementById('chatAvatar');
    const textInput = document.getElementById('textInput');
    const sendBtn = document.getElementById('sendBtn');
    const voiceBtn = document.getElementById('voiceBtn');
    const voiceBar = document.getElementById('voiceBar');
    const stopRecordingBtn = document.getElementById('stopRecordingBtn');
    const inputArea = document.getElementById('inputArea');
    const searchInput = document.getElementById('searchInput');

    let contacts = [];

    fetch('/api/contacts')
      .then(r => r.json())
      .then(data => {
        contacts = data;
        renderContactList();
      });

    function renderContactList(filter) {
      contactList.innerHTML = '';
      const filtered = filter
        ? contacts.filter(c => c.name.toLowerCase().includes(filter.toLowerCase()))
        : contacts;
      filtered.forEach(c => {
        const div = document.createElement('div');
        div.className = 'contact-item' + (currentContact && currentContact.id == c.id ? ' active' : '');
        div.innerHTML = `
          <div class="contact-avatar" style="background:${c.avatar_color || '#6366f1'}">${c.name[0]}</div>
          <div class="contact-info">
            <div class="contact-top">
              <span class="contact-name">${c.name}</span>
              <span class="status-indicator ${c.status}"></span>
            </div>
            <span class="contact-preview">${c.last_msg || ''}</span>
          </div>
        `;
        div.addEventListener('click', () => selectContact(c));
        contactList.appendChild(div);
      });
    }

    function selectContact(contact) {
      currentContact = contact;
      emptyState.style.display = 'none';
      chatHeader.style.display = 'flex';
      inputArea.style.display = 'flex';

      chatContactName.textContent = contact.name;
      chatAvatar.textContent = contact.name[0];
      chatAvatar.style.background = contact.avatar_color || '#6366f1';
      const labels = { online: 'Online', away: 'Away', offline: 'Offline' };
      chatContactStatus.textContent = labels[contact.status] || '';
      chatContactStatus.className = 'chat-contact-status ' + contact.status;

      renderContactList(searchInput.value);
      lastMessageId = 0;
      loadMessages(true);
      showChat();
    }

    function loadMessages(initial) {
      if (!currentContact) return;
      const url = '/chat/messages/' + currentContact.id + (initial ? '' : '?after=' + lastMessageId);
      fetch(url)
        .then(r => r.json())
        .then(msgs => {
          if (initial) messagesContainer.innerHTML = '';
          msgs.forEach(m => {
            if (m.id > lastMessageId) lastMessageId = m.id;
            renderMessage(m);
          });
          messagesContainer.scrollTop = messagesContainer.scrollHeight;
        });
    }

    function formatTime(dateStr) {
      if (!dateStr) return '';
      try {
        const d = new Date(dateStr.replace(' ', 'T') + 'Z');
        return d.toLocaleTimeString(undefined, { hour: '2-digit', minute: '2-digit' });
      } catch (e) {
        return '';
      }
    }

    function renderMessage(m) {
      const div = document.createElement('div');
      const isMine = m.sender_id == userId;
      div.className = 'message ' + (isMine ? 'sent' : 'received');
      const time = formatTime(m.created_at);
      if (m.type === 'voice') {
        div.innerHTML = `<div class="message-content voice"><audio controls style="height:36px;max-width:200px"><source src="${m.message}"></audio><span class="message-time">${time}</span></div>`;
      } else {
        div.innerHTML = `<div class="message-content"><p>${m.message}</p><span class="message-time">${time}</span></div>`;
      }
      messagesContainer.appendChild(div);
    }

    function sendMessage() {
      if (!currentContact) return;
      const text = textInput.value.trim();
      if (!text) return;
      const fd = new FormData();
      fd.append('receiver_id', currentContact.id);
      fd.append('message', text);
      fetch('/chat/send', { method: 'POST', body: fd }).then(() => {
        textInput.value = '';
        loadMessages();
        const c = contacts.find(x => x.id == currentContact.id);
        if (c) { c.last_msg = text; renderContactList(searchInput.value); }
      });
    }

    sendBtn.addEventListener('click', sendMessage);
    textInput.addEventListener('keydown', e => { if (e.key === 'Enter') sendMessage(); });

    voiceBtn.addEventListener('click', async function () {
      if (!currentContact) return;
      try {
        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
        const types = ['audio/mp4;codecs=mp4a.40.2', 'audio/mp4', 'audio/aac', 'audio/webm;codecs=opus', 'audio/webm', 'audio/ogg;codecs=opus'];
        const preferred = types.find(t => MediaRecorder.isTypeSupported(t)) || '';
        mediaRecorder = new MediaRecorder(stream, preferred ? { mimeType: preferred } : {});
        audioChunks = [];
        mediaRecorder.ondataavailable = e => audioChunks.push(e.data);
        mediaRecorder.onstop = () => {
          stream.getTracks().forEach(t => t.stop());
          const actual = mediaRecorder.mimeType || preferred || 'audio/webm';
          const ext = actual.includes('mp4') || actual.includes('aac') ? 'm4a' : actual.includes('ogg') ? 'ogg' : 'webm';
          const blob = new Blob(audioChunks, { type: actual });
          const fd = new FormData();
          fd.append('audio', blob, 'voice.' + ext);
          fd.append('receiver_id', currentContact.id);
          fetch('/chat/upload-voice', { method: 'POST', body: fd }).then(() => loadMessages());
        };
        mediaRecorder.start();
        voiceBar.classList.add('active');
      } catch (e) {
        alert('Microphone access denied');
      }
    });

    stopRecordingBtn.addEventListener('click', function () {
      if (mediaRecorder && mediaRecorder.state === 'recording') mediaRecorder.stop();
      voiceBar.classList.remove('active');
    });

    function isMobile() { return window.innerWidth <= 768; }

    function showSidebar() {
      document.querySelector('.sidebar').classList.remove('hidden');
      document.querySelector('.main-panel').classList.remove('active');
    }

    function showChat() {
      if (isMobile()) {
        document.querySelector('.sidebar').classList.add('hidden');
        document.querySelector('.main-panel').classList.add('active');
      }
    }

    document.getElementById('backBtn').addEventListener('click', showSidebar);

    setInterval(() => {
      if (currentContact) loadMessages();
    }, 5000);

    searchInput.addEventListener('input', function () {
      renderContactList(this.value);
    });

    document.getElementById('addContactBtn').addEventListener('click', function () {
      const email = prompt('Enter the email of the user to add:');
      if (!email) return;
      const fd = new FormData();
      fd.append('email', email);
      fetch('/api/contacts/add', { method: 'POST', body: fd })
        .then(r => r.json().then(d => ({ status: r.status, body: d })))
        .then(({ status, body }) => {
          if (status === 200) {
            alert('Contact added!');
            fetch('/api/contacts').then(r => r.json()).then(data => {
              contacts = data;
              renderContactList(searchInput.value);
            });
          } else {
            alert(body.error || 'Failed to add contact');
          }
        });
    });
  </script>
</body>
</html>
