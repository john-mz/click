const express = require('express');
const http = require('http');
const socketIo = require('socket.io');
const bodyParser = require('body-parser');
const cors = require('cors');

const app = express();
const server = http.createServer(app);
const io = socketIo(server, { cors: { origin: '*' } });

app.use(cors());
app.use(bodyParser.json());

app.post('/broadcast', (req, res) => {
    const { type, data } = req.body;
    io.emit(type, data);
    res.sendStatus(200);
});

io.on('connection', (socket) => {
    console.log('Client connected');
});

server.listen(3000, () => {
    console.log('WebSocket server running on port 3000');
});
