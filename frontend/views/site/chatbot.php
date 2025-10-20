<?php
/** @var yii\web\View $this */
$this->title = 'FaidiHR Chatbot';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= $this->title ?></title>
  <style>
    body {
      font-family: 'Segoe UI', Arial, sans-serif;
      background: #fffaf3;
      margin: 0;
      padding: 0;
      height: 100vh;
      overflow-x: hidden;
    }

    /* === Floating chat bubble === */
    #chat-toggle {
      position: fixed;
      bottom: 25px;
      right: 25px;
      background: linear-gradient(135deg, #ff6600, #e65c00);
      color: white;
      border: none;
      border-radius: 50%;
      width: 60px;
      height: 60px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.3);
      cursor: pointer;
      font-size: 26px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.3s ease;
      z-index: 1000;
    }
    #chat-toggle:hover { transform: scale(1.1); }

    /* === Chat window === */
    #chat-container {
      position: fixed;
      bottom: 90px;
      right: 25px;
      width: 360px;
      max-height: 520px;
      background: #ffffff;
      border-radius: 16px;
      box-shadow: 0 4px 18px rgba(0,0,0,0.25);
      display: none;
      flex-direction: column;
      overflow: hidden;
      border: 1px solid #ffb366;
      z-index: 999;
    }

    #chat-header {
      background: linear-gradient(90deg, #ff6600, #e65c00);
      color: white;
      text-align: center;
      padding: 14px;
      font-weight: bold;
      font-size: 16px;
      position: relative;
    }

    #close-btn {
      position: absolute;
      right: 10px;
      top: 6px;
      background: transparent;
      border: none;
      color: white;
      font-size: 20px;
      cursor: pointer;
    }

    #chat-messages {
      flex: 1;
      padding: 10px;
      overflow-y: auto;
      background: #fff5eb;
      display: flex;
      flex-direction: column;
      scroll-behavior: smooth;
    }

    #chat-input-area {
      display: flex;
      border-top: 1px solid #ddd;
      background: #fff;
      padding: 5px;
    }

    #chat-input {
      flex: 1;
      border: none;
      padding: 10px;
      outline: none;
      font-size: 14px;
    }

    #send-btn, #new-chat-btn {
      background: #ff6600;
      color: white;
      border: none;
      border-radius: 8px;
      margin-left: 4px;
      padding: 0 12px;
      cursor: pointer;
      transition: 0.3s;
    }
    #send-btn:hover, #new-chat-btn:hover { background: #ff751a; }

    /* === Message Bubbles === */
    .message {
      margin: 6px 0;
      max-width: 80%;
      padding: 10px 14px;
      border-radius: 20px;
      line-height: 1.5;
      animation: fadeIn 0.3s ease-in-out;
      word-wrap: break-word;
      display: inline-block;
    }

    .you {
      background: #ff6600;
      color: #fff;
      align-self: flex-end;
      border-bottom-right-radius: 0;
    }

    .bot {
      background: #f0f0f0;
      color: #333;
      align-self: flex-start;
      border-bottom-left-radius: 0;
    }

    /* Inner content for structured bot replies */
    .bot-content {
      background: #f9fafb;
      border-radius: 12px;
      padding: 12px;
      color: #2c3e50;
      line-height: 1.6;
      max-width: 360px;
    }

    .bot-content .topic {
      font-weight: 600;
      margin-bottom: 6px;
      display: block;
    }

    .bot-content a {
      color: #ff6600;
      text-decoration: none;
      font-weight: 600;
    }

    .bot-content a:hover {
      text-decoration: underline;
    }

    /* Quick actions */
    #quick-actions {
      display: flex;
      flex-wrap: wrap;
      gap: 6px;
      margin: 6px 0;
      padding: 6px 0;
    }
    #quick-actions button {
      background: #ffe0cc;
      border: 1px solid #ff6600;
      border-radius: 8px;
      padding: 6px 10px;
      cursor: pointer;
      font-size: 13px;
    }
    #quick-actions button:hover {
      background: #ff6600;
      color: white;
    }

    @keyframes fadeIn {
      from {opacity: 0; transform: translateY(10px);}
      to {opacity: 1; transform: translateY(0);}
    }

    ol.roman {
      list-style-type: upper-roman;
      margin: 8px 0;
      padding-left: 25px;
    }
    ol.roman li {
      margin: 4px 0;
    }

    /* Greeting card */
    .greeting-card {
      display: flex;
      align-items: center;
      gap: 10px;
      background: linear-gradient(135deg, #d9f7ff, #e8faff);
      border-radius: 16px;
      padding: 12px 18px;
      box-shadow: 0 3px 6px rgba(0,0,0,0.1);
      animation: fadeIn 0.6s ease-in-out;
      max-width: 80%;
      font-size: 1rem;
      color: #333;
    }

    .wave {
      font-size: 1.6rem;
      animation: wave-hand 1.2s infinite ease-in-out;
    }

    @keyframes wave-hand {
      0% { transform: rotate(0deg); }
      25% { transform: rotate(15deg); }
      50% { transform: rotate(-10deg); }
      75% { transform: rotate(15deg); }
      100% { transform: rotate(0deg); }
    }

    /* small helpers */
    .followup-buttons {
  display: flex;
  gap: 10px;
  margin-top: 8px;
}

.followup-buttons button {
  padding: 6px 14px;
  border: none;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 2px 6px rgba(0,0,0,0.15);
  font-size: 0.9rem;
}

.btn-yes {
  background: #4caf50; /* green */
  color: white;
}

.btn-yes:hover {
  background: #45a049;
  transform: scale(1.05);
}

.btn-no {
  background: #f44336; /* red */
  color: white;
}

.btn-no:hover {
  background: #e53935;
  transform: scale(1.05);
}
    }
  </style>
</head>
<body>

<!-- Floating Chat Icon -->
<button id="chat-toggle">💬</button>

<!-- Chat Window -->
<div id="chat-container" aria-hidden="true">
  <div id="chat-header">
    FaidiHR Chatbot
    <button id="close-btn" aria-label="Close chat">&times;</button>
  </div>

  <div id="chat-messages" role="log" aria-live="polite">
    <!-- initial friendly message as a bot content -->
    <div class="message bot">
      <div class="bot-content">
        <span class="topic">WELCOME</span>
        Hi! I'm FaidiHR Assistant.Type a question or pick a quick action to get started.
      </div>
    </div>
  </div>

  <div id="chat-input-area">
    <input id="chat-input" type="text" placeholder="Type your message..." aria-label="Chat input" />
    <button id="send-btn" aria-label="Send">Send</button>
    <button id="new-chat-btn" aria-label="New chat">🆕</button>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const API_URL = "http://127.0.0.1:8000";
  const chatToggle = document.getElementById("chat-toggle");
  const chatContainer = document.getElementById("chat-container");
  const closeBtn = document.getElementById("close-btn");
  const messagesDiv = document.getElementById("chat-messages");
  const input = document.getElementById("chat-input");
  const sendBtn = document.getElementById("send-btn");
  const newChatBtn = document.getElementById("new-chat-btn");

  // --- State ---
  let lastWasFollowUp = false;
  let lastUserSaidNo = false;

  const noVariants = ["no", "nope", "not now", "nah", "cancel", "stop"];

  function toRoman(num) {
    const roman = ["I","II","III","IV","V","VI","VII","VIII","IX","X"];
    return roman[num-1] || num;
  }

  function appendUserMessage(text) {
    const wrapper = document.createElement("div");
    wrapper.className = "message you";
    wrapper.textContent = text;
    messagesDiv.appendChild(wrapper);
    messagesDiv.scrollTop = messagesDiv.scrollHeight;
  }

  function appendBotStructured(topic, content, url = "") {
    const wrapper = document.createElement("div");
    wrapper.className = "message bot";
    const inner = document.createElement("div");
    inner.className = "bot-content";

    const topicSpan = document.createElement("span");
    topicSpan.className = "topic";
    topicSpan.textContent = (topic || "RESPONSE").toUpperCase();
    inner.appendChild(topicSpan);

    const contentDiv = document.createElement("div");
    contentDiv.innerHTML = content || "";
    inner.appendChild(contentDiv);

    if (url) {
      const urlDiv = document.createElement("div");
      urlDiv.style.marginTop = "10px";
      const a = document.createElement("a");
      a.href = url;
      a.target = "_blank";
      a.rel = "noopener noreferrer";
      a.textContent = "🔗 Click here to get started";
      urlDiv.appendChild(a);
      inner.appendChild(urlDiv);
    }

    wrapper.appendChild(inner);
    messagesDiv.appendChild(wrapper);
    messagesDiv.scrollTop = messagesDiv.scrollHeight;
  }

  async function loadQuickActions() {
    let container = document.getElementById("quick-actions");
    if (container) container.remove();

    container = document.createElement("div");
    container.id = "quick-actions";
    messagesDiv.appendChild(container);

    try {
      const res = await fetch(`${API_URL}/quick_actions`);
      const data = await res.json();
      if (Array.isArray(data.actions)) {
        data.actions.forEach(action => {
          const btn = document.createElement("button");
          btn.textContent = action;
          btn.addEventListener("click", () => handleSend(action));
          container.appendChild(btn);
        });
      }
    } catch (err) {
      console.error("Error loading quick actions:", err);
    }
    messagesDiv.scrollTop = messagesDiv.scrollHeight;
  }

  async function newChat(session_id = "default") {
    try {
      await fetch(`${API_URL}/new_chat?session_id=${encodeURIComponent(session_id)}`);
    } catch (err) {
      console.warn("new_chat failed", err);
    }
    messagesDiv.innerHTML = "";
    appendBotStructured("WELCOME", "🆕 New chat started. How can I assist you?");
    lastWasFollowUp = false;
    lastUserSaidNo = false;
    loadQuickActions();
  }

  async function handleSend(rawText) {
    const text = (typeof rawText === "string") ? rawText.trim() : input.value.trim();
    if (!text) return;

    // Reset flags if user types a new query that isn't "yes" or "no"
    const lowerText = text.toLowerCase();
    if (!["yes","no"].includes(lowerText)) {
      lastWasFollowUp = false;
      lastUserSaidNo = false;
    }

    appendUserMessage(text);
    input.value = "";

    const typingEl = document.createElement("div");
    typingEl.className = "message bot";
    typingEl.innerHTML = `<div class="bot-content"><i>Typing...</i></div>`;
    messagesDiv.appendChild(typingEl);
    messagesDiv.scrollTop = messagesDiv.scrollHeight;

    try {
      const normalized = text; // optionally call normalizeInput(text) if needed
      const res = await fetch(`${API_URL}/chat?q=${encodeURIComponent(normalized)}`);
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      const data = await res.json();
      typingEl.remove();

      if (data.topic === "GREETING") {
        appendBotStructured("GREETING", data.content);
        return;
      }

      if (data.topic && data.topic !== "NOT FOUND" && data.content) {
        // Format arrows into Roman numerals
        const points = data.content.split("→").map(p => p.trim()).filter(p => p);
        const romanContent = points.map((p,i) => `${toRoman(i+1)}. ${p}`).join("<br>");
        appendBotStructured(data.topic, romanContent, data.url || "");

        // If user just said "no", mark handled and exit
        if (noVariants.some(v => lowerText === v || lowerText.includes(v))) {
          lastWasFollowUp = true;
          lastUserSaidNo = true;
          return;
        }

        // Show follow-up only if not handled
        if (!lastWasFollowUp && !lastUserSaidNo) {
          const followUpPrompt = document.createElement("div");
          followUpPrompt.className = "message bot";
          followUpPrompt.innerHTML = `<div class="bot-content">🤖 Would you like me to help you with this?</div>`;
          messagesDiv.appendChild(followUpPrompt);

          const buttonContainer = document.createElement("div");
          buttonContainer.className = "followup-buttons";

          const yesBtn = document.createElement("button");
          yesBtn.className = "btn-yes";
          yesBtn.textContent = "👍 Yes";
          yesBtn.addEventListener("click", () => {
            followUpPrompt.remove();
            buttonContainer.remove();
            lastWasFollowUp = false;
            lastUserSaidNo = false;
            handleSend("yes");
          });

          const noBtn = document.createElement("button");
          noBtn.className = "btn-no";
          noBtn.textContent = "👎 No";
          noBtn.addEventListener("click", () => {
            followUpPrompt.remove();
            buttonContainer.remove();
            lastWasFollowUp = true;
            lastUserSaidNo = true;
            appendBotStructured("INFO", "Alright 👍 Let me know whenever you need help again!");
          });

          buttonContainer.appendChild(yesBtn);
          buttonContainer.appendChild(noBtn);
          messagesDiv.appendChild(buttonContainer);
          messagesDiv.scrollTop = messagesDiv.scrollHeight;
        }
        return; // exit after handling match & follow-up
      }

      // No match found fallback
      appendBotStructured("NOT FOUND", "Sorry, I couldn't find information related to your request. Try rephrasing or selecting a quick action.");
    } catch (error) {
      if (typingEl.parentNode) typingEl.remove();
      console.error("Chatbot error:", error);
      appendBotStructured("ERROR", "⚠️ Server not reachable or an error occurred. Please try again.");
    }
  }

  // Toggle and close
  chatToggle.addEventListener("click", () => {
    const visible = chatContainer.style.display === "flex";
    chatContainer.style.display = visible ? "none" : "flex";
    chatContainer.style.flexDirection = "column";
    chatContainer.setAttribute("aria-hidden", visible ? "true" : "false");
    if (!visible) loadQuickActions();
  });

  closeBtn.addEventListener("click", () => {
    chatContainer.style.display = "none";
    chatContainer.setAttribute("aria-hidden", "true");
  });

  sendBtn.addEventListener("click", () => handleSend());
  input.addEventListener("keypress", e => { if (e.key === "Enter") handleSend(); });
  newChatBtn.addEventListener("click", () => newChat());

  // Initial quick actions load
  loadQuickActions();
});
</script>

</body>
</html>
