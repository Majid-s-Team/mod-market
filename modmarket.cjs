const express = require("express");
const https = require("https");
const { Server } = require("socket.io");
const axios = require("axios");
const cors = require("cors");
const fs = require("fs");

const app = express();
app.use(cors());
app.use(express.json());

const options = {
  key: fs.readFileSync("/home/retrocubedev/ssl/combined.pem"),
  cert: fs.readFileSync("/home/retrocubedev/ssl/cert.pem"),
};

const server = https.createServer(options, app);
const io = new Server(server, {
  cors: {
    origin: "*",
    methods: ["GET", "POST"],
  },
});

const LARAVEL_API_URL = "https://modmarket.retrocubedev.com";
let onlineUsers = new Map();

// user_id -> Set(of with_user_ids)
let activeChats = new Map();

io.on("connection", (socket) => {
  console.log("New WebSocket connection:", socket.id);

  // REGISTER
  socket.on("register", async (rawData) => {
    console.log("Event: register | Data:", rawData);
    let data = typeof rawData === "string" ? JSON.parse(rawData) : rawData;
    const { user_id } = data;

    if (!user_id) {
      socket.emit("error", { message: "user_id is required" });
      socket.disconnect();
      return;
    }

    socket.userId = user_id;
    onlineUsers.set(user_id, socket);
    socket.emit("registered", { user_id });

    try {
      const unseen = await axios.get(`${LARAVEL_API_URL}/api/socket/messages/unseen/${user_id}`);
      if (unseen.data?.data?.length > 0)
        socket.emit("receive_message", unseen.data.data);

      const inbox = await axios.get(`${LARAVEL_API_URL}/api/socket/messages/inbox/${user_id}`);
      if (inbox.data?.data?.length > 0)
        socket.emit("inbox_list", inbox.data.data);
    } catch (err) {
      console.log("Failed to load unseen/inbox:", err.message);
    }
  });

  // SEND MESSAGE
  socket.on("send_message", async (rawData) => {
    let data = typeof rawData === "string" ? JSON.parse(rawData) : rawData;
    const { sender_id, receiver_id, vehicle_ad_id, message, message_type = "text", media_url } = data;

    if (!sender_id || !receiver_id || (!message && !media_url)) {
      socket.emit("error", { message: "sender_id, receiver_id, and either message or media_url are required" });
      return;
    }

    try {
      const payload = { sender_id, receiver_id, message_type };
      if (vehicle_ad_id) payload.vehicle_ad_id = vehicle_ad_id;
      if (message) payload.message = message;
      if (media_url) payload.media_url = media_url;

      // Save message
      const response = await axios.post(`${LARAVEL_API_URL}/api/socket/messages`, payload);
      const savedMessage = response.data?.data;
      if (!savedMessage) {
        socket.emit("error", { message: "Invalid response from Laravel API" });
        return;
      }

      const receiverSocket = onlineUsers.get(receiver_id);
      socket.emit("message_sent", savedMessage);

      // Emit to receiver
      if (receiverSocket) {
        receiverSocket.emit("receive_message", savedMessage);
      }

      // Check if receiver chat is active — if yes, mark as read immediately
      const isActive =
        activeChats.has(receiver_id) &&
        activeChats.get(receiver_id).has(sender_id);

      if (isActive) {
        try {
          await axios.post(`${LARAVEL_API_URL}/api/socket/messages/seen`, {
            message_ids: [savedMessage.id],
          });
          console.log(`Auto-marked as read for ${receiver_id} (chat open)`);
        } catch (err) {
          console.log("Failed to auto mark read:", err.message);
        }
      }

      // Update inbox both sides
      const updateData = {
        chat_with_id: receiver_id,
        last_message: savedMessage.message,
        message_type: savedMessage.message_type,
        media_url: savedMessage.media_url,
        time: savedMessage.created_at,
        date: savedMessage.created_at,
      };
      socket.emit("update_inbox", updateData);
      if (receiverSocket) {
        receiverSocket.emit("update_inbox", {
          ...updateData,
          chat_with_id: sender_id,
        });
      }
    } catch (err) {
      socket.emit("error", { message: "Failed to send message" });
    }
  });

  // GET CHAT HISTORY
  socket.on("get_chat_history", async (rawData) => {
    let data = typeof rawData === "string" ? JSON.parse(rawData) : rawData;
    const { user_id, with_user_id } = data;
    if (!user_id || !with_user_id) {
      socket.emit("error", { message: "user_id and with_user_id are required" });
      return;
    }

    try {
      // Mark chat active
      if (!activeChats.has(user_id)) activeChats.set(user_id, new Set());
      activeChats.get(user_id).add(with_user_id);

      const response = await axios.get(
        `${LARAVEL_API_URL}/api/socket/messages/history/${user_id}/${with_user_id}`
      );
      const chatHistory = response.data.data;
      socket.emit("chat_history", chatHistory);

      // Mark all unseen messages as read
      const unseenIds = chatHistory
        .filter((m) => m.receiver_id === user_id && m.status !== "read")
        .map((m) => m.id);

      if (unseenIds.length > 0) {
        await axios.post(`${LARAVEL_API_URL}/api/socket/messages/seen`, {
          message_ids: unseenIds,
        });
        console.log(`Marked ${unseenIds.length} messages as read for ${user_id}`);
      }
    } catch (err) {
      socket.emit("error", { message: "Failed to load chat history" });
    }
  });

  // CHAT CLOSE
  socket.on("chat_close", (rawData) => {
    let data = typeof rawData === "string" ? JSON.parse(rawData) : rawData;
    const { user_id, with_user_id } = data;
    if (!user_id || !with_user_id) {
      socket.emit("error", { message: "user_id and with_user_id are required" });
      return;
    }

    if (activeChats.has(user_id)) {
      activeChats.get(user_id).delete(with_user_id);
      if (activeChats.get(user_id).size === 0) activeChats.delete(user_id);
      console.log(`Chat closed: ${user_id} ↔ ${with_user_id}`);
    }
  });

  socket.on("disconnect", () => {
    if (socket.userId) {
      onlineUsers.delete(socket.userId);
      activeChats.delete(socket.userId);
      console.log(`User ${socket.userId} disconnected`);
    }
  });
});

const PORT = 4000;
server.listen(PORT, () =>
  console.log(`WebSocket server running on port ${PORT}`)
);
