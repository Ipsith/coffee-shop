/**
 * chatbot.js — Highland Assistant chatbot logic
 * Handles: widget open/close animation, sending messages to
 * chatbot_api.php via fetch(), rendering bubbles + typing indicator.
 */

document.addEventListener('DOMContentLoaded', () => {
  const toggleBtn = document.getElementById('chatbotToggle');
  const chatWindow = document.getElementById('chatWindow');
  const minimizeBtn = document.getElementById('chatMinimize');
  const chatBody = document.getElementById('chatBody');
  const chatForm = document.getElementById('chatForm');
  const chatInput = document.getElementById('chatInput');
  const quickOptions = document.getElementById('chatQuickOptions');
  const iconOpen = document.getElementById('chatIconOpen');
  const iconClose = document.getElementById('chatIconClose');

  let hasGreeted = false;

  // ---- Open / close the chat window ----
  function openChat() {
    chatWindow.classList.add('open');
    iconOpen.style.display = 'none';
    iconClose.style.display = 'inline-block';
    if (!hasGreeted) {
      hasGreeted = true;
      addBotMessage("Welcome! ☕ I'm your Highland Assistant. Ask me about our menu, hours, delivery, or say something like \"I want a strong coffee\" for a recommendation.");
    }
    chatInput.focus();
  }

  function closeChat() {
    chatWindow.classList.remove('open');
    iconOpen.style.display = 'inline-block';
    iconClose.style.display = 'none';
  }

  toggleBtn.addEventListener('click', () => {
    chatWindow.classList.contains('open') ? closeChat() : openChat();
  });
  minimizeBtn.addEventListener('click', closeChat);

  // ---- Render a message bubble ----
  function addUserMessage(text) {
    const div = document.createElement('div');
    div.className = 'msg user';
    div.textContent = text;
    chatBody.appendChild(div);
    scrollToBottom();
  }

  function addBotMessage(text) {
    const div = document.createElement('div');
    div.className = 'msg bot';
    div.textContent = text;
    chatBody.appendChild(div);
    scrollToBottom();
  }

  function showTyping() {
    const el = document.createElement('div');
    el.className = 'typing-indicator';
    el.id = 'typingIndicator';
    el.innerHTML = '<span></span><span></span><span></span>';
    chatBody.appendChild(el);
    scrollToBottom();
  }

  function hideTyping() {
    const el = document.getElementById('typingIndicator');
    if (el) el.remove();
  }

  function scrollToBottom() {
    chatBody.scrollTop = chatBody.scrollHeight;
  }

  // ---- Send message to chatbot_api.php ----
  async function sendMessage(text) {
    addUserMessage(text);
    showTyping();

    try {
      const res = await fetch('chatbot_api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ message: text }),
      });

      if (!res.ok) throw new Error('Network response was not ok');
      const data = await res.json();

      // small delay so the typing indicator feels natural
      setTimeout(() => {
        hideTyping();
        addBotMessage(data.reply || "Sorry, I couldn't process that.");
      }, 500 + Math.random() * 400);

    } catch (err) {
      hideTyping();
      addBotMessage('Oops — I had trouble connecting. Please check your connection and try again.');
      console.error('Chatbot error:', err);
    }
  }

  // ---- Form submit (typed message) ----
  chatForm.addEventListener('submit', (e) => {
    e.preventDefault();
    const text = chatInput.value.trim();
    if (!text) return;
    chatInput.value = '';
    sendMessage(text);
  });

  // ---- Quick option buttons ----
  quickOptions.addEventListener('click', (e) => {
    const btn = e.target.closest('.quick-btn');
    if (!btn) return;
    sendMessage(btn.dataset.msg);
  });
});
