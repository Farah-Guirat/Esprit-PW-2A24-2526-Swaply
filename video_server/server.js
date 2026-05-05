const express = require('express');
const http = require('http');
const socketIo = require('socket.io');
const cors = require('cors');
require('dotenv').config();

const app = express();
const server = http.createServer(app);
const io = socketIo(server, {
    cors: { origin: "*", methods: ["GET", "POST"], credentials: true }
});

app.use(cors({ origin: "*", methods: ["GET", "POST"], credentials: true }));
app.use(express.json());

const PORT = process.env.PORT || 3000;

const activeCalls = new Map();
const userSockets = new Map(); // userId (number) -> socketId

io.on('connection', (socket) => {
    console.log(`[CONNECT] Socket ${socket.id}`);

    // ─── Register User ────────────────────────────────────────────────────────
    socket.on('register_user', (data) => {
        const id_user = parseInt(data.id_user);
        if (id_user) {
            userSockets.set(id_user, socket.id);
            socket.userId = id_user;
            console.log(`[REGISTER] User ${id_user} -> socket ${socket.id}`);
            console.log(`[MAP] Users connectes:`, Object.fromEntries(userSockets));
            socket.emit('user_registered', { success: true, socket_id: socket.id });
        }
    });

    // ─── Initiate Call ────────────────────────────────────────────────────────
    // FIX CRITIQUE: on reçoit id_interlocuteur directement du client PHP
    socket.on('initiate_call', (data) => {
        const { id_video_call, id_conversation, id_initiateur, id_interlocuteur,
                initiateur_nom, initiateur_prenom } = data;

        const callId   = parseInt(id_video_call);
        const initId   = parseInt(id_initiateur);
        const interloc = parseInt(id_interlocuteur);

        console.log(`[CALL_INIT] Appel ${callId} par user ${initId} vers user ${interloc}`);
        console.log(`[MAP] Sockets:`, Object.fromEntries(userSockets));

        if (!activeCalls.has(callId)) {
            activeCalls.set(callId, {
                id_video_call: callId,
                id_conversation: parseInt(id_conversation),
                id_initiateur: initId,
                sockets: new Map(),
                createdAt: Date.now() // Tracker la création de l'appel
            });
        }

        const call = activeCalls.get(callId);
        call.sockets.set(initId, socket.id);
        socket.join(`call_${callId}`);

        // Notifier l'interlocuteur
        const targetSocket = userSockets.get(interloc);
        console.log(`[NOTIFY] User ${interloc} socket: ${targetSocket}`);
        if (targetSocket) {
            io.to(targetSocket).emit('incoming_call', {
                id_video_call: callId,
                id_conversation: parseInt(id_conversation),
                id_initiateur: initId,
                initiateur_nom: initiateur_nom || 'Utilisateur',
                initiateur_prenom: initiateur_prenom || ''
            });
            console.log(`[NOTIFY] OK - appel notifie a ${interloc}`);
        } else {
            console.log(`[NOTIFY] FAIL - user ${interloc} pas connecte`);
        }
    });

    // ─── Accept Call ──────────────────────────────────────────────────────────
    socket.on('accept_call', (data) => {
        const id_video_call = parseInt(data.id_video_call);
        const id_user       = parseInt(data.id_user);
        const call          = activeCalls.get(id_video_call);

        if (!call) { console.log(`[ACCEPT] Appel ${id_video_call} introuvable`); return; }

        call.sockets.set(id_user, socket.id);
        socket.join(`call_${id_video_call}`);

        const participants = Array.from(call.sockets.keys());
        console.log(`[ACCEPT] User ${id_user} a accepte. Participants: ${participants}`);

        // Notifier tous les participants dans la room
        io.to(`call_${id_video_call}`).emit('call_accepted', { id_video_call, id_user, participants });
    });

    // ─── Reject Call ──────────────────────────────────────────────────────────
    socket.on('reject_call', (data) => {
        const id_video_call = parseInt(data.id_video_call);
        const id_user       = parseInt(data.id_user);
        const call          = activeCalls.get(id_video_call);
        if (!call) return;

        const initSocket = call.sockets.get(call.id_initiateur);
        if (initSocket) {
            io.to(initSocket).emit('call_rejected', { 
                id_video_call, 
                id_user,
                reason: 'user_rejected'
            });
            console.log(`[REJECT] Appel ${id_video_call} rejeté par ${id_user} (initiateur: ${call.id_initiateur})`);
        }
        activeCalls.delete(id_video_call);
    });

    // ─── WebRTC Signaling ─────────────────────────────────────────────────────
    socket.on('webrtc_offer', (data) => {
        const toSocket = userSockets.get(parseInt(data.to_user));
        console.log(`[OFFER] ${data.from_user} -> ${data.to_user} (socket: ${toSocket})`);
        if (toSocket) io.to(toSocket).emit('webrtc_offer', {
            id_video_call: parseInt(data.id_video_call),
            from_user: parseInt(data.from_user),
            offer: data.offer
        });
    });

    socket.on('webrtc_answer', (data) => {
        const toSocket = userSockets.get(parseInt(data.to_user));
        console.log(`[ANSWER] ${data.from_user} -> ${data.to_user} (socket: ${toSocket})`);
        if (toSocket) io.to(toSocket).emit('webrtc_answer', {
            id_video_call: parseInt(data.id_video_call),
            from_user: parseInt(data.from_user),
            answer: data.answer
        });
    });

    socket.on('ice_candidate', (data) => {
        const toSocket = userSockets.get(parseInt(data.to_user));
        if (toSocket) io.to(toSocket).emit('ice_candidate', {
            id_video_call: parseInt(data.id_video_call),
            from_user: parseInt(data.from_user),
            candidate: data.candidate
        });
    });

    // ─── Leave / End ──────────────────────────────────────────────────────────
    socket.on('leave_call', (data) => {
        const id_video_call = parseInt(data.id_video_call);
        const id_user       = parseInt(data.id_user);
        const call          = activeCalls.get(id_video_call);
        if (!call) return;

        call.sockets.delete(id_user);
        socket.leave(`call_${id_video_call}`);

        const remaining = Array.from(call.sockets.keys());
        // Notifier les participants restants dans la room
        io.to(`call_${id_video_call}`).emit('user_left', { id_video_call, id_user, remaining_participants: remaining });

        if (call.sockets.size === 0) activeCalls.delete(id_video_call);
    });

    socket.on('end_call', (data) => {
        const id_video_call = parseInt(data.id_video_call);
        const call          = activeCalls.get(id_video_call);
        if (!call) return;

        // Notifier tous les participants une seule fois
        io.to(`call_${id_video_call}`).emit('call_ended', { id_video_call, reason: 'user_ended' });
        activeCalls.delete(id_video_call);
    });

    socket.on('toggle_audio', (d) => io.to(`call_${d.id_video_call}`).emit('audio_toggled', d));
    socket.on('toggle_video', (d) => io.to(`call_${d.id_video_call}`).emit('video_toggled', d));

    // ─── Disconnect ────────────────────────────────────────────────────────────
    socket.on('disconnect', () => {
        if (socket.userId) {
            if (userSockets.get(socket.userId) === socket.id) {
                userSockets.delete(socket.userId);
            }
            activeCalls.forEach((call, callId) => {
                if (call.sockets.has(socket.userId)) {
                    call.sockets.delete(socket.userId);
                    const remaining = Array.from(call.sockets.keys());
                    call.sockets.forEach((sid) => {
                        io.to(sid).emit('user_left', { id_video_call: callId, id_user: socket.userId, remaining_participants: remaining });
                    });
                    if (call.sockets.size === 0) activeCalls.delete(callId);
                }
            });
        }
        console.log(`[DISCONNECT] Socket ${socket.id}`);
    });
});

// ─── REST ──────────────────────────────────────────────────────────────────────
app.get('/health', (req, res) => {
    res.json({ status: 'OK', users: Object.fromEntries(userSockets), calls: activeCalls.size });
});
app.get('/api/calls', (req, res) => {
    res.json({ success: true, calls: Array.from(activeCalls.values()).map(c => ({ id: c.id_video_call, participants: Array.from(c.sockets.keys()) })) });
});

server.listen(PORT, () => {
    console.log(`🎥 Swaply WebRTC sur port ${PORT}`);
    
    // Nettoyer les appels orphelins toutes les 30 secondes
    setInterval(() => {
        const now = Date.now();
        const orphanedCalls = [];
        
        activeCalls.forEach((call, callId) => {
            // Si un appel n'a qu'un participant depuis plus de 5 minutes, le supprimer
            if (call.sockets.size <= 1 && (now - (call.createdAt || now)) > 300000) {
                orphanedCalls.push(callId);
            }
        });
        
        orphanedCalls.forEach(callId => {
            console.log(`[CLEANUP] Suppression d'appel orphelin: ${callId}`);
            activeCalls.delete(callId);
        });
        
        if (orphanedCalls.length > 0) {
            console.log(`[CLEANUP] ${orphanedCalls.length} appel(s) orphelin(s) supprimé(s)`);
        }
    }, 30000); // Toutes les 30 secondes
});