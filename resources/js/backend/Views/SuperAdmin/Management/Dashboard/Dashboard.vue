<template>
  <div class="chat-dashboard">
    <!-- Header Section -->
    <div class="header-section">
      <div class="welcome-card">
        <div class="welcome-content">
          <h1 class="welcome-title">
            <i class="fas fa-comments"></i>
            Welcome to Chat
          </h1>
          <p class="welcome-subtitle">Start conversations and connect with others</p>
        </div>
        <div class="welcome-actions">
          <button @click="goToMessages" class="btn btn-primary btn-lg">
            <i class="fas fa-comments"></i>
            Go to Messages
          </button>
        </div>
      </div>
    </div>

    <!-- Quick Actions Section -->
    <div class="quick-actions">
      <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5 mb-4">
          <div class="action-card" @click="goToMessages">
            <div class="action-icon message-chat">
              <i class="fas fa-comments"></i>
            </div>
            <h3>Messages</h3>
            <p>View all your conversations and start new chats</p>
          </div>
        </div>

        <div class="col-md-6 col-lg-5 mb-4">
          <div class="action-card" @click="startNewMessage">
            <div class="action-icon new-message">
              <i class="fas fa-paper-plane"></i>
            </div>
            <h3>New Message</h3>
            <p>Start a new conversation with someone</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Chats Section -->
    <div class="recent-chats" v-if="recentChats.length > 0">
      <h2 class="section-title">Recent Conversations</h2>
      <div class="chat-list">
        <div v-for="chat in recentChats" :key="chat.id" class="chat-item" @click="openMessage(chat.id)">
          <div class="chat-avatar">
            <img :src="chat.avatar || '/avatar.png'" :alt="chat.name">
            <span v-if="chat.isOnline" class="online-indicator"></span>
          </div>
          <div class="chat-info">
            <div class="chat-header">
              <h4 class="chat-name">{{ chat.name }}</h4>
              <span class="chat-time">{{ formatTime(chat.lastMessage.time) }}</span>
            </div>
            <p class="chat-preview">{{ chat.lastMessage.text }}</p>
          </div>
          <div class="chat-meta">
            <span v-if="chat.unreadCount > 0" class="unread-badge">
              {{ chat.unreadCount }}
            </span>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script>
export default {
  name: 'ChatDashboard',
  data() {
    return {
      recentChats: []
    };
  },
  async created() {
    await this.loadRecentMessages();
  },
  methods: {
    // Navigate to your existing messages page
    goToMessages() {
      // Update this route to match your existing messages route
      this.$router.push('/message/conversation'); // or whatever your messages route is
    },

    // Navigate to start new message
    startNewMessage() {
      // Update this route to match your new message route
      this.$router.push('/message/conversation'); // or whatever your new message route is
    },

    // Open specific message/conversation
    openMessage(messageId) {
      // Update this route to match your message detail route
      this.$router.push(`/message/conversation`); // or whatever your message detail route is
    },

    // Load recent messages from your existing API
    async loadRecentMessages() {
      try {
        const response = await axios.get('/messages/get-all-conversations');
        if (response.data.status === 'success') {
          this.recentChats = response.data.data.map(convo => ({
            id: convo.id,
            name: convo.is_group ? convo.group_name : (convo.participant?.name || 'Unknown'),
            avatar: convo.participant?.image || '/avatar.png',
            lastMessage: {
              text: 'Tap to view conversation', // Replace with actual last message if available
              time: new Date(convo.last_updated)
            },
            unreadCount: convo.unread_count || 0,
            isOnline: convo.participant?.status === 'active'
          }));
        }
      } catch (error) {
        console.error('Error loading recent messages:', error);
      }
    },

    formatTime(date) {
      const now = new Date();
      const diff = now - date;
      const hours = Math.floor(diff / (1000 * 60 * 60));
      const days = Math.floor(diff / (1000 * 60 * 60 * 24));

      if (days > 0) {
        return `${days}d ago`;
      } else if (hours > 0) {
        return `${hours}h ago`;
      } else {
        return 'Just now';
      }
    }
  }
};
</script>

<style scoped>
.chat-dashboard {
  min-height: 100vh;
  /* background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); */
  padding: 20px;
}

/* Header Section */
.header-section {
  margin-bottom: 40px;
}

.welcome-card {
  /* background: rgba(255, 255, 255, 0.95); */
  border-radius: 20px;
  padding: 40px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
  backdrop-filter: blur(10px);
}

.welcome-content h1.welcome-title {
  font-size: 2.5rem;
  font-weight: 700;
  color: #fff;
  margin-bottom: 10px;
  display: flex;
  align-items: center;
  gap: 15px;
}

.welcome-title i {
  color: #6c5ce7;
  font-size: 2.2rem;
}

.welcome-subtitle {
  font-size: 1.2rem;
  color: #fff;
  margin: 0;
}

.welcome-actions .btn-primary {
  padding: 15px 30px;
  font-size: 1.1rem;
  font-weight: 600;
  border-radius: 50px;
  background: linear-gradient(45deg, #6c5ce7, #a29bfe);
  border: none;
  box-shadow: 0 8px 25px rgba(108, 92, 231, 0.3);
  transition: all 0.3s ease;
}

.welcome-actions .btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 12px 35px rgba(108, 92, 231, 0.4);
}

/* Quick Actions */
.quick-actions {
  margin-bottom: 40px;
}

.action-card {
  background: rgba(255, 255, 255, 0.95);
  border-radius: 20px;
  padding: 30px 25px;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s ease;
  height: 100%;
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.action-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
}

.action-icon {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 20px;
  font-size: 2rem;
  color: white;
}

.action-icon.message-chat {
  background: linear-gradient(45deg, #6c5ce7, #a29bfe);
}

.action-icon.new-message {
  background: linear-gradient(45deg, #00b894, #00cec9);
}

.action-card h3 {
  font-size: 1.4rem;
  font-weight: 600;
  color: #2d3436;
  margin-bottom: 10px;
}

.action-card p {
  color: #636e72;
  font-size: 1rem;
  line-height: 1.5;
  margin: 0;
}

/* Recent Chats */
.recent-chats {
  background: rgba(255, 255, 255, 0.95);
  border-radius: 20px;
  padding: 30px;
  backdrop-filter: blur(10px);
}

.section-title {
  font-size: 1.8rem;
  font-weight: 600;
  color: #2d3436;
  margin-bottom: 25px;
  display: flex;
  align-items: center;
  gap: 10px;
}

.chat-list {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.chat-item {
  display: flex;
  align-items: center;
  padding: 20px;
  background: rgba(108, 92, 231, 0.05);
  border-radius: 15px;
  cursor: pointer;
  transition: all 0.3s ease;
  border: 1px solid rgba(108, 92, 231, 0.1);
}

.chat-item:hover {
  background: rgba(108, 92, 231, 0.1);
  transform: translateX(5px);
}

.chat-avatar {
  position: relative;
  margin-right: 15px;
}

.chat-avatar img {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  object-fit: cover;
}

.online-indicator {
  position: absolute;
  bottom: 2px;
  right: 2px;
  width: 12px;
  height: 12px;
  background: #00b894;
  border-radius: 50%;
  border: 2px solid white;
}

.chat-info {
  flex: 1;
}

.chat-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 5px;
}

.chat-name {
  font-size: 1.1rem;
  font-weight: 600;
  color: #2d3436;
  margin: 0;
}

.chat-time {
  font-size: 0.9rem;
  color: #636e72;
}

.chat-preview {
  font-size: 0.95rem;
  color: #636e72;
  margin: 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 300px;
}

.chat-meta {
  display: flex;
  align-items: center;
}

.unread-badge {
  background: #e17055;
  color: white;
  font-size: 0.8rem;
  padding: 4px 8px;
  border-radius: 50px;
  font-weight: 600;
}

/* Responsive Design */
@media (max-width: 768px) {
  .chat-dashboard {
    padding: 15px;
  }

  .welcome-card {
    flex-direction: column;
    text-align: center;
    gap: 25px;
    padding: 30px 20px;
  }

  .welcome-title {
    font-size: 2rem !important;
  }

  .action-card {
    padding: 25px 20px;
  }

  .chat-preview {
    max-width: 200px;
  }
}
</style>
