<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>ChatPulse Demo</title>
    @vite('resources/js/app.js')
    <style>
        body { font-family: sans-serif; padding: 1rem; }
        #log { height: 300px; overflow-y: auto; border: 1px solid #ccc; padding: 0.5rem; }
        #input { width: 100%; margin-top: 1rem; }
    </style>
</head>
<body>
<h1>ChatPulse Demo (Room: {{ $roomId }})</h1>

<pre id="log">Loading messages…</pre>
<input id="input" placeholder="Type a message…" />

<script>
    const ROOM_ID = "{{ $roomId }}";
    const TOKEN   = "{{ $token }}";

    // Fetch latest messages
    async function loadMessages() {
        const res = await fetch(`/api/chat/rooms/${ROOM_ID}/messages`, {
            headers: { 'Authorization': `Bearer ${TOKEN}` }
        });
        const data = await res.json();
        const log = document.getElementById('log');
        log.textContent = data.data.map(m => `${m.user_id}: ${m.body}`).join('\n');
    }

    // Send new message on Enter
    document.getElementById('input').addEventListener('keypress', async e => {
        if (e.key === 'Enter' && e.target.value.trim()) {
            const body = e.target.value.trim();
            e.target.value = '';
            await fetch('/api/chat/messages', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'Authorization': `Bearer ${TOKEN}`,
                },
                body: new URLSearchParams({ room_id: ROOM_ID, body }),
            });
        }
    });

    // Realtime updates
    window.addEventListener('DOMContentLoaded', () => {
        if (window.Echo) {
            window.Echo.channel(`chat.room.${ROOM_ID}`)
                .listen('.MessageCreated', e => {
                    const log = document.getElementById('log');
                    log.textContent += `\n${e.user_id}: ${e.body}`;
                    log.scrollTop = log.scrollHeight;
                });
        }
        loadMessages();
    });
</script>
</body>
</html>
