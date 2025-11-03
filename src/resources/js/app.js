import './bootstrap'

// swap this to your room id:
const ROOM_ID = '01k8njke5xj8kxkz51bhaa1mxk'

window.addEventListener('DOMContentLoaded', () => {
    const log = m => {
        const el = document.getElementById('log')
        if (el) el.textContent += '\n' + m
    }

    if (!window.Echo) {
        console.warn('Echo not initialized');
        return
    }

    window.Echo.channel(`chat.room.${ROOM_ID}`)
        .listen('.MessageCreated', (e) => {
            console.log('MessageCreated', e)
            log('MessageCreated ' + JSON.stringify(e))
        })


    //todo: temp test from web
    window.Echo.channel('chat.public')
        .listen('.MessagePublished', (e) => {
            console.log('MessagePublished', e)
            const el = document.getElementById('log')
            if (el) el.textContent += '\n' + JSON.stringify(e)
        })
})
