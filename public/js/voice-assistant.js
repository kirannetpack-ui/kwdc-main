// public/js/voice-assistant.js
document.addEventListener('DOMContentLoaded', function() {
    // ===== DOM Elements =====
    const launchBtn = document.getElementById('voiceLaunchBtn');
    const chatWindow = document.getElementById('voiceChatWindow');
    const closeBtn = document.getElementById('voiceCloseBtn');
    const toggleBtn = document.getElementById('voiceToggleBtn');
    const statusEl = document.getElementById('voiceStatus');
    const messagesEl = document.getElementById('voiceMessages');
    const langSelect = document.getElementById('voiceLangSelect');
    const textInput = document.getElementById('voiceTextInput');
    const sendBtn = document.getElementById('voiceSendBtn');
    const clearBtn = document.getElementById('assistantClearBtn');

    let currentLanguage = 'en'; // default
    let isListening = false;
    let conversationActive = false;
    let heardSpeech = false;
    let lastRecognitionError = null;
    let recognitionStartedAt = 0;
    let lastAssistantNotice = '';
    const userScope = document.querySelector('meta[name="user-id"]')?.content || 'guest';
    const historyKey = 'kwdcAssistantHistory:' + userScope;
    const openKey = 'kwdcAssistantOpen';
    const contextKey = 'kwdcAssistantContext:' + userScope;
    const maxHistoryItems = 60;

    function readHistory() {
        try {
            const raw = JSON.parse(localStorage.getItem(historyKey) || '[]');
            return raw.filter(item => {
                if (!item?.text || !item?.sender) return false;
                const t = item.text.toLowerCase();
                // Filter out any legacy error notices or connection complaints
                if (t.includes('browser speech service') || t.includes('microphone permission is blocked') || t.includes('speech recognition is blocked')) {
                    return false;
                }
                return true;
            });
        } catch (error) {
            console.warn('Assistant history could not be read.', error);
            return [];
        }
    }

    function writeHistory(items) {
        try {
            localStorage.setItem(historyKey, JSON.stringify(items.slice(-maxHistoryItems)));
        } catch (error) {
            console.warn('Assistant history could not be saved.', error);
        }
    }

    function clearHistory() {
        try {
            localStorage.removeItem(historyKey);
            localStorage.removeItem(contextKey);
        } catch (error) {
            console.warn('Assistant history could not be cleared.', error);
        }
    }

    function rememberMessage(text, sender) {
        const items = readHistory();
        items.push({ text, sender, at: new Date().toISOString() });
        writeHistory(items);
    }

    function setAssistantOpen(open) {
        try {
            sessionStorage.setItem(openKey, open ? '1' : '0');
        } catch (error) {
            console.warn('Assistant open state could not be saved.', error);
        }
    }

    function shouldRestoreOpen() {
        try {
            return sessionStorage.getItem(openKey) === '1';
        } catch (error) {
            return false;
        }
    }

    function readAssistantContext() {
        try {
            return JSON.parse(sessionStorage.getItem(contextKey) || '{}');
        } catch (error) {
            console.warn('Assistant context could not be read.', error);
            return {};
        }
    }

    function writeAssistantContext(nextContext) {
        try {
            const current = readAssistantContext();
            sessionStorage.setItem(contextKey, JSON.stringify({
                ...current,
                ...nextContext,
                data: {
                    ...(current.data || {}),
                    ...(nextContext.data || {}),
                },
                updatedAt: new Date().toISOString(),
            }));
        } catch (error) {
            console.warn('Assistant context could not be saved.', error);
        }
    }

    // ===== Language Selector & Toggle =====
    const langToggle = document.getElementById('voiceLangToggle');
    const langOptEn = document.getElementById('langOptEn');
    const langOptNp = document.getElementById('langOptNp');

    function setLanguage(lang) {
        currentLanguage = lang;
        if (langSelect) langSelect.value = lang;
        if (langOptEn) langOptEn.classList.toggle('active', lang === 'en');
        if (langOptNp) langOptNp.classList.toggle('active', lang === 'np');
        const msg = lang === 'en' 
            ? 'Language changed to English' 
            : 'भाषा नेपालीमा परिवर्तन भयो';
        addMessage(msg, 'assistant');
    }

    if (langToggle) {
        langToggle.addEventListener('click', function(e) {
            e.preventDefault();
            const nextLang = currentLanguage === 'en' ? 'np' : 'en';
            setLanguage(nextLang);
        });
    }

    if (langSelect) {
        langSelect.addEventListener('change', function() {
            setLanguage(this.value);
        });
    }

    // ===== Safety Checks =====
    if (!launchBtn) {
        console.error('❌ Voice launch button not found!');
        return;
    }
    if (!chatWindow) {
        console.error('❌ Voice chat window not found!');
        return;
    }

    const initialMessagesHtml = messagesEl.innerHTML;

    function setListeningUi(active, message = null) {
        isListening = active;
        window.isListening = active;
        if (toggleBtn) {
            toggleBtn.innerHTML = active
                ? '<i class="fas fa-stop"></i>'
                : '<i class="fas fa-microphone"></i>';
            toggleBtn.classList.toggle('listening', active);
            toggleBtn.title = active ? 'Stop listening' : 'Voice Input';
        }
        if (statusEl) {
            statusEl.textContent = message || (active ? 'Listening... speak now' : 'Type a request or tap mic to speak');
        }
    }

    // ===== OPEN / CLOSE HELPERS =====
    window.openKwdcAssistant = function(promptText) {
        if (chatWindow) {
            chatWindow.style.display = 'flex';
            setAssistantOpen(true);
            if (textInput) {
                if (promptText) textInput.value = promptText;
                setTimeout(() => textInput.focus(), 50);
            }
        }
    };

    // Hotkey: Ctrl + K or Cmd + K to summon AI Copilot
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
            e.preventDefault();
            window.openKwdcAssistant();
        }
    });

    // ===== TOGGLE CHAT WINDOW =====
    launchBtn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const isHidden = chatWindow.style.display === 'none' || chatWindow.style.display === '';
        if (isHidden) {
            window.openKwdcAssistant();
        } else {
            chatWindow.style.display = 'none';
            setAssistantOpen(false);
        }
    });

    // ===== CLOSE BUTTON =====
    if (closeBtn) {
        closeBtn.addEventListener('click', function(e) {
            if (e) e.stopPropagation();
            chatWindow.style.display = 'none';
            if (isListening) {
                if (typeof stopMediaRecorderVoice === 'function') stopMediaRecorderVoice();
                try { recognition?.stop(); } catch(e) {}
                setListeningUi(false);
            }
            stopSpeaking();
            setAssistantOpen(false);
        });
    }

    // Close on Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && chatWindow.style.display === 'flex') {
            closeBtn ? closeBtn.click() : (chatWindow.style.display = 'none');
        }
    });

    // Close on click outside (ignoring clicks on launch triggers)
    document.addEventListener('click', function(e) {
        if (e.target.closest('.kwdc-topbar-search') || e.target.closest('#kwdcTopbarSearchBtn') || e.target.closest('#voiceLaunchBtn') || e.target.closest('.kwdc-assistant-launch')) {
            return;
        }
        if (chatWindow.style.display === 'flex') {
            const container = document.getElementById('voice-assistant-container');
            if (container && !container.contains(e.target)) {
                chatWindow.style.display = 'none';
                if (isListening) {
                    if (typeof stopMediaRecorderVoice === 'function') stopMediaRecorderVoice();
                    try { recognition?.stop(); } catch(e) {}
                    setListeningUi(false);
                }
                stopSpeaking();
                setAssistantOpen(false);
            }
        }
    });

    // ===== SPEECH RECOGNITION =====
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    let recognition = null;

    if (!SpeechRecognition) {
        statusEl.textContent = 'Mic not supported here. Type below.';
        toggleBtn.disabled = true;
        toggleBtn.style.opacity = '0.65';
        console.warn('SpeechRecognition not supported; typed assistant fallback enabled.');
    } else {
        recognition = new SpeechRecognition();
        recognition.continuous = false;
        recognition.interimResults = false;
        recognition.lang = 'en-US';
        recognition.maxAlternatives = 1;

        window.recognition = recognition;
    }
    window.isListening = false;

    function stopSpeaking() {
        if (window.speechSynthesis) {
            window.speechSynthesis.cancel();
        }
    }

    // ===== ADD MESSAGE =====
    function renderMessage(text, sender) {
        if (!messagesEl || !text) return;
        const div = document.createElement('div');
        div.className = sender === 'user'
            ? 'kwdc-assistant-msg user-msg'
            : 'kwdc-assistant-msg assistant-msg';
        const isNepali = currentLanguage === 'np';
        const bubble = document.createElement('div');
        bubble.className = 'kwdc-assistant-bubble';
        bubble.textContent = text;
        if (isNepali) {
            bubble.style.fontSize = '15px';
        }
        div.appendChild(bubble);
        messagesEl.appendChild(div);
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    // ===== Speech Synthesis (Text-to-Speech) =====
    let isSpeechEnabled = localStorage.getItem('kwdcAssistantSpeech') !== 'false';
    const speechToggleBtn = document.getElementById('voiceSpeechToggle');

    function updateSpeechToggleUi() {
        if (!speechToggleBtn) return;
        speechToggleBtn.innerHTML = isSpeechEnabled 
            ? '<i class="fas fa-volume-up" style="color:#f97316;"></i>' 
            : '<i class="fas fa-volume-xmark" style="color:#64748b;"></i>';
    }

    if (speechToggleBtn) {
        updateSpeechToggleUi();
        speechToggleBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            isSpeechEnabled = !isSpeechEnabled;
            localStorage.setItem('kwdcAssistantSpeech', isSpeechEnabled ? 'true' : 'false');
            updateSpeechToggleUi();
            if (!isSpeechEnabled && window.speechSynthesis) {
                window.speechSynthesis.cancel();
            }
        });
    }

    function speakAssistantText(text) {
        if (!isSpeechEnabled || !('speechSynthesis' in window)) return;
        try {
            window.speechSynthesis.cancel();
            // Clean markdown asterisks, URLs, brackets and list bullets for smooth natural voice
            const cleanText = text
                .replace(/<[^>]*>?/gm, '')
                .replace(/https?:\/\/\S+/g, '')
                .replace(/[*_#`~[\]]/g, '')
                .replace(/^[-•]\s*/gm, '')
                .trim();
            if (!cleanText || cleanText.length > 300) return;
            const utterance = new SpeechSynthesisUtterance(cleanText);
            utterance.rate = 1.0;
            utterance.pitch = 1.0;
            // Use hi-IN for Nepali phonetics as Windows/Chromium handles Devanagari accurately through it
            utterance.lang = currentLanguage === 'np' ? 'hi-IN' : 'en-US';
            window.speechSynthesis.speak(utterance);
        } catch (err) {
            console.warn('Speech synthesis not available:', err);
        }
    }

    function addMessage(text, sender) {
        renderMessage(text, sender);
        rememberMessage(text, sender);
        if (sender === 'assistant') {
            speakAssistantText(text);
        }
    }

    function restoreHistory() {
        const items = readHistory();
        if (items.length === 0) {
            return;
        }

        messagesEl.innerHTML = '';
        items.forEach(item => renderMessage(item.text, item.sender));
    }

    function resetAssistantMessages() {
        clearHistory();
        messagesEl.innerHTML = initialMessagesHtml;
        lastAssistantNotice = '';
        statusEl.textContent = 'Ready';
    }

    function addAssistantNotice(text) {
        if (!text || text === lastAssistantNotice) {
            return;
        }

        lastAssistantNotice = text;
        addMessage(text, 'assistant');
    }

    function formatAssistantResponse(data) {
        const parts = [];
        if (data.message) {
            parts.push(data.message);
        }

        if (data.summary) {
            parts.push(`Summary: ${data.summary}`);
        }

        if (Array.isArray(data.missing_fields) && data.missing_fields.length) {
            parts.push(`Still needed: ${data.missing_fields.join(', ')}`);
        }

        const fields = data.guidance?.fields_guidance;
        if (fields && typeof fields === 'object') {
            const importantFields = Object.values(fields)
                .filter(field => field?.required && !field?.value)
                .slice(0, 4)
                .map(field => `${field.label}: ${field.hint}`);

            if (importantFields.length) {
                parts.push(`What to fill next:\n${importantFields.map(item => `- ${item}`).join('\n')}`);
            }
        }

        const recommendations = data.recommendations;
        if (recommendations?.suggested_vehicle) {
            parts.push(`Suggested vehicle: ${recommendations.suggested_vehicle}`);
        }
        if (recommendations?.estimated_time) {
            parts.push(`Estimated time: ${recommendations.estimated_time}`);
        }

        return parts.filter(Boolean).join('\n\n') || 'I prepared the next step for you.';
    }

    function makeCssSafe(value) {
        if (window.CSS?.escape) {
            return CSS.escape(value);
        }

        return String(value).replace(/"/g, '\\"');
    }

    function prettifyFieldName(value) {
        return String(value || '')
            .replace(/\[[^\]]*\]/g, ' ')
            .replace(/[_-]+/g, ' ')
            .replace(/\s+/g, ' ')
            .trim()
            .replace(/\b\w/g, letter => letter.toUpperCase());
    }

    function displayFieldName(field, fallback = 'that field') {
        const name = String(field.name || field.id || '').toLowerCase();

        if (name.includes('delivery_stops') && name.includes('address')) return 'Delivery Address';
        if (name.includes('delivery_stops') && name.includes('recipient_name')) return 'Recipient Name';
        if (name.includes('delivery_stops') && name.includes('recipient_phone')) return 'Recipient Phone';
        if (name.includes('pickup_contact_person')) return 'Pickup Contact Person';
        if (name.includes('pickup_contact_phone')) return 'Pickup Contact Phone';
        if (name.includes('pickup_address')) return 'Pickup Address';
        if (name.includes('total_distance')) return 'Total Distance';
        if (name.includes('total_price')) return 'Total Price';

        const readable = field.placeholder || fieldTextParts(field).find(part => !part.includes('[')) || fallback;
        return prettifyFieldName(readable);
    }

    function normalizeFieldText(value) {
        return String(value || '')
            .toLowerCase()
            .replace(/\bchnage\b/g, 'change')
            .replace(/\bchagne\b/g, 'change')
            .replace(/\bchaneg\b/g, 'change')
            .replace(/\breplce\b/g, 'replace')
            .replace(/\bbhatapur\b/g, 'bhaktapur')
            .replace(/\bateshor\b/g, 'koteshwor')
            .replace(/\bkoteswor\b/g, 'koteshwor')
            .replace(/[_-]+/g, ' ')
            .replace(/[^a-z0-9\s]/g, ' ')
            .replace(/\b(the|a|an|field|input|box|please|current|this|page|request|form|value)\b/g, ' ')
            .replace(/\s+/g, ' ')
            .trim();
    }

    function fieldTextParts(field) {
        const id = field.id || '';
        const parts = [
            field.name,
            id,
            field.placeholder,
            field.getAttribute('aria-label'),
            field.dataset.label,
        ];

        if (id) {
            const label = document.querySelector(`label[for="${makeCssSafe(id)}"]`);
            if (label) parts.push(label.textContent);
        }

        const wrappedLabel = field.closest('label');
        if (wrappedLabel) parts.push(wrappedLabel.textContent);

        const nearbyLabel = field.closest('.form-group, .mb-3, .mb-4, .col, .col-md-6, .row')?.querySelector('label');
        if (nearbyLabel) parts.push(nearbyLabel.textContent);

        return parts.filter(Boolean).map(part => String(part).trim()).filter(Boolean);
    }

    function visibleAssistantFields() {
        return Array.from(document.querySelectorAll('input, textarea, select'))
            .filter(field => {
                const type = (field.getAttribute('type') || '').toLowerCase();
                if (field.disabled || field.readOnly) return false;
                if (['hidden', 'password', 'file', 'submit', 'button', 'reset'].includes(type)) return false;
                if (field.name && ['_token', '_method'].includes(field.name)) return false;

                const style = window.getComputedStyle(field);
                const rect = field.getBoundingClientRect();
                return style.display !== 'none' && style.visibility !== 'hidden' && rect.width > 0 && rect.height > 0;
            });
    }

    function fieldAliasesForQuery(query) {
        const normalized = normalizeFieldText(query);
        const aliases = [];

        if (/\b(contact person|contact name|pickup contact|person name)\b/.test(normalized)) {
            aliases.push('pickup contact person', 'contact person', 'contact name', 'pickup_contact_person');
        }
        if (/\b(phone|mobile|number|contact phone|contact number)\b/.test(normalized)) {
            aliases.push('pickup contact phone', 'contact phone', 'recipient phone', 'phone', 'mobile');
        }
        if (/\b(email|mail)\b/.test(normalized)) {
            aliases.push('email', 'email address');
        }
        if (/\b(pickup|from|origin|collection)\b/.test(normalized) && /\b(address|location|place)\b/.test(normalized)) {
            aliases.push('pickup address', 'from address', 'origin', 'pickup_address');
        }
        if (/\b(delivery|drop|destination|to|recipient)\b/.test(normalized) && /\b(address|location|place)\b/.test(normalized)) {
            aliases.push('delivery address', 'destination address', 'drop address', 'delivery_address');
        }
        if (/\b(recipient|receiver|customer)\b/.test(normalized) && /\b(name|person)\b/.test(normalized)) {
            aliases.push('recipient name', 'receiver name');
        }
        if (/\b(item|items|cargo|goods|description|details)\b/.test(normalized)) {
            aliases.push('items description', 'cargo description', 'description');
        }
        if (/\b(price|amount|cost|total|rate)\b/.test(normalized)) {
            aliases.push('total price', 'amount', 'price');
        }
        if (/\b(distance|km|kilometer)\b/.test(normalized)) {
            aliases.push('total distance', 'distance', 'km');
        }
        if (/\b(date|day)\b/.test(normalized)) {
            aliases.push('scheduled date', 'date');
        }
        if (/\b(time|hour)\b/.test(normalized)) {
            aliases.push('scheduled time', 'time');
        }
        if (/\b(vehicle|truck|van|bike)\b/.test(normalized)) {
            aliases.push('vehicle type', 'vehicle');
        }

        aliases.push(normalized);
        return aliases.filter(Boolean);
    }

    function scoreField(field, requestedLabel) {
        const needles = fieldAliasesForQuery(requestedLabel).map(normalizeFieldText).filter(Boolean);
        const haystackParts = fieldTextParts(field).map(normalizeFieldText).filter(Boolean);
        const haystack = haystackParts.join(' ');

        return needles.reduce((best, needle) => {
            if (!needle) return best;
            if (haystackParts.includes(needle)) return Math.max(best, 100);
            if (haystack.includes(needle)) return Math.max(best, 80);

            const words = needle.split(' ').filter(word => word.length > 1);
            const matches = words.filter(word => haystack.includes(word)).length;
            if (matches === words.length && words.length > 0) return Math.max(best, 70);
            if (matches > 0) return Math.max(best, matches * 18);
            return best;
        }, 0);
    }

    function findVisibleField(requestedLabel) {
        return visibleAssistantFields()
            .map(field => ({ field, score: scoreField(field, requestedLabel) }))
            .filter(item => item.score >= 35)
            .sort((a, b) => b.score - a.score)[0]?.field || null;
    }

    function currentFieldValue(field) {
        if (field.tagName === 'SELECT') {
            return field.selectedOptions?.[0]?.textContent || field.value || '';
        }

        return field.value || '';
    }

    function findVisibleFieldByValue(value) {
        const needle = normalizeFieldText(value);
        if (!needle) return null;

        return visibleAssistantFields()
            .map(field => {
                const rawValue = currentFieldValue(field);
                const normalizedValue = normalizeFieldText(rawValue);
                let score = 0;

                if (normalizedValue === needle) {
                    score = 100;
                } else if (normalizedValue.includes(needle)) {
                    score = 75;
                }

                return { field, score };
            })
            .filter(item => item.score > 0)
            .sort((a, b) => b.score - a.score)[0]?.field || null;
    }

    function fillAssistantField(field, value) {
        const type = (field.getAttribute('type') || '').toLowerCase();
        const cleanValue = String(value).trim();

        if (field.tagName === 'SELECT') {
            const normalizedValue = normalizeFieldText(cleanValue);
            const match = Array.from(field.options).find(option => {
                return normalizeFieldText(option.textContent) === normalizedValue
                    || normalizeFieldText(option.value) === normalizedValue
                    || normalizeFieldText(option.textContent).includes(normalizedValue);
            });
            if (match) field.value = match.value;
        } else if (type === 'checkbox' || type === 'radio') {
            field.checked = /^(yes|true|on|checked|enable|enabled|1)$/i.test(cleanValue);
        } else {
            field.value = cleanValue;
        }

        field.dispatchEvent(new Event('input', { bubbles: true }));
        field.dispatchEvent(new Event('change', { bubbles: true }));
        field.focus({ preventScroll: true });
        field.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function replaceAssistantFieldValue(field, oldValue, newValue) {
        const currentValue = currentFieldValue(field);
        const escapedOldValue = oldValue.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        const nextValue = currentValue.match(new RegExp(escapedOldValue, 'i'))
            ? currentValue.replace(new RegExp(escapedOldValue, 'ig'), newValue)
            : newValue;

        fillAssistantField(field, nextValue);
    }

    function normalizeAssistantCommand(text) {
        return String(text || '')
            .trim()
            .replace(/\s+/g, ' ')
            .replace(/\bchnage\b/ig, 'change')
            .replace(/\bchagne\b/ig, 'change')
            .replace(/\bchaneg\b/ig, 'change')
            .replace(/\breplce\b/ig, 'replace')
            .replace(/\bswtich\b/ig, 'switch');
    }

    function parseCurrentPageReplacement(text) {
        const clean = normalizeAssistantCommand(text);
        const patterns = [
            /^(?:please\s+)?instead\s+of\s+(.+?)\s+(?:put|use|make\s+it|set\s+it\s+to)\s+(.+?)$/i,
            /^(?:please\s+)?replace\s+(.+?)\s+with\s+(.+?)$/i,
            /^(?:please\s+)?(?:change|switch)\s+(.+?)\s+(?:to|into)\s+(.+?)$/i,
        ];

        for (const pattern of patterns) {
            const match = clean.match(pattern);
            if (match) {
                return {
                    oldValue: match[1].trim(),
                    newValue: match[2].trim(),
                };
            }
        }

        return null;
    }

    function parseCurrentPageFill(text) {
        const clean = normalizeAssistantCommand(text);
        const patterns = [
            /^(?:please\s+)?(?:put|set|change|fill|add|make)\s+(?:the\s+)?(.+?)\s+(?:as|to|=)\s+(.+?)(?=\s+(?:on|for|in|at)\s+.+$|$)/i,
            /^(?:please\s+)?(?:enter|type)\s+(.+?)\s+(?:in|into|for)\s+(?:the\s+)?(.+?)$/i,
        ];

        for (const pattern of patterns) {
            const match = clean.match(pattern);
            if (!match) continue;

            if (/^(?:please\s+)?(?:enter|type)\b/i.test(clean)) {
                return { label: match[2], value: match[1] };
            }

            return { label: match[1], value: match[2] };
        }

        return null;
    }

    function replaceInAssistantContext(oldValue, newValue) {
        const context = readAssistantContext();
        const data = context.data || {};
        const oldNeedle = normalizeFieldText(oldValue);
        if (!oldNeedle) return false;

        let replaced = false;
        const nextData = {};
        Object.entries(data).forEach(([key, value]) => {
            if (typeof value !== 'string') {
                nextData[key] = value;
                return;
            }

            if (normalizeFieldText(value).includes(oldNeedle)) {
                const escapedOldValue = String(oldValue).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                nextData[key] = value.match(new RegExp(escapedOldValue, 'i'))
                    ? value.replace(new RegExp(escapedOldValue, 'ig'), newValue)
                    : newValue;
                replaced = true;
            } else {
                nextData[key] = value;
            }
        });

        if (!replaced) return false;

        writeAssistantContext({ data: nextData });
        applyDataToCurrentPage(nextData);

        const params = new URLSearchParams(nextData);
        if (params.toString() && context.url && window.location.pathname === context.url) {
            window.history.replaceState({}, '', `${context.url}?${params.toString()}`);
        }

        return true;
    }

    function clickPageCommand(text) {
        const clean = normalizeAssistantCommand(text).toLowerCase();
        const commands = [
            {
                matches: ['calculate', 'price', 'estimate'],
                selectors: ['#calculateBtn', '[onclick*="calculate"]'],
                label: 'calculate price',
            },
            {
                matches: ['find driver', 'recommend driver', 'ai recommend', 'driver'],
                selectors: ['#findDriversBtn'],
                label: 'find drivers',
            },
        ];

        const command = commands.find(item => item.matches.some(match => clean.includes(match)));
        if (!command) return false;

        const button = command.selectors
            .map(selector => document.querySelector(selector))
            .find(Boolean);
        if (!button) return false;

        addMessage(text, 'user');
        lastAssistantNotice = '';
        button.click();
        addAssistantNotice(`Done. I started ${command.label} on this page.`);
        statusEl.textContent = 'Action started';
        return true;
    }

    function tryCurrentPageFill(text) {
        if (clickPageCommand(text)) {
            return true;
        }

        const replacement = parseCurrentPageReplacement(text);
        if (replacement) {
            const replacementField = findVisibleFieldByValue(replacement.oldValue);
            if (replacementField) {
                addMessage(text, 'user');
                lastAssistantNotice = '';
                replaceAssistantFieldValue(replacementField, replacement.oldValue, replacement.newValue);
                addAssistantNotice(`Done. I replaced ${replacement.oldValue} with ${replacement.newValue} in ${displayFieldName(replacementField)}. Review it before saving.`);
                statusEl.textContent = 'Updated current page';
                return true;
            }

            if (replaceInAssistantContext(replacement.oldValue, replacement.newValue)) {
                addMessage(text, 'user');
                lastAssistantNotice = '';
                addAssistantNotice(`Done. I changed ${replacement.oldValue} to ${replacement.newValue} in the assistant-filled details. Review the form before saving.`);
                statusEl.textContent = 'Updated assistant details';
                return true;
            }
        }

        const command = parseCurrentPageFill(text);
        if (!command) {
            return false;
        }

        const field = findVisibleField(command.label);
        if (!field) {
            return false;
        }

        addMessage(text, 'user');
        lastAssistantNotice = '';
        fillAssistantField(field, command.value);
        addAssistantNotice(`Done. I set ${displayFieldName(field, command.label)} to ${command.value}. Review it before saving.`);
        statusEl.textContent = 'Filled current page';
        return true;
    }

    function applyDataToCurrentPage(data) {
        if (!data || typeof data !== 'object') {
            return false;
        }

        const fieldAliases = {
            pickup_address: ['pickup_address'],
            delivery_address: ['delivery_address', 'delivery_stops[1][address]', 'delivery_stops[0][address]'],
            pickup_contact_person: ['pickup_contact_person', 'contact_person', 'contact_name'],
            pickup_contact_phone: ['pickup_contact_phone', 'contact_phone', 'phone'],
            recipient_name: ['recipient_name', 'delivery_stops[0][recipient_name]'],
            recipient_phone: ['recipient_phone', 'delivery_stops[0][recipient_phone]'],
            items_description: ['items_description', 'delivery_stops[0][notes]'],
            total_distance: ['total_distance'],
            total_price: ['total_price'],
            vehicle_type: ['vehicle_type'],
            scheduled_date: ['scheduled_date', 'date'],
            scheduled_time: ['scheduled_time', 'time'],
        };

        let filled = false;
        Object.entries(data).forEach(([key, value]) => {
            if (value === null || value === undefined || value === '') {
                return;
            }

            const names = fieldAliases[key] || [key];
            const field = names
                .map((name) => document.querySelector(`[name="${makeCssSafe(name)}"], #${makeCssSafe(name)}`))
                .find(Boolean);

            if (!field) {
                return;
            }

            fillAssistantField(field, value);
            filled = true;
        });

        return filled;
    }

    function handleAssistantAction(data) {
        if (!((data.action === 'open_page' || data.action === 'redirect') && data.url)) {
            return false;
        }

        const params = new URLSearchParams(data.data || {});
        const targetUrl = params.toString() ? `${data.url}?${params.toString()}` : data.url;
        const target = new URL(targetUrl, window.location.origin);
        if (target.origin !== window.location.origin) {
            addAssistantNotice('That action points outside this workspace.');
            return false;
        }

        setAssistantOpen(true);
        writeAssistantContext({
            intent: data.intent,
            action: data.action,
            url: target.pathname,
            data: data.data || {},
            summary: data.summary,
        });

        if (target.pathname === window.location.pathname) {
            if (params.toString()) {
                window.history.pushState({}, '', target.pathname + target.search);
            }

            const filled = applyDataToCurrentPage(data.data || {});
            statusEl.textContent = filled ? 'Filled this page' : 'Ready';
            if (filled) {
                addAssistantNotice('I filled what I could on this page. Please review before saving.');
            }
            return true;
        }

        addAssistantNotice('Opening the right page and keeping this chat here.');
        window.location.href = targetUrl;
        return true;
    }

    // ===== SEND TO BACKEND (with better error handling) =====
    function sendToBackend(text) {
        addMessage(text, 'user');
        lastAssistantNotice = '';
        const language = currentLanguage;
        statusEl.textContent = 'Working on it...';

        fetch('/ai/voice-assistant', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify({ message: text, language: language })
        })
        .then(async res => {
            if (!res.ok) {
                const body = await res.text();
                throw new Error(`Request failed with ${res.status}: ${body.substring(0, 150)}`);
            }

            // Check if response is JSON
            const contentType = res.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                // If not JSON, read as text and throw
                const text = await res.text();
                throw new Error(`Expected JSON, got ${contentType}: ${text.substring(0, 100)}`);
            }
            return res.json();
        })
        .then(data => {
            const responseMessage = formatAssistantResponse(data);
            addMessage(responseMessage, 'assistant');
            statusEl.textContent = 'Type a request or tap mic to speak';

            // ===== HANDLE ACTIONS =====
            if (handleAssistantAction(data)) {
                return;
            }

            if (data.action === 'submit') {
                const msg = language === 'np' 
                    ? 'कार्य सफलतापूर्वक पूरा भयो!' 
                    : 'Action completed successfully.';
                addMessage(msg, 'assistant');
            }

            if (data.done) {
                toggleBtn.disabled = false;
            }
        })
        .catch(err => {
            console.error('❌ Backend error:', err);
            const msg = currentLanguage === 'np' 
                ? 'क्षमा गर्नुहोस्, अनुरोध पूरा गर्न सकिएन।' 
                : 'Could not process that request. You can try rephrasing or type below.';
            addMessage(msg, 'assistant');
            statusEl.textContent = 'Type below or retry';
        })
        .finally(() => {
            if (textInput) textInput.disabled = false;
            if (sendBtn) sendBtn.disabled = false;
        });
    }

    function handleAssistantInput(text) {
        const command = text.trim().toLowerCase().replace(/[.!?]+$/, '');
        const decision = command.match(/^(approve|reject)(?: this| the)?(?: request)?$/);
        const save = /^(save|submit)(?: this| the)?(?: form| reminder| request)?$/.test(command);
        if (decision || save) {
            const forms = Array.from(document.querySelectorAll('.main-content form'))
                .filter(form => form.offsetWidth > 0 && form.method.toLowerCase() === 'post');
            const button = decision ? forms.flatMap(form => Array.from(form.querySelectorAll('button[name="decision"]')))
                .find(button => button.value === (decision[1] === 'approve' ? 'approved' : 'rejected')) : null;
            const form = button?.form || (save && forms.length === 1 ? forms[0] : null)
                || (save ? document.getElementById('reminder-editor') : null);
            addMessage(text, 'user');
            if (!form) {
                addAssistantNotice('Open the request or form you want to act on first.');
            } else if (form.reportValidity()) {
                addAssistantNotice('Submitting. The page will show whether it succeeded.');
                form.requestSubmit(button || undefined);
            } else {
                addAssistantNotice('Complete the highlighted fields first.');
            }
            if (textInput) textInput.disabled = false;
            if (sendBtn) sendBtn.disabled = false;
            return;
        }
        if (tryCurrentPageFill(text)) {
            if (textInput) textInput.disabled = false;
            if (sendBtn) sendBtn.disabled = false;
            return;
        }

        sendToBackend(text);
    }

    // ===== RECOGNITION EVENTS =====
    async function ensureMicrophoneAccess() {
        if (!navigator.mediaDevices?.getUserMedia) {
            return true;
        }

        try {
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            stream.getTracks().forEach(track => track.stop());
            return true;
        } catch (error) {
            console.warn('Microphone access failed:', error);
            if (statusEl) statusEl.textContent = 'Mic permission blocked. Type below.';
            textInput?.focus();
            return false;
        }
    }

    if (recognition) recognition.onresult = function(event) {
        let finalTranscript = '';
        for (let i = event.resultIndex; i < event.results.length; i++) {
            const transcript = event.results[i][0].transcript;
            if (transcript.trim() !== '') {
                heardSpeech = true;
            }
            if (event.results[i].isFinal) {
                finalTranscript += transcript;
            }
        }
        if (finalTranscript.trim() !== '') {
            recognition.stop();
            setListeningUi(false, 'Processing...');
            stopSpeaking();
            handleAssistantInput(finalTranscript.trim());
        }
    };

    if (recognition) recognition.onstart = function() {
        heardSpeech = false;
        lastRecognitionError = null;
        recognitionStartedAt = Date.now();
        setListeningUi(true, 'Listening... speak now');
    };

    if (recognition) recognition.onspeechstart = function() {
        heardSpeech = true;
        if (statusEl) statusEl.textContent = 'Hearing you...';
    };

    if (recognition) recognition.onspeechend = function() {
        if (statusEl) statusEl.textContent = 'Processing speech...';
    };

    if (recognition) recognition.onend = function() {
        if (!isListening) {
            return;
        }

        const listenedForMs = Date.now() - recognitionStartedAt;
        setListeningUi(false);

        if (lastRecognitionError === 'not-allowed' || lastRecognitionError === 'service-not-allowed') {
            if (statusEl) statusEl.textContent = 'Mic permission blocked. Type below.';
            textInput?.focus();
            return;
        }

        if (!heardSpeech || listenedForMs < 1200) {
            if (statusEl) statusEl.textContent = 'No voice heard. Tap mic or type below.';
        }
    };

    // ===== MEDIARECORDER GEMINI VOICE ENGINE (Resilient fallback for HTTP / network blocks) =====
    let useMediaRecorderMode = false;
    let mediaRecorder = null;
    let mediaChunks = [];
    let mediaStream = null;
    let mediaTimeout = null;

    function getSupportedMimeType() {
        const types = [
            'audio/webm;codecs=opus',
            'audio/webm',
            'audio/ogg;codecs=opus',
            'audio/mp4',
            'audio/wav'
        ];
        for (const t of types) {
            if (window.MediaRecorder && MediaRecorder.isTypeSupported(t)) {
                return t;
            }
        }
        return '';
    }

    async function startMediaRecorderVoice() {
        if (!navigator.mediaDevices?.getUserMedia || !window.MediaRecorder) {
            statusEl.textContent = 'Voice recording not supported in this browser.';
            textInput?.focus();
            return;
        }

        try {
            stopSpeaking();
            statusEl.textContent = 'Opening microphone...';
            mediaStream = await navigator.mediaDevices.getUserMedia({ audio: true });

            const mimeType = getSupportedMimeType();
            mediaRecorder = mimeType ? new MediaRecorder(mediaStream, { mimeType }) : new MediaRecorder(mediaStream);
            mediaChunks = [];

            mediaRecorder.ondataavailable = function(e) {
                if (e.data && e.data.size > 0) {
                    mediaChunks.push(e.data);
                }
            };

            mediaRecorder.onstop = async function() {
                setListeningUi(false, 'Transcribing with Gemini...');
                if (mediaTimeout) {
                    clearTimeout(mediaTimeout);
                    mediaTimeout = null;
                }

                if (mediaStream) {
                    mediaStream.getTracks().forEach(track => track.stop());
                    mediaStream = null;
                }

                if (mediaChunks.length === 0) {
                    statusEl.textContent = 'No voice recorded. Tap mic to retry.';
                    return;
                }

                const resolvedMime = mediaRecorder.mimeType || 'audio/webm';
                const audioBlob = new Blob(mediaChunks, { type: resolvedMime });

                if (audioBlob.size < 600) {
                    statusEl.textContent = 'Voice too short. Tap mic to speak.';
                    return;
                }

                try {
                    const reader = new FileReader();
                    reader.onloadend = async function() {
                        const base64Data = reader.result;
                        try {
                            const res = await fetch('/ai/transcribe', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                                },
                                body: JSON.stringify({
                                    audio: base64Data,
                                    mime_type: resolvedMime,
                                    language: currentLanguage
                                })
                            });

                            if (!res.ok) {
                                throw new Error(`HTTP ${res.status}`);
                            }

                            const json = await res.json();
                            const transcript = (json.transcript || '').trim();
                            if (transcript) {
                                statusEl.textContent = 'Processing request...';
                                handleAssistantInput(transcript);
                            } else {
                                statusEl.textContent = 'No words heard. Tap mic and try again.';
                            }
                        } catch (err) {
                            console.error('Gemini transcription error:', err);
                            statusEl.textContent = 'Could not transcribe voice. Type below or retry.';
                        }
                    };
                    reader.readAsDataURL(audioBlob);
                } catch (e) {
                    console.error('Audio blob processing error:', e);
                    statusEl.textContent = 'Audio processing error. Type below.';
                }
            };

            mediaRecorder.start(250);
            setListeningUi(true, 'Listening... speak now (tap mic to finish)');

            // Auto-stop after 8 seconds max
            mediaTimeout = setTimeout(() => {
                if (mediaRecorder && mediaRecorder.state === 'recording') {
                    stopMediaRecorderVoice();
                }
            }, 8000);

        } catch (err) {
            console.warn('Microphone access failed:', err);
            setListeningUi(false);
            statusEl.textContent = 'Mic access blocked. Allow mic in browser.';
            textInput?.focus();
        }
    }

    function stopMediaRecorderVoice() {
        if (mediaTimeout) {
            clearTimeout(mediaTimeout);
            mediaTimeout = null;
        }
        if (mediaRecorder && mediaRecorder.state === 'recording') {
            try {
                mediaRecorder.stop();
            } catch (e) {
                console.warn('Error stopping MediaRecorder:', e);
            }
        }
    }

    if (recognition) recognition.onerror = function(event) {
        console.warn('Voice recognition notice:', event.error);
        lastRecognitionError = event.error;

        // If Web Speech cloud has network issues (common on localhost / http), automatically switch to Gemini MediaRecorder!
        if (event.error === 'network' || event.error === 'service-not-allowed') {
            console.info('Switching to Gemini MediaRecorder engine due to Web Speech network restriction.');
            useMediaRecorderMode = true;
            try { recognition.stop(); } catch(e) {}
            // Immediately engage MediaRecorder voice recording
            startMediaRecorderVoice();
            return;
        }

        const statusMap = {
            'not-allowed': 'Mic access blocked. Allow mic in browser.',
            'no-speech': 'No voice heard. Tap mic or type.',
            'audio-capture': 'No mic found. Type below.',
        };

        const hint = statusMap[event.error] || 'Voice unavailable — type below.';
        if (statusEl) {
            statusEl.textContent = hint;
        }

        if (isListening) {
            try { recognition.stop(); } catch(e) {}
            setListeningUi(false, hint);
            stopSpeaking();
        }

        textInput?.focus();
    };

    // ===== TOGGLE LISTENING =====
    if (toggleBtn) {
        toggleBtn.addEventListener('click', async function() {
            if (isListening) {
                if (useMediaRecorderMode) {
                    stopMediaRecorderVoice();
                } else {
                    try { recognition?.stop(); } catch(e) {}
                    stopSpeaking();
                    setListeningUi(false);
                }
                return;
            }

            // If in MediaRecorder mode or Web Speech is missing, use Gemini recorder directly
            if (useMediaRecorderMode || !recognition) {
                startMediaRecorderVoice();
                return;
            }

            recognition.lang = currentLanguage === 'np' ? 'ne-NP' : 'en-US';
            stopSpeaking();
            toggleBtn.disabled = true;
            statusEl.textContent = 'Checking microphone...';

            try {
                recognition.start();
            } catch (error) {
                console.warn('Recognition start failed, switching to Gemini MediaRecorder:', error);
                useMediaRecorderMode = true;
                startMediaRecorderVoice();
            } finally {
                toggleBtn.disabled = false;
            }
        });
    }

    if (sendBtn && textInput) {
        const sendTypedMessage = function() {
            const text = textInput.value.trim();
            if (!text) {
                textInput.focus();
                return;
            }

            textInput.value = '';
            textInput.disabled = true;
            sendBtn.disabled = true;
            statusEl.textContent = 'Processing...';
            handleAssistantInput(text);
        };

        sendBtn.addEventListener('click', sendTypedMessage);
        textInput.addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                sendTypedMessage();
            }
        });
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            resetAssistantMessages();
            textInput?.focus();
        });
    }

    if (messagesEl) {
        messagesEl.addEventListener('click', function(event) {
            const button = event.target.closest('[data-prompt]');
            if (!button) return;

            const prompt = button.dataset.prompt;
            if (!prompt) return;

            if (textInput) {
                textInput.value = prompt;
                textInput.focus();
            }

            handleAssistantInput(prompt);
        });
    }

    restoreHistory();
    if (shouldRestoreOpen()) {
        chatWindow.style.display = 'flex';
    } else {
        chatWindow.style.display = 'none';
    }
});
