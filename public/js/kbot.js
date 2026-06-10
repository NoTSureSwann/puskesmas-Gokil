/**
 * kBot Enterprise JS
 * Implements Lazy Loading, Pagination, Boyer-Moore & Sequential Search
 */

document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('toggle-kbot');
    const chatWindow = document.getElementById('kbot-chat-window');
    const closeBtn = document.getElementById('close-kbot');
    const inputField = document.getElementById('kbot-input');
    const sendBtn = document.getElementById('send-kbot');
    const messagesContainer = document.getElementById('kbot-messages');
    const loadingIndicator = document.getElementById('kbot-loading');

    let isWindowOpen = false;
    let chatPage = 1;
    let isLoading = false;
    let hasMoreHistory = true; // For dummy pagination logic

    // Algorithms
    function sequentialSearch(arr, target) {
        for (let i = 0; i < arr.length; i++) {
            if (arr[i].toLowerCase() === target.toLowerCase()) {
                return i;
            }
        }
        return -1;
    }

    function boyerMooreSearch(text, pattern) {
        let m = pattern.length;
        let n = text.length;
        if (m === 0) return 0;
        
        let badChar = new Array(256).fill(-1);
        for (let i = 0; i < m; i++) {
            badChar[pattern.charCodeAt(i)] = i;
        }

        let s = 0;
        while (s <= (n - m)) {
            let j = m - 1;
            while (j >= 0 && pattern[j].toLowerCase() === text[s + j].toLowerCase()) {
                j--;
            }
            if (j < 0) {
                return s; // Pattern found
                // s += (s + m < n) ? m - badChar[text.charCodeAt(s + m)] : 1; // for all occurrences
            } else {
                s += Math.max(1, j - badChar[text.charCodeAt(s + j)]);
            }
        }
        return -1;
    }

    // Chat UI logic
    function toggleChat() {
        isWindowOpen = !isWindowOpen;
        chatWindow.style.display = isWindowOpen ? 'flex' : 'none';
        if (isWindowOpen) {
            inputField.focus();
            scrollToBottom();
        }
    }

    toggleBtn.addEventListener('click', toggleChat);
    closeBtn.addEventListener('click', toggleChat);

    function scrollToBottom() {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    function addMessage(text, isUser = false, rawAnalysis = null) {
        const div = document.createElement('div');
        div.className = "d-flex mb-3 " + (isUser ? "justify-content-end" : "");
        
        let contentClass = isUser ? "bg-primary text-white p-3 rounded-3" : "bg-light p-3 rounded-3";
        let style = isUser ? "max-width: 85%; border-bottom-right-radius: 0;" : "max-width: 85%; border-bottom-left-radius: 0;";
        
        let contentHtml = `<div class="${contentClass}" style="${style}">${text}</div>`;
        
        if (rawAnalysis && !isUser) {
            // Enterprise Details hidden by default
            contentHtml += `
            <div class="mt-1 w-100">
                <button class="btn btn-sm btn-link text-muted p-0" onclick="this.nextElementSibling.style.display = this.nextElementSibling.style.display === 'none' ? 'block' : 'none'" style="font-size: 0.75rem;">+ Lihat Detail Analisis (Parameter 2)</button>
                <div class="bg-dark text-success p-2 rounded mt-1" style="display: none; font-family: monospace; font-size: 0.75rem; overflow-x: auto;">
                    ${JSON.stringify(rawAnalysis, null, 2)}
                </div>
            </div>`;
            
            div.style.flexDirection = 'column';
        }
        
        div.innerHTML = contentHtml;
        messagesContainer.appendChild(div);
        
        // Check if keyword requires specific local handling (Boyer-Moore example)
        if (isUser) {
            const doctors = ['Dr. Budi', 'Dr. Siti', 'Dr. Andi'];
            if (boyerMooreSearch(text, 'dokter') !== -1) {
                console.log("[Boyer-Moore] Keyword 'dokter' detected locally. Preparing recommendation.");
            }
        }

        scrollToBottom();
    }

    async function fetchResponse(message) {
        try {
            // Loading state
            sendBtn.disabled = true;
            inputField.disabled = true;
            
            // CSRF Token - required for Laravel POST
            let token = document.querySelector('meta[name="csrf-token"]');
            
            const response = await fetch('/api/kbot/analyze', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token ? token.content : ''
                },
                body: JSON.stringify({ message: message })
            });

            const data = await response.json();
            
            if (data.status === 'success') {
                // parameter_1 = Chat text response
                // parameter_2 = Raw AI analysis scores
                addMessage(data.parameter_1, false, data.parameter_2);
            } else {
                addMessage("Maaf, terjadi kesalahan saat menghubungi server AI.");
            }
        } catch (error) {
            console.error(error);
            addMessage("Koneksi kBot terputus. Mohon coba lagi.");
        } finally {
            sendBtn.disabled = false;
            inputField.disabled = false;
            inputField.focus();
        }
    }

    sendBtn.addEventListener('click', () => {
        const msg = inputField.value.trim();
        if (msg) {
            addMessage(msg, true);
            inputField.value = '';
            fetchResponse(msg);
        }
    });

    inputField.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            sendBtn.click();
        }
    });

    // Lazy Loading / Pagination for chat history
    messagesContainer.addEventListener('scroll', () => {
        if (messagesContainer.scrollTop === 0 && !isLoading && hasMoreHistory) {
            loadOlderMessages();
        }
    });

    function loadOlderMessages() {
        isLoading = true;
        loadingIndicator.style.display = 'block';
        
        // Simulate fetching page N from server
        setTimeout(() => {
            chatPage++;
            const oldScrollHeight = messagesContainer.scrollHeight;
            
            const div = document.createElement('div');
            div.className = "d-flex mb-3";
            div.innerHTML = `<div class="bg-light p-3 rounded-3 text-muted" style="max-width: 85%; border-bottom-left-radius: 0; font-style: italic;">[Riwayat Halaman ${chatPage}] - Pesan terdahulu dimuat (Lazy Load)</div>`;
            
            // Insert after loading indicator but before current top messages
            messagesContainer.insertBefore(div, loadingIndicator.nextSibling);
            
            // Maintain scroll position so it doesn't jump
            messagesContainer.scrollTop = messagesContainer.scrollHeight - oldScrollHeight;
            
            isLoading = false;
            loadingIndicator.style.display = 'none';
            
            if (chatPage >= 3) {
                hasMoreHistory = false; // Stop after 3 mock pages
            }
        }, 1000);
    }
});
