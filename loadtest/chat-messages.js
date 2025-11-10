import http from 'k6/http';
import { check, sleep } from 'k6';

const BASE_URL = __ENV.BASE_URL || 'http://chatpulse.localhost:8080';
const TOKEN    = __ENV.TOKEN || '';
const ROOM_ID  = __ENV.ROOM_ID || '';

if (!TOKEN || !ROOM_ID) {
    throw new Error('TOKEN and ROOM_ID must be provided via environment variables');
}

export const options = {
    stages: [
        { duration: '10s', target: 5 },   // warm-up
        { duration: '30s', target: 20 },  // moderate load
        { duration: '30s', target: 40 },  // higher load
        { duration: '10s', target: 0 },   // ramp down
    ],
    thresholds: {
        http_req_failed: ['rate<0.01'],           // < 1% errors
        http_req_duration: ['p(95)<300'],         // p95 < 300ms
    },
};

export default function () {
    const body = {
        room_id: ROOM_ID,
        body: `k6 message at ${new Date().toISOString()}`,
    };

    const res = http.post(
        `${BASE_URL}/api/chat/messages`,
        body,
        {
            headers: {
                Authorization: `Bearer ${TOKEN}`,
                Accept: 'application/json',
            },
        }
    );

    check(res, {
        'status is 201': (r) => r.status === 201,
    });

    sleep(0.2);
}
