#!/usr/bin/env node

/**
 * WebSocket Server for Real-time Updates
 * Handles live exam progress, leaderboard updates, and notifications
 */

const WebSocket = require('ws');
const http = require('http');
const url = require('url');

const PORT = process.env.WEBSOCKET_PORT || 8080;
const HOST = process.env.WEBSOCKET_HOST || 'localhost';

// Create HTTP server
const server = http.createServer();

// Create WebSocket server
const wss = new WebSocket.Server({ server });

// Store active connections
const connections = {
    exams: {},      // exam_id -> [clients]
    teachers: {},   // teacher_id -> [clients]
    students: {},   // student_id -> [clients]
};

wss.on('connection', (ws, req) => {
    const queryParams = url.parse(req.url, true).query;
    const clientId = {
        type: queryParams.type,
        id: queryParams.id,
        examId: queryParams.exam_id,
        timestamp: Date.now(),
    };

    console.log(`✓ Client connected: ${clientId.type} ${clientId.id}`);

    // Register client
    registerClient(ws, clientId);

    ws.on('message', (message) => {
        try {
            const data = JSON.parse(message);
            handleMessage(ws, clientId, data);
        } catch (error) {
            console.error('Message parse error:', error);
        }
    });

    ws.on('close', () => {
        console.log(`✗ Client disconnected: ${clientId.type} ${clientId.id}`);
        unregisterClient(ws, clientId);
    });

    ws.on('error', (error) => {
        console.error('WebSocket error:', error);
    });

    // Send welcome message
    ws.send(JSON.stringify({
        type: 'connected',
        message: 'Connected to WebSocket server'
    }));
});

function registerClient(ws, clientId) {
    if (clientId.type === 'student' && clientId.examId) {
        if (!connections.exams[clientId.examId]) {
            connections.exams[clientId.examId] = [];
        }
        connections.exams[clientId.examId].push({ ws, clientId });
    } else if (clientId.type === 'teacher') {
        if (!connections.teachers[clientId.id]) {
            connections.teachers[clientId.id] = [];
        }
        connections.teachers[clientId.id].push({ ws, clientId });
    }
}

function unregisterClient(ws, clientId) {
    if (clientId.type === 'student' && clientId.examId) {
        if (connections.exams[clientId.examId]) {
            connections.exams[clientId.examId] = connections.exams[clientId.examId]
                .filter(c => c.ws !== ws);
        }
    } else if (clientId.type === 'teacher') {
        if (connections.teachers[clientId.id]) {
            connections.teachers[clientId.id] = connections.teachers[clientId.id]
                .filter(c => c.ws !== ws);
        }
    }
}

function handleMessage(ws, clientId, data) {
    switch (data.type) {
        case 'exam_progress':
            broadcastExamProgress(clientId.examId, data);
            break;
        case 'leaderboard_update':
            broadcastLeaderboardUpdate(clientId.examId, data);
            break;
        case 'notification':
            sendNotification(data.studentId, data);
            break;
        case 'ping':
            ws.send(JSON.stringify({ type: 'pong' }));
            break;
        default:
            console.log(`Unknown message type: ${data.type}`);
    }
}

function broadcastExamProgress(examId, data) {
    if (connections.exams[examId]) {
        const message = JSON.stringify({
            type: 'exam_progress',
            data: data,
            timestamp: Date.now()
        });

        connections.exams[examId].forEach(({ ws }) => {
            if (ws.readyState === WebSocket.OPEN) {
                ws.send(message);
            }
        });
    }
}

function broadcastLeaderboardUpdate(examId, data) {
    if (connections.exams[examId]) {
        const message = JSON.stringify({
            type: 'leaderboard_update',
            data: data,
            timestamp: Date.now()
        });

        connections.exams[examId].forEach(({ ws }) => {
            if (ws.readyState === WebSocket.OPEN) {
                ws.send(message);
            }
        });
    }
}

function sendNotification(studentId, data) {
    if (connections.students[studentId]) {
        const message = JSON.stringify({
            type: 'notification',
            data: data,
            timestamp: Date.now()
        });

        connections.students[studentId].forEach(({ ws }) => {
            if (ws.readyState === WebSocket.OPEN) {
                ws.send(message);
            }
        });
    }
}

// Health check endpoint
server.on('request', (req, res) => {
    if (req.url === '/health') {
        res.writeHead(200, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify({
            status: 'ok',
            timestamp: new Date().toISOString(),
            connections: {
                exams: Object.keys(connections.exams).length,
                teachers: Object.keys(connections.teachers).length,
            }
        }));
    } else {
        res.writeHead(404);
        res.end();
    }
});

server.listen(PORT, HOST, () => {
    console.log(`\n🚀 WebSocket server running on ${HOST}:${PORT}`);
    console.log(`📡 Health check: http://${HOST}:${PORT}/health\n`);
});

// Graceful shutdown
process.on('SIGINT', () => {
    console.log('\n⏹  Shutting down WebSocket server...');
    wss.clients.forEach(client => {
        client.close();
    });
    server.close();
    process.exit(0);
});
