
const chatBox = document.getElementById('chat-box');
const chatHeader = document.getElementById('chat-header');
const sendBtn = document.getElementById('send-btn');
const messageInput = document.getElementById('message');
const chatItemsContainer = document.getElementById('chat-items');
const newChatBtn = document.getElementById('new-chat-btn');
const searchChat = document.getElementById('search-chat');

let chatCounter = 2; 
let activeChatId = 'chat-1'; 
let chatMessages = {}; 
let chatNames = {}; 


function loadChatsFromStorage() {
    const savedMessages = localStorage.getItem('chatMessages');
    const savedNames = localStorage.getItem('chatNames');

    if (savedMessages && savedNames) {
        chatMessages = JSON.parse(savedMessages);
        chatNames = JSON.parse(savedNames);

        
        for (const chatId in chatNames) {
            addChatToList(chatId, chatNames[chatId]);
        }

        
        const firstChatId = Object.keys(chatNames)[0];
        if (firstChatId) {
            openChat(firstChatId);
        }
    } else {
        
        chatMessages = { 'chat-1': [] };
        chatNames = { 'chat-1': 'Chat 1' };
        addChatToList('chat-1', 'Chat 1');
        openChat('chat-1');
    }
}


function saveChatsToStorage() {
    localStorage.setItem('chatMessages', JSON.stringify(chatMessages));
    localStorage.setItem('chatNames', JSON.stringify(chatNames));
}


function addChatToList(chatId, chatName) {
    const newChat = document.createElement('div');
    newChat.classList.add('chat-item');
    newChat.textContent = chatName;
    newChat.id = chatId;
    newChat.setAttribute('data-chat-id', chatId);
    chatItemsContainer.appendChild(newChat);

    
    newChat.addEventListener('click', () => openChat(chatId));
}


newChatBtn.addEventListener('click', () => {
    const chatName = prompt("Ingresa el nombre del nuevo chat:");
    if (chatName) {
        const newChatId = `chat-${chatCounter}`;
        addChatToList(newChatId, chatName);

        
        chatMessages[newChatId] = [];
        chatNames[newChatId] = chatName;
        chatCounter++;

        
        saveChatsToStorage();
    }
});


function openChat(chatId) {
    activeChatId = chatId; 
    chatBox.innerHTML = '';
    chatHeader.textContent = chatNames[chatId]; 

    
    chatMessages[chatId].forEach(message => {
        const messageElement = document.createElement('div');
        messageElement.classList.add('message');
        messageElement.textContent = message;
        chatBox.appendChild(messageElement);
    });
}


sendBtn.addEventListener('click', () => {
    const message = messageInput.value.trim();
    if (message) {
        const messageElement = document.createElement('div');
        messageElement.classList.add('message');
        messageElement.textContent = message;
        chatBox.appendChild(messageElement);

       
        chatMessages[activeChatId].push(message);

       
        saveChatsToStorage();

        messageInput.value = '';
        chatBox.scrollTop = chatBox.scrollHeight; 
    }
});


messageInput.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') {
        sendBtn.click();
    }
});


loadChatsFromStorage();


searchChat.addEventListener('input', function () {
    const filter = searchChat.value.toLowerCase();
    const chatItems = document.querySelectorAll('.chat-item');

    chatItems.forEach(item => {
        const chatName = item.textContent.toLowerCase();
        if (chatName.includes(filter)) {
            item.style.display = '';
        } else {
            item.style.display = 'none'; 
        }
    });
});
