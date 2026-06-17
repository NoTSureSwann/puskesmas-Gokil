/**
 * kBot Enterprise JS v2.0
 * Modern UI Integration with Machine Learning Backend
 */

function initKbot() {
    const toggleBtn = document.getElementById('toggle-kbot');
    const chatWindow = document.getElementById('kbot-chat-window');
    const closeBtn = document.getElementById('close-kbot');
    const inputField = document.getElementById('kbot-input');
    const sendBtn = document.getElementById('send-kbot');
    const messagesContainer = document.getElementById('kbot-messages');

    if (!toggleBtn) return; // Exit if kBot widget is not on the page

    // Get CSRF Token for Laravel POST requests
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

    let isWindowOpen = false;
    let chatHistory = [];

    // Chat UI logic
    function toggleChat() {
        isWindowOpen = !isWindowOpen;
        if (isWindowOpen) {
            chatWindow.classList.add('open');
            if (inputField) inputField.focus();
            scrollToBottom();
        } else {
            chatWindow.classList.remove('open');
        }
    }

    toggleBtn.addEventListener('click', toggleChat);
    if (closeBtn) closeBtn.addEventListener('click', toggleChat);

    function scrollToBottom() {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    function addMessage(text, isUser = false) {
        const div = document.createElement('div');
        div.className = `message-bubble ${isUser ? 'message-user' : 'message-bot'}`;
        div.innerHTML = text;
        messagesContainer.appendChild(div);
        scrollToBottom();
        return div;
    }

    function addTypingIndicator() {
        const div = document.createElement('div');
        div.className = 'typing-indicator';
        div.id = 'typing-indicator';
        div.innerHTML = `
            <div class="typing-dot"></div>
            <div class="typing-dot"></div>
            <div class="typing-dot"></div>
        `;
        messagesContainer.appendChild(div);
        scrollToBottom();
        return div;
    }

    function removeTypingIndicator() {
        const indicator = document.getElementById('typing-indicator');
        if (indicator) {
            indicator.remove();
        }
    }

    function addDiagnosticCard(parameter_2, message_id, original_input) {
        if (!parameter_2 || !parameter_2.statistical_quartiles) return;
        
        const metrics = parameter_2.metrics || {};
        if (metrics.state && (metrics.state === 1 || metrics.state === 2)) {
            return; // Do not show diagnostic card for sapaan or keluhan ambigu
        }
        
        const qData = parameter_2.statistical_quartiles || {};
        const nlpData = parameter_2.nlp_classification || {};
        
        const triase = metrics.triase_tag || qData.cdc_triage || 'Unknown';
        const skor = metrics.skor || `${qData.raw_score}/10.0 (${qData.Status})`;
        const aksi = metrics.rekomendasi_aksi || qData.action || '';
        const poli = metrics.klasifikasi_poli || nlpData.poli_name || '';
        const dokter = metrics.rekomendasi_dokter || nlpData.doctor || '';
        
        const badgeClass = triase.includes('RED') ? 'kritis' : 
                           triase.includes('YELLOW') ? 'sedang' : 'aman';

        const div = document.createElement('div');
        div.className = 'diagnostic-card';
        
        // Setup internal HTML for diagnostic card
        let html = `
            <div class="diagnostic-title">
                <i class="fa-solid fa-microscope"></i> Analisis AI kBot
            </div>
            <hr>
            <div><strong>Triase CDC:</strong> <span class="diagnostic-badge ${badgeClass}">${triase}</span></div>
            <div><strong>Skor Keparahan:</strong> ${skor}</div>
            <div><strong>Rekomendasi Aksi:</strong> ${aksi}</div>
        `;

        if (poli) {
            html += `
            <hr>
            <div><strong>Klasifikasi Poli:</strong> ${poli}</div>
            <div><strong>Rekomendasi Dokter:</strong> ${dokter}</div>
            `;
        }

        if (metrics.tips_kesehatan || metrics.rekomendasi_tips_kesehatan) {
            html += `
            <hr>
            <div><strong>Tips Kesehatan:</strong> ${metrics.tips_kesehatan || metrics.rekomendasi_tips_kesehatan}</div>
            <div><strong>Makanan & Buah:</strong> ${metrics.makanan_buah || metrics.rekomendasi_makanan_buah}</div>
            <div><strong>Pola Hidup Sehat:</strong> ${metrics.pola_hidup || metrics.rekomendasi_hidup_sehat}</div>
            `;
        }

        // Add Booking Button if Poli is identified and Triage is not RED
        if (poli && !triase.includes('RED')) {
            html += `
            <hr>
            <button class="btn btn-sm btn-primary w-100 mt-2 kbot-book-btn" data-poli="${poli}" data-keluhan="${original_input}">
                <i class="fa-solid fa-calendar-check"></i> Daftar ke ${poli} Sekarang
            </button>
            `;
        }

        // Add feedback buttons
        html += `
            <div class="feedback-container" id="feedback-${message_id}">
                <button class="feedback-btn btn-up" data-rating="1" title="Respons Akurat"><i class="fa-solid fa-thumbs-up"></i></button>
                <button class="feedback-btn btn-down" data-rating="0" title="Respons Keliru"><i class="fa-solid fa-thumbs-down"></i></button>
            </div>
        `;

        div.innerHTML = html;
        messagesContainer.appendChild(div);

        // Attach event listener for Booking Button
        const bookBtn = div.querySelector('.kbot-book-btn');
        if (bookBtn) {
            bookBtn.addEventListener('click', async () => {
                bookBtn.disabled = true;
                bookBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memproses...';
                
                try {
                    const res = await fetch('/api/kbot/book', {
                        method: 'POST',
                        headers: { 
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            poli_name: bookBtn.dataset.poli,
                            keluhan: bookBtn.dataset.keluhan
                        })
                    });
                    const resData = await res.json();
                    
                    if (resData.status === 'success') {
                        bookBtn.classList.remove('btn-primary');
                        bookBtn.classList.add('btn-success');
                        bookBtn.innerHTML = '<i class="fa-solid fa-check"></i> ' + resData.message;
                        
                        // Optionally redirect after 2 seconds
                        if (resData.redirect) {
                            setTimeout(() => { window.location.href = resData.redirect; }, 2000);
                        }
                    } else {
                        bookBtn.classList.remove('btn-primary');
                        bookBtn.classList.add('btn-danger');
                        bookBtn.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> ' + resData.message;
                    }
                } catch (e) {
                    bookBtn.disabled = false;
                    bookBtn.innerHTML = '<i class="fa-solid fa-calendar-check"></i> Gagal, Coba Lagi';
                }
            });
        }
        
        // Add event listeners for feedback buttons
        const btnUp = div.querySelector('.btn-up');
        const btnDown = div.querySelector('.btn-down');
        
        const handleFeedback = async (rating, btnClicked, btnOther, activeClass) => {
            btnClicked.classList.add(activeClass);
            btnOther.classList.remove(activeClass === 'active-up' ? 'active-down' : 'active-up');
            
            try {
                await fetch('/api/kbot/feedback', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        message_id: message_id,
                        rating: rating,
                        original_input: original_input
                    })
                });
            } catch (e) {
                console.error('Failed to send feedback', e);
            }
        };

        btnUp.addEventListener('click', () => handleFeedback(1, btnUp, btnDown, 'active-up'));
        btnDown.addEventListener('click', () => handleFeedback(0, btnDown, btnUp, 'active-down'));

        scrollToBottom();
    }

    async function fetchResponse(message) {
        try {
            if (sendBtn) sendBtn.disabled = true;
            if (inputField) inputField.disabled = true;
            
            addTypingIndicator();
            
            const response = await fetch('/api/kbot/analyze', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ message: message, history: chatHistory })
            });

            const data = await response.json();
            removeTypingIndicator();
            
            if (data.status === 'success') {
                chatHistory.push({ role: 'user', content: message });
                chatHistory.push({ role: 'assistant', content: data.parameter_1 });
                if (chatHistory.length > 10) chatHistory = chatHistory.slice(-10);

                addMessage(data.parameter_1, false);
                addDiagnosticCard(data.parameter_2, data.message_id, message);
            } else {
                addMessage("Maaf, terjadi kesalahan saat menghubungi server AI.");
            }
        } catch (error) {
            removeTypingIndicator();
            console.error(error);
            addMessage("Koneksi kBot terputus. Mohon coba lagi.");
        } finally {
            if (sendBtn) sendBtn.disabled = false;
            if (inputField) {
                inputField.disabled = false;
                inputField.focus();
            }
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
}

// Run on DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initKbot);
} else {
    initKbot();
}
