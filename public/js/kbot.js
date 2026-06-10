/**
 * kBot Enterprise JS v2.0
 * Modern UI Integration with Machine Learning Backend
 */

document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('toggle-kbot');
    const chatWindow = document.getElementById('kbot-chat-window');
    const closeBtn = document.getElementById('close-kbot');
    const inputField = document.getElementById('kbot-input');
    const sendBtn = document.getElementById('send-kbot');
    const messagesContainer = document.getElementById('kbot-messages');

    let isWindowOpen = false;

    // Chat UI logic
    function toggleChat() {
        isWindowOpen = !isWindowOpen;
        if (isWindowOpen) {
            chatWindow.classList.add('open');
            inputField.focus();
            scrollToBottom();
        } else {
            chatWindow.classList.remove('open');
        }
    }

    toggleBtn.addEventListener('click', toggleChat);
    closeBtn.addEventListener('click', toggleChat);

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
        
        const qData = parameter_2.statistical_quartiles;
        const nlpData = parameter_2.nlp_classification;
        
        const badgeClass = qData.Status === 'Kritis' ? 'kritis' : 
                           qData.Status === 'Sedang' ? 'sedang' : '';

        const div = document.createElement('div');
        div.className = 'diagnostic-card';
        
        // Setup internal HTML for diagnostic card
        let html = `
            <div class="diagnostic-title">
                <i class="fa-solid fa-microscope"></i> Analisis AI kBot
            </div>
            <hr>
            <div><strong>Triase CDC:</strong> <span class="diagnostic-badge ${badgeClass}">${qData.cdc_triage}</span></div>
            <div><strong>Skor Keparahan:</strong> ${qData.raw_score}/10.0 (${qData.Status})</div>
            <div><strong>Rekomendasi Aksi:</strong> ${qData.action}</div>
        `;

        if (nlpData) {
            html += `
            <hr>
            <div><strong>Klasifikasi Poli:</strong> ${nlpData.poli_name} (Confidence: ${(nlpData.confidence * 100).toFixed(1)}%)</div>
            <div><strong>Rekomendasi Dokter:</strong> ${nlpData.doctor}</div>
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
        
        // Add event listeners for feedback buttons
        const btnUp = div.querySelector('.btn-up');
        const btnDown = div.querySelector('.btn-down');
        
        const handleFeedback = async (rating, btnClicked, btnOther, activeClass) => {
            btnClicked.classList.add(activeClass);
            btnOther.classList.remove(activeClass === 'active-up' ? 'active-down' : 'active-up');
            
            try {
                await fetch('http://localhost:5000/kbot/feedback', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
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
            sendBtn.disabled = true;
            inputField.disabled = true;
            
            addTypingIndicator();
            
            const response = await fetch('http://localhost:5000/kbot/analyze', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ message: message })
            });

            const data = await response.json();
            removeTypingIndicator();
            
            if (data.status === 'success') {
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
});
