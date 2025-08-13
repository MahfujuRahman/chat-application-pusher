<template>
  <div class="chat-layout">
    <!-- Modal -->
    <div v-if="showModal" class="modal-overlay">
      <div class="modal-content">
        <div class="modal-header">
          <p class="modal-title">Start new conversation</p>
          <button class="close-btn" @click="showModal = false">&times;</button>
        </div>
        <div class="modal-body">
          <select v-model="selectedUserId" class="form-control">
            <option value="">Select User</option>
            <option v-for="user in users" :key="user.id" :value="user.id">
              {{ user.name }}
            </option>
          </select>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="showModal = false">Cancel</button>
          <button class="btn btn-primary" @click="createConversation">Create</button>
        </div>
      </div>
    </div>

    <!-- Group Chat Modal -->
    <div v-if="showGroupChatModal" class="modal-overlay">
      <div class="modal-content">
        <div class="modal-header">
          <p class="modal-title">Create Group Chat</p>
          <button class="close-btn" @click="showGroupChatModal = false">&times;</button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Group Name</label>
            <input v-model="groupChatName" type="text" class="form-control" placeholder="Enter group name" />
          </div>
          <div class="mb-3">
            <label class="form-label">Select Members</label>
            <div class="user-selection-list" style="max-height: 200px; overflow-y: auto; padding-left: 15px">
              <div v-for="user in users" :key="user.id" class="form-check">
                <input v-model="selectedUserIds" :value="user.id" type="checkbox" class="form-check-input"
                  :id="`user-${user.id}`" />
                <label :for="`user-${user.id}`" class="form-check-label">
                  {{ user.name }}
                </label>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="showGroupChatModal = false">Cancel</button>
          <button class="btn btn-primary" @click="createGroupChat"
            :disabled="!groupChatName || selectedUserIds.length === 0">Create Group</button>
        </div>
      </div>
    </div>

    <!-- Group Members Modal -->
    <div v-if="showGroupMembersModal" class="modal-overlay">
      <div class="modal-content" style="width: 450px">
        <div class="modal-header">
          <p class="modal-title">Group Members - {{ activeGroup?.group_name }}</p>
          <div class="d-flex align-items-center gap-2">
            <!-- 3-dot menu button -->
            <div class="dropdown" style="position: relative">
              <button class="btn btn-sm btn-outline-secondary" type="button"
                @click.stop="showGroupMenu = !showGroupMenu" title="Group Actions">
                <i class="fa fa-ellipsis-v"></i>
              </button>
              <div v-if="showGroupMenu" class="dropdown-menu show" style="
                  right: 0px;
                  left: auto;
                  position: absolute;
                  min-width: 140px;
                  z-index: 10;
                  background-color: #2a2a2a;
                  border: 1px solid rgb(58 58 58);
                  border-radius: 4px;
                  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
                " @click.stop>
                <button v-if="activeGroup?.creator === auth_info.id || auth_info.role_id === 1" class="dropdown-item"
                  @click="
                    showEditGroupSection = !showEditGroupSection;
                  showGroupMenu = false;
                  ">
                  <i class="fa fa-edit me-2"></i> Edit Group Name
                </button>
                <button v-if="activeGroup?.creator === auth_info.id || auth_info.role_id === 1"
                  class="dropdown-item text-danger" @click="
                    deleteGroup();
                  showGroupMenu = false;
                  ">
                  <i class="fa fa-trash me-2"></i> Delete Group
                </button>
              </div>
            </div>
            <button class="close-btn" @click="showGroupMembersModal = false">&times;</button>
          </div>
        </div>
        <div class="modal-body">
          <!-- Edit Group Name Section -->
          <div v-if="showEditGroupSection" class="edit-group-section mb-3">
            <div class="mb-2">
              <label class="form-label">Edit Group Name</label>
              <input v-model="editGroupName" type="text" class="form-control" placeholder="Enter new group name" />
            </div>
            <div class="d-flex gap-2">
              <button class="btn btn-sm btn-success" @click="updateGroupName"
                :disabled="!editGroupName || editGroupName === activeGroup?.group_name">
                Update
              </button>
              <button class="btn btn-sm btn-secondary mx-2" @click="cancelEditGroup">Cancel</button>
            </div>
          </div>

          <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <label class="form-label">Members ({{ groupMembers.members?.length }})</label>
              <button class="btn btn-sm btn-primary" @click="showAddMemberSection = !showAddMemberSection">
                <i class="fa fa-plus"></i> Add Member
              </button>
            </div>

            <!-- Add Member Section -->
            <div v-if="showAddMemberSection" class="add-member-section mb-3">
              <div class="user-selection-list"
                style="max-height: 150px; overflow-y: auto; border: 1px solid #444; border-radius: 4px; padding-left: 15px">
                <div v-for="user in availableUsers" :key="user.id" class="form-check">
                  <input v-model="newMemberIds" :value="user.id" type="checkbox" class="form-check-input"
                    :id="`new-member-${user.id}`" />
                  <label :for="`new-member-${user.id}`" class="form-check-label">
                    {{ user.name }}
                  </label>
                </div>
              </div>
              <div class="mt-2">
                <button class="btn btn-sm btn-success me-2" @click="addMembersToGroup"
                  :disabled="newMemberIds.length === 0">Add Selected</button>
                <button class="btn btn-sm btn-secondary mx-2" @click="showAddMemberSection = false">Cancel</button>
              </div>
            </div>

            <!-- Current Members List -->
            <div class="member-list">
              <div v-for="member in groupMembers?.members" :key="member.id" class="member-item">
                <div class="member-info">
                  <img v-if="member.image" :src="member.image" class="member-avatar"
                    @error="$event.target.src = 'avatar.png'" />
                  <div v-else class="member-avatar">
                    {{ getInitials(member.name) }}
                  </div>
                  <div class="member-details">
                    <div class="member-name">
                      {{ member.name }}
                      <span v-if="member.is_creator" class="creator-badge">Creator</span>
                    </div>
                    <div class="member-email">{{ member.email }}</div>
                  </div>
                </div>
                <button v-if="!member.is_creator && (activeGroup?.creator === auth_info.id || auth_info.role_id === 1)"
                  class="btn btn-sm btn-outline-danger" @click="removeMemberFromGroup(member.id)">
                  <i class="fa fa-times"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="showGroupMembersModal = false">Close</button>
        </div>
      </div>
    </div>

    <!-- Sidebar -->
    <aside v-if="!isMobile || mobileView === 'list'" class="chat-sidebar dark-mode">
      <div class="sidebar-header d-flex justify-content-between align-items-center">
        <span>Conversations</span>
        <button title="New Group Chat" class="btn btn-dark btn-sm" @click="openGroupChatModal"><i
            class="fa fa-users text-success"></i></button>
        <button class="btn btn-dark btn-sm" @click="openModal">
          <i class="fa fa-plus text-success"></i>
        </button>
      </div>
      <ul class="conversation-list">
        <li v-for="conversation in conversations" :key="conversation.id" @click="loadMessages(conversation)"
          class="conversation-item" :class="{ active: conversation.id === activeConversation?.id }">
          <img v-if="conversation.participant?.image" class="avatar" :src="conversation.participant?.image"
            @error="$event.target.src = 'avatar.png'" />
          <div v-else class="avatar" :class="{ 'group-avatar': conversation.participant?.is_group }">
            <i v-if="conversation.participant?.is_group" class="fa fa-users"></i>
            <span v-else>{{ getInitials(conversation.participant?.name) }}</span>
          </div>
          <div class="conversation-info">
            <div :title="conversation.participant?.name" class="conversation-name"
              style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis"
              v-if="conversation.participant?.name">
              {{ conversation.participant.name }}
              <span v-if="conversation.participant?.is_group" class="group-indicator">
                ({{ conversation.participant.participants_count }} members)
              </span>
            </div>
          </div>
          <div class="conversation-meta">
            <span class="conversation-time">{{ formatTime(conversation.last_updated) }}</span>
            <span v-if="conversation.unread_count > 0" class="unread-badge">{{ conversation.unread_count }}</span>
            <button v-if="conversation.participant?.is_group"
              class="btn btn-sm btn-outline-light group-members-btn text-white border-white"
              @click.stop="openGroupMembersModal(conversation)" title="View Group Members">
              <i class="fa fa-users"></i>
            </button>
          </div>
        </li>
      </ul>
    </aside>

    <!-- Chat Container -->
    <div v-if="!isMobile || mobileView === 'chat'" class="chat-container dark-mode">
      <div class="chat-header d-flex justify-content-between">
        <div class="d-flex align-items-center gap-3">
          <!-- Back button only in mobile -->
          <button v-if="isMobile" class="btn btn-link text-white me-2" @click="backToList">
            <i class="fa fa-arrow-left"></i>
          </button>
          <img v-if="activeConversation?.participant?.image" class="avatar"
            :src="activeConversation?.participant?.image" @error="$event.target.src = 'avatar.png'" />

            <div v-if="activeConversation?.participant?.name" class="avatar"
              :class="{ 'group-avatar': activeConversation?.participant?.is_group }">
              <i v-if="activeConversation?.participant?.is_group" class="fa fa-users"></i>
              <span v-else>{{ getInitials(activeConversation?.participant?.name) }}</span>
            </div>
            <div v-else class="avatar" style="padding: 15px;">
              <i class="fa fa-user"></i>
            </div>

          {{ activeConversation?.participant?.name || "" }}
        </div>
        <div class="d-flex gap-2">
          <!-- Debug button -->
          <!-- <button class="btn btn-warning btn-sm" @click="debugEchoConnection" title="Debug Echo">
            🔧 Debug
          </button> -->
          <button class="btn btn-dark btn-sm" @click="loadMessages(activeConversation)">
            <i class="fa fa-refresh"></i>
          </button>
        </div>
      </div>

      <div class="chat-messages" ref="chatMessages" @scroll="onChatScroll" @click="onChatClick">
        <!-- Load More Messages Button -->
        <div v-if="hasMoreMessages && !loadingMessages" class="text-center mb-3">
          <button @click="loadMoreMessages" :disabled="loadingMoreMessages" class="btn btn-outline-secondary btn-sm">
            <span v-if="loadingMoreMessages">Loading...</span>
            <span v-else>Load More Messages</span>
          </button>
        </div>

        <!-- Loading Messages Indicator -->
        <div v-if="loadingMessages" class="text-center">
          <span>Loading messages...</span>
        </div>

        <!-- Messages List with Previous Bubble Design -->
        <div v-for="message in messages" :key="message.id" class="chat-bubble" :class="message.type">
          <!-- Sender name for group chats -->
          <div v-if="message.type === 'theirs' && activeConversation?.participant?.is_group" class="chat-sender-name">
            {{ message.sender?.name }}
          </div>

          <!-- Message content -->
          <div>{{ message.text }}</div>

          <!-- Message time -->
          <div class="chat-meta">
            <span>{{ formatRelativeTime(message.created_at) }}</span>
          </div>
        </div>
      </div>

      <!-- Typing Indicator - positioned between messages and input -->
      <div v-if="isTyping && typingUser" class="typing-indicator-container">
        <div class="chat-bubble theirs typing-indicator">
          <div class="typing-dots">
            <span></span>
            <span></span>
            <span></span>
          </div>
          <span class="typing-text">{{ typingUser.name }} is typing...</span>
        </div>
      </div>

      <!-- Chat Input Area -->
      <form v-if="activeConversation" class="chat-input-container" @submit.prevent="sendMessage">
        <div class="chat-input-wrapper">
          <textarea ref="messageInput" v-model.trim="newMessage" class="chat-input" placeholder="Type your message..."
            :disabled="!activeConversation || sendingMessage" @keydown="handleInputKeydown" @input="handleTyping"
            rows="1"></textarea>

          <!-- Send Button -->
          <button type="submit" class="send-button" :disabled="!newMessage || !activeConversation || sendingMessage"
            :class="{ 'sending': sendingMessage }">
            <div v-if="sendingMessage" class="sending-spinner"></div>
            <span v-else class="send-icon" style="font-size: 1.3em;">
              <svg width="22" height="22" viewBox="0 0 22 22" fill="none">
                <path d="M2 11L20 2L11 20L10 13L2 11Z" fill="currentColor" />
              </svg>
            </span>
          </button>
        </div>

      </form>

      <div v-else class="chat-empty-state"
        style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; color: #aaa;">
        <div class="empty-message" style="text-align: center;">
          <i class="fa fa-comments" style="font-size: 3em; color: #6c757d; margin-bottom: 12px;"></i>
          <h4 style="margin-bottom: 8px; font-weight: 500;">No Conversation Selected</h4>
          <p style="font-size: 1.1em; color: #888;">Choose a chat from the sidebar to start messaging.<br>Or create a
            new conversation!</p>
        </div>
      </div>
    </div>

  </div>
</template>

<script>
import axios from "axios";
import { auth_store } from "../../../../../GlobalStore/auth_store";
import { mapState } from "pinia";

export default {
  data() {
    return {
      showModal: false,
      showGroupChatModal: false,
      selectedUserId: "",
      selectedUserIds: [],
      groupChatName: "",
      newMessage: "",
      users: [],
      conversations: [],
      messages: [],
      activeConversation: null,
      unreadCounts: {},
      showGroupMembersModal: false,
      groupMembers: [],
      availableUsers: [],
      newMemberIds: [],
      showAddMemberSection: false,
      activeGroup: null,
      showEditGroupSection: false,
      editGroupName: "",
      showGroupMenu: false,
      pendingMarkAsRead: null, // Track conversation ID that needs to be marked as read

      isMobile: window.innerWidth <= 767,
      mobileView: "list", // 'list' | 'chat'

      echoChannels: [], // Track active Echo channels for cleanup
      scrollThrottle: null, // Throttle scroll events
      clickThrottle: null, // Throttle click events

      // Enhanced UI states
      loadingMessages: false,
      loadingMoreMessages: false,
      sendingMessage: false,
      hasMoreMessages: false,
      currentPage: 1,
      isTyping: false,
      typingUser: null,
      userIsTyping: false,
      typingTimeout: null,
      typingDebounce: null,
    };
  },
  computed: {
    ...mapState(auth_store, {
      auth_info: "auth_info",
    }),
  },
  mounted() {
    console.log("🚀 LIFECYCLE: Conversation component mounted");
    console.log("User info:", this.auth_info);
    console.log("Window Echo available:", !!window.Echo);
    console.log("Window Pusher available:", !!window.Pusher);

    window.addEventListener("resize", this.handleResize);
    document.addEventListener("click", this.handleClickOutside);
    this.handleResize(); // initial setup

    this.loadConversations();

    // Small delay to ensure Echo is fully initialized
    console.log("⏰ Setting up Echo listeners with delay...");
    this.$nextTick(() => {
      setTimeout(() => {
        console.log("⚡ Executing delayed setupEchoListeners");
        this.setupEchoListeners();
      }, 100);
    });
  },
  beforeUnmount() {
    this.cleanupEchoListeners();
  },
  methods: {
    setupEchoListeners() {
      console.log("🔧 Setting up Echo listeners");

      const userId = this.auth_info?.id;
      if (!userId) {
        console.error("❌ No user ID found for Echo setup");
        return;
      }

      if (!window.Echo) {
        console.error("❌ Echo is not initialized");
        return;
      }

      console.log("✅ Echo validation passed. Setting up listener for user:", userId);

      try {
        const channelName = `chat.${userId}`;
        console.log("📡 Subscribing to channel:", channelName);

        // Create the channel first
        const channel = window.Echo.private(channelName);

        // Add the listener using a separate call to ensure it's registered
        channel.listen('MessageSent', (e) => {
          console.log("🎉 RECEIVED MessageSent event!", e);
          this.handleIncomingMessage(e);

          // Show real-time notification if message is from another user
          if (e.sender && e.sender.id !== this.auth_info.id) {
            window.s_alert(`💬 ${e.sender.name}: ${e.text.substring(0, 50)}...`);
          }
        });

        // Add typing listener - REMOVE THIS as we'll use conversation channels
        // channel.listen('UserTyping', (e) => {
        //   console.log("⌨️ RECEIVED UserTyping event!", e);
        //   this.handleTypingEvent(e);
        // });

        // Add event handlers
        channel.subscribed(() => {
          console.log("✅ Successfully subscribed to:", channelName);

          // Double-check that the listener is registered
          setTimeout(() => {
            const pusherChannelName = `private-${channelName}`;
            const pusherChannel = window.Echo.connector.pusher.channels.channels[pusherChannelName];
            if (pusherChannel) {
              console.log("📊 Channel callbacks after subscription:", pusherChannel.callbacks);
              if (pusherChannel.callbacks && pusherChannel.callbacks.MessageSent) {
                console.log("✅ MessageSent callback is properly registered!");
              } else {
                console.log("❌ MessageSent callback is NOT registered, forcing registration...");
                // Force bind if not registered
                pusherChannel.bind('MessageSent', (e) => {
                  console.log("🎉 FORCE BIND: RECEIVED MessageSent event!", e);
                  this.handleIncomingMessage(e);
                });
              }
            }
          }, 500);
        });

        channel.error((error) => {
          console.error("❌ Subscription error:", error);
          if (error.type === 'AuthError') {
            window.s_alert("Authentication failed. Please refresh the page.", "error");
          }
        });

        this.echoChannels.push({ channel, name: channelName });

      } catch (error) {
        console.error("❌ Echo setup error:", error);
      }
    },

    cleanupEchoListeners() {
      console.log("🧹 Cleaning up Echo listeners");
      this.echoChannels.forEach(({ channel, name }) => {
        try {
          window.Echo.leave(name);
          console.log(`✅ Left channel: ${name}`);
        } catch (error) {
          console.error(`❌ Error leaving channel ${name}:`, error);
        }
      });
      this.echoChannels = [];
    },

    // Setup conversation-specific listeners when a conversation is opened
    setupConversationListeners(conversationId) {
      if (!window.Echo || !conversationId) return;

      console.log("🔧 Setting up conversation listeners for conversation:", conversationId);

      // Check if we already have a listener for this conversation
      const existingChannel = this.echoChannels.find(ch => ch.name === `conversation.${conversationId}`);
      if (existingChannel) {
        console.log("⚠️ Conversation channel already exists, skipping setup");
        return;
      }

      try {
        const channelName = `conversation.${conversationId}`;
        console.log("📡 Subscribing to conversation channel:", channelName);

        // Create the conversation channel
        const channel = window.Echo.private(channelName);

        // Listen for typing events on this conversation
        channel.listen('UserTyping', (e) => {
          console.log("⌨️ RECEIVED UserTyping event on conversation channel!", e);
          this.handleTypingEvent(e);
        });

        // Add callback registration check
        channel.subscribed(() => {
          console.log("✅ Successfully subscribed to conversation channel:", channelName);

          // Double-check callback registration after subscription
          setTimeout(() => {
            const pusherChannelName = `private-${channelName}`;
            const pusherChannel = window.Echo.connector.pusher.channels.channels[pusherChannelName];
            if (pusherChannel && pusherChannel.callbacks && pusherChannel.callbacks.UserTyping) {
              console.log("✅ UserTyping callback is properly registered on conversation channel!");
            } else {
              console.log("❌ UserTyping callback NOT registered, force binding...");
              // Force bind the callback
              if (pusherChannel) {
                pusherChannel.bind('UserTyping', (e) => {
                  console.log("🎉 FORCE BIND: RECEIVED UserTyping event!", e);
                  this.handleTypingEvent(e);
                });
              }
            }
          }, 100);
        });

        // Handle subscription errors
        channel.error((error) => {
          console.error("❌ Conversation channel subscription error:", error);
        });

        // Store channel info for cleanup
        this.echoChannels.push({ channel, name: channelName });

      } catch (error) {
        console.error("❌ Conversation Echo setup error:", error);
      }
    },

    handleIncomingMessage(e) {
      console.log("🎯 STEP R2: handleIncomingMessage called");
      console.log("📨 Raw event object:", e);
      console.log("📨 Event keys:", Object.keys(e));
      console.log("📨 Event structure analysis:", {
        hasId: !!e.id,
        hasMessageProp: !!e.message,
        hasSender: !!e.sender,
        hasConversationId: !!e.conversation_id,
        hasText: !!e.text
      });

      // Handle both old and new data structures
      const messageData = e.message || e; // Support both structures
      const senderData = e.sender || messageData.sender;

      console.log("🔄 STEP R3: Processed data");
      console.log("📝 Message data:", messageData);
      console.log("👤 Sender data:", senderData);
      console.log("🆔 Message ID:", messageData.id);
      console.log("💬 Conversation ID:", messageData.conversation_id);
      console.log("📝 Text:", messageData.text);

      // Check if this is for current active conversation
      const isCurrentConversation = this.activeConversation?.id === messageData.conversation_id;
      console.log("🎯 STEP R4: Conversation check");
      console.log("Active conversation ID:", this.activeConversation?.id);
      console.log("Message conversation ID:", messageData.conversation_id);
      console.log("Is current conversation:", isCurrentConversation);

      // Avoid duplicates
      const exists = this.messages.some((m) => m.id === messageData.id);
      console.log("🔍 STEP R5: Duplicate check");
      console.log("Message exists in current list:", exists);
      console.log("Current messages count:", this.messages.length);
      console.log("Current message IDs:", this.messages.map(m => m.id));

      if (!exists && messageData.id) {
        console.log("✅ STEP R6: Adding new message");

        if (isCurrentConversation) {
          const newMessage = {
            id: messageData.id,
            text: messageData.text,
            conversation_id: messageData.conversation_id,
            created_at: messageData.date_time,
            sender: senderData,
            sender_id: senderData?.id,
            type: senderData?.id === this.auth_info.id ? "mine" : "theirs",
          };

          console.log("📱 STEP R7: Adding to current conversation UI");
          console.log("New message object:", newMessage);

          this.messages.push(newMessage);
          this.scrollToBottom();
          console.log("✅ STEP R8: Message added successfully!");
          console.log("New messages count:", this.messages.length);
        } else {
          console.log("🔄 STEP R7: Message from different conversation, refreshing list");
          this.loadConversations();
        }
      } else {
        console.log("⚠️ STEP R6: Message skipped", {
          reason: exists ? "Already exists" : "No ID",
          exists,
          hasId: !!messageData.id,
          messageId: messageData.id
        });
      }

      console.log("🏁 STEP R9: handleIncomingMessage completed");
    },

    handleTypingEvent(e) {
      console.log("⌨️ Handling typing event:", e);

      // Don't show typing indicator for current user
      if (e.user?.id === this.auth_info.id) {
        console.log("⌨️ Ignoring typing event for current user");
        return;
      }

      // Only show typing for active conversation
      if (e.conversation_id !== this.activeConversation?.id) {
        console.log("⌨️ Typing event for different conversation, ignoring");
        return;
      }

      if (e.is_typing) {
        // User started typing
        this.isTyping = true;
        this.typingUser = e.user;

        // Clear any existing typing timeout (but don't set new one - keep indicator visible)
        if (this.typingTimeout) {
          clearTimeout(this.typingTimeout);
          this.typingTimeout = null;
        }

        // Removed auto-hide timeout - typing indicator stays until user stops typing

      } else {
        // User stopped typing
        this.isTyping = false;
        this.typingUser = null;

        if (this.typingTimeout) {
          clearTimeout(this.typingTimeout);
          this.typingTimeout = null;
        }
      }
    },

    async loadConversations() {
      try {
        const res = await axios.get("/messages/get-all-conversations");
        // Sort conversations to put group chats at the top
        this.conversations = res.data.data.sort((a, b) => {
          // Group chats first, then individual chats
          if (a.participant?.is_group && !b.participant?.is_group) return -1;
          if (!a.participant?.is_group && b.participant?.is_group) return 1;
          // Within the same type, sort by last_updated (most recent first)
          return new Date(b.last_updated) - new Date(a.last_updated);
        });
      } catch (err) {
        console.error("Failed to load conversations", err);
      }
    },
    async loadUsers() {
      try {
        const res = await axios.get("users?get_all=1");
        this.users = res.data.data;
      } catch (err) {
        console.error("Failed to load users", err);
      }
    },
    openModal() {
      this.showModal = true;
      if (this.users.length === 0) {
        this.loadUsers();
      }
    },
    openGroupChatModal() {
      this.showGroupChatModal = true;
      if (this.users.length === 0) {
        this.loadUsers();
      }
    },
    async createConversation() {
      if (!this.selectedUserId) return;
      try {
        await axios.post("/messages/start-conversation", {
          participant_id: this.selectedUserId,
        });
        this.showModal = false;
        this.selectedUserId = "";
        await this.loadConversations();
      } catch (err) {
        console.error("Failed to create conversation", err);
      }
    },
    async createGroupChat() {
      if (!this.groupChatName || this.selectedUserIds.length === 0) return;
      try {
        await axios.post("/messages/create-group-chat", {
          name: this.groupChatName,
          participant_ids: this.selectedUserIds,
        });
        this.showGroupChatModal = false;
        this.groupChatName = "";
        this.selectedUserIds = [];
        await this.loadConversations();
      } catch (err) {
        console.error("Failed to create group chat", err);
      }
    },
    async loadMessages(convo) {
      if (!convo) return;

      // Cancel any pending mark as read for previous conversation
      this.pendingMarkAsRead = null;
      this.loadingMessages = true;
      this.currentPage = 1; // Reset to first page

      // Clean up previous conversation listeners (keep personal channel)
      this.echoChannels = this.echoChannels.filter(({ channel, name }) => {
        if (name.startsWith('conversation.')) {
          try {
            window.Echo.leave(name);
            console.log(`✅ Left previous conversation channel: ${name}`);
            return false; // Remove from array
          } catch (error) {
            console.error(`❌ Error leaving channel ${name}:`, error);
            return false;
          }
        }
        return true; // Keep personal channels
      });

      this.activeConversation = convo;

      // Setup conversation-specific Echo listeners
      this.setupConversationListeners(convo.id);

      try {
        const res = await axios.get(`/messages/get-conversation-messages/${convo.id}?page=1&per_page=20`);
        const messages = res.data.data.map((m) => ({
          ...m,
          type: m.sender?.id === this.auth_info.id ? "mine" : "theirs",
          sent: true, // Mark existing messages as sent
        }));

        this.messages = messages;
        this.hasMoreMessages = res.data.data.length === 20; // If we got 20 messages, there might be more

        // Auto-scroll to bottom
        this.$nextTick(() => {
          this.scrollToBottom();
        });

        // Store unread count for later processing
        this.pendingMarkAsRead = convo.unread_count > 0 ? convo.id : null;

        // Emit conversation opened event
        window.dispatchEvent(
          new CustomEvent("conversationOpened", {
            detail: { conversationId: convo.id },
          })
        );

        // Mark messages as read after a short delay to ensure user is actually viewing
        if (this.pendingMarkAsRead) {
          setTimeout(() => {
            this.checkAndMarkAsRead();
          }, 1500); // 1.5 second delay to ensure user is actually reading
        }

        if (this.isMobile) this.mobileView = "chat";

        // Set focus to input
        this.$nextTick(() => {
          if (this.$refs.messageInput) {
            this.$refs.messageInput.focus();
          }
        });

      } catch (err) {
        console.error("Failed to load messages", err);
        window.s_alert("Failed to load messages", "error");
      } finally {
        this.loadingMessages = false;
      }
    },

    async loadMoreMessages() {
      if (!this.activeConversation || this.loadingMoreMessages) return;

      this.loadingMoreMessages = true;

      try {
        this.currentPage += 1;
        const res = await axios.get(`/messages/get-conversation-messages/${this.activeConversation.id}?page=${this.currentPage}&per_page=20`);
        const moreMessages = res.data.data.map((m) => ({
          ...m,
          type: m.sender?.id === this.auth_info.id ? "mine" : "theirs",
          sent: true,
        }));

        // Prepend older messages to the beginning of the array
        this.messages = [...moreMessages, ...this.messages];

        // Check if there are more messages
        this.hasMoreMessages = res.data.data.length === 20;

      } catch (err) {
        console.error("Failed to load more messages", err);
        window.s_alert("Failed to load more messages", "error");
        this.currentPage -= 1; // Revert page increment on error
      } finally {
        this.loadingMoreMessages = false;
      }
    },
    async sendMessage() {
      if (!this.newMessage || this.sendingMessage) return;

      // Clear typing status
      this.stopTyping();

      const messageText = this.newMessage;
      const tempId = Date.now(); // Temporary ID for optimistic UI

      console.log("🚀 STEP 1: Starting sendMessage process");
      console.log("📝 Message content:", messageText);
      console.log("👤 Sender ID:", this.auth_info.id);
      console.log("💬 Conversation:", this.activeConversation);

      // Mark messages as read when user sends a message (indicates they're actively in the chat)
      if (this.pendingMarkAsRead) {
        await this.checkAndMarkAsRead();
      }

      // Optimistic UI - Add message immediately with sending status
      const optimisticMessage = {
        id: tempId,
        text: messageText,
        conversation_id: this.activeConversation.id,
        created_at: new Date().toISOString(),
        sender: this.auth_info,
        sender_id: this.auth_info.id,
        type: "mine",
        sending: true, // Mark as sending
        sent: false,
      };

      this.messages.push(optimisticMessage);
      this.newMessage = "";
      this.sendingMessage = true;

      // Auto-scroll to show new message
      this.$nextTick(() => {
        this.scrollToBottom();
      });

      try {
        const payload = {
          conversation_id: this.activeConversation.id,
          text: messageText,
        };

        console.log("📤 STEP 2: Sending API request with payload:", payload);
        const res = await axios.post("/messages/send", payload);
        console.log("✅ STEP 3: API response received:", res.data);

        // Update the optimistic message with real data
        const messageIndex = this.messages.findIndex(m => m.id === tempId);
        if (messageIndex !== -1) {
          this.messages[messageIndex] = {
            ...res.data.data,
            sender: this.auth_info,
            type: "mine",
            sending: false,
            sent: true,
          };
        }

        console.log("🎯 STEP 5: Message sent successfully. Backend should now broadcast to receiver.");
        console.log("📡 Expected broadcast channel:", `private-chat.${this.getReceiverId()}`);

      } catch (err) {
        console.error("❌ STEP ERROR: Failed to send message", err);

        // Remove failed message from UI
        const messageIndex = this.messages.findIndex(m => m.id === tempId);
        if (messageIndex !== -1) {
          this.messages.splice(messageIndex, 1);
        }

        // Restore message text
        this.newMessage = messageText;

        window.s_alert("Failed to send message. Please try again.", "error");
      } finally {
        this.sendingMessage = false;
      }
    },

    getReceiverId() {
      if (!this.activeConversation) return null;

      // For group chats, there might be multiple receivers
      if (this.activeConversation.participant?.is_group) {
        return "group-" + this.activeConversation.id;
      }

      // For individual chats, find the other participant
      const participants = [
        this.activeConversation.creator,
        this.activeConversation.participant_id || this.activeConversation.participant?.id
      ].filter(id => id && id !== this.auth_info.id);

      return participants[0] || null;
    },
    scrollToBottom() {
      this.$nextTick(() => {
        const container = this.$refs.chatMessages;
        if (container) {
          container.scrollTop = container.scrollHeight;
        }
      });
    },
    getInitials(name) {
      return name?.charAt(0).toUpperCase() || "U";
    },
    formatTime(time) {
      if (!time) return "";
      return new Date(time).toLocaleTimeString([], {
        hour: "2-digit",
        minute: "2-digit",
      });
    },

    // Format relative time like "1 min ago"
    formatRelativeTime(time) {
      if (!time) return "";

      const messageDate = new Date(time);
      const now = new Date();
      const diffMs = now - messageDate;
      const diffMins = Math.floor(diffMs / 60000);
      const diffHours = Math.floor(diffMs / 3600000);
      const diffDays = Math.floor(diffMs / 86400000);

      if (diffMins < 1) return "Just now";
      if (diffMins < 60) return `${diffMins} min ago`;
      if (diffHours < 24) return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`;
      if (diffDays < 7) return `${diffDays} day${diffDays > 1 ? 's' : ''} ago`;

      // For older messages, show date
      return messageDate.toLocaleDateString();
    },

    // Enhanced UI utility methods
    formatMessageTime(time) {
      if (!time) return "";
      const messageDate = new Date(time);
      const now = new Date();
      const diffMs = now - messageDate;
      const diffMins = Math.floor(diffMs / 60000);
      const diffHours = Math.floor(diffMs / 3600000);
      const diffDays = Math.floor(diffMs / 86400000);

      if (diffMins < 1) return "Just now";
      if (diffMins < 60) return `${diffMins}m ago`;
      if (diffHours < 24) return `${diffHours}h ago`;
      if (diffDays < 7) return `${diffDays}d ago`;

      return messageDate.toLocaleDateString();
    },

    isFirstMessageFromSender(message, index) {
      if (index === 0) return true;
      const prevMessage = this.messages[index - 1];
      return prevMessage.sender_id !== message.sender_id;
    },

    isLastMessageFromSender(message, index) {
      if (index === this.messages.length - 1) return true;
      const nextMessage = this.messages[index + 1];
      return nextMessage.sender_id !== message.sender_id;
    },

    // Typing functionality
    handleInputKeydown(event) {
      if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        this.sendMessage();
        return;
      }

      // Auto-resize textarea
      this.$nextTick(() => {
        const textarea = event.target;
        textarea.style.height = 'auto';
        textarea.style.height = Math.min(textarea.scrollHeight, 100) + 'px';
      });
    },

    handleTyping() {
      if (!this.activeConversation) return;

      // Clear existing debounce
      if (this.typingDebounce) {
        clearTimeout(this.typingDebounce);
      }

      // Start typing indicator
      if (!this.userIsTyping) {
        this.userIsTyping = true;
        this.broadcastTyping(true);
      }

      // Stop typing after 2 seconds of no input
      this.typingDebounce = setTimeout(() => {
        this.stopTyping();
      }, 2000);
    },

    stopTyping() {
      if (this.userIsTyping) {
        this.userIsTyping = false;
        this.broadcastTyping(false);
      }

      if (this.typingDebounce) {
        clearTimeout(this.typingDebounce);
        this.typingDebounce = null;
      }
    },

    broadcastTyping(isTyping) {
      if (!this.activeConversation) return;

      // Send typing event to backend for broadcasting
      axios.post('/messages/typing', {
        conversation_id: this.activeConversation.id,
        is_typing: isTyping
      }).catch(error => {
        console.error('Failed to broadcast typing status:', error);
      });

      console.log(`User ${isTyping ? 'started' : 'stopped'} typing in conversation ${this.activeConversation?.id}`);
    },

    handleResize() {
      this.isMobile = window.innerWidth <= 767;
      if (this.isMobile && !this.activeConversation) {
        this.mobileView = "list";
      }
    },

    handleClickOutside(event) {
      // Close dropdown if clicking outside
      if (!event.target.closest(".dropdown")) {
        this.showGroupMenu = false;
      }
    },

    backToList() {
      this.mobileView = "list";
      this.activeConversation = null;
    },
    async markMessagesAsRead(conversationId) {
      try {
        await axios.post(`/messages/mark-as-read/${conversationId}`);
      } catch (err) {
        console.error("Failed to mark messages as read", err);
      }
    },

    async checkAndMarkAsRead() {
      // Only mark as read if user is still on the same conversation and it's pending
      if (this.pendingMarkAsRead && this.activeConversation?.id === this.pendingMarkAsRead) {
        await this.markMessagesAsRead(this.pendingMarkAsRead);

        // Update the conversation's unread count in the list
        const conversationIndex = this.conversations.findIndex((c) => c.id === this.pendingMarkAsRead);
        if (conversationIndex !== -1) {
          this.conversations[conversationIndex].unread_count = 0;
        }

        // Emit global event to update header badge
        window.dispatchEvent(
          new CustomEvent("messagesMarkedAsRead", {
            detail: { conversationId: this.pendingMarkAsRead },
          })
        );

        this.pendingMarkAsRead = null;
      }
    },

    onChatScroll() {
      // Mark messages as read when user scrolls in the chat (throttled)
      if (this.pendingMarkAsRead && !this.scrollThrottle) {
        this.scrollThrottle = setTimeout(() => {
          this.checkAndMarkAsRead();
          this.scrollThrottle = null;
        }, 1000); // Throttle to max once per second
      }
    },

    onChatClick() {
      // Mark messages as read when user clicks in the chat area (throttled)
      if (this.pendingMarkAsRead && !this.clickThrottle) {
        this.clickThrottle = setTimeout(() => {
          this.checkAndMarkAsRead();
          this.clickThrottle = null;
        }, 500); // Throttle to max once per 500ms
      }
    },

    // Group Member Management Methods
    async openGroupMembersModal(conversation) {
      this.activeGroup = conversation;
      this.showGroupMembersModal = true;
      this.showAddMemberSection = false;
      this.showGroupMenu = false;
      this.newMemberIds = [];

      // Load group members
      await this.loadGroupMembers(conversation.id);

      // Load available users for adding
      await this.loadAvailableUsers(conversation.id);
    },

    async loadGroupMembers(conversationId) {
      try {
        const response = await axios.get(`/messages/group-members/${conversationId}`);
        console.log("Group Members Response:", response);

        this.groupMembers = response.data?.data || [];
      } catch (err) {
        console.error("Failed to load group members", err);
      }
    },

    async loadAvailableUsers(conversationId) {
      try {
        const response = await axios.get(`/messages/available-users/${conversationId}`);
        this.availableUsers = response.data.data || [];
      } catch (err) {
        console.error("Failed to load available users", err);
      }
    },

    async addMembersToGroup() {
      if (this.newMemberIds.length === 0) return;

      try {
        const payload = {
          conversation_id: this.activeGroup.id,
          user_ids: this.newMemberIds,
        };

        await axios.post("/messages/add-group-members", payload);

        // Refresh group members list
        await this.loadGroupMembers(this.activeGroup.id);
        await this.loadAvailableUsers(this.activeGroup.id);

        // Reset form
        this.newMemberIds = [];
        this.showAddMemberSection = false;

        // Refresh conversations to update member count
        await this.fetchConversations();
      } catch (err) {
        console.error("Failed to add members to group", err);
      }
    },

    async removeMemberFromGroup(userId) {

      if (!confirm("Are you sure you want to remove this member?")) return;

      try {
        const payload = {
          conversation_id: this.activeGroup.id,
          user_id: userId,
        };

        await axios.post("/messages/remove-group-member", payload);

        // Refresh group members list
        await this.loadGroupMembers(this.activeGroup.id);
        await this.loadAvailableUsers(this.activeGroup.id);

        // Refresh conversations to update member count
        await this.fetchConversations();
      } catch (err) {
        console.error("Failed to remove member from group", err);
      }

    },

    editGroup() {
      this.showEditGroupSection = true;
      this.showAddMemberSection = false;
      this.editGroupName = this.activeGroup?.group_name || "";
    },

    cancelEditGroup() {
      this.showEditGroupSection = false;
      this.editGroupName = "";
    },

    async updateGroupName() {
      if (!this.editGroupName || this.editGroupName === this.activeGroup?.group_name) {
        return;
      }

      try {
        const response = await axios.put(`/messages/conversations/${this.activeGroup.id}/group`, {
          group_name: this.editGroupName,
        });

        console.log("Update Group Name Response:", response.data.status);
        if (response.data?.status == "success") {
          // Update local data
          this.activeGroup.group_name = this.editGroupName;

          // Update conversations list
          const conversationIndex = this.conversations.findIndex((c) => c.id === this.activeGroup.id);
          if (conversationIndex !== -1) {
            this.conversations[conversationIndex].group_name = this.editGroupName;
          }

          // Update active conversation if it's the same group
          if (this.activeConversation?.id === this.activeGroup.id) {
            this.activeConversation.group_name = this.editGroupName;
          }

          this.showEditGroupSection = false;
          this.editGroupName = "";

          window.s_alert("Group name updated successfully");
        }
      } catch (err) {
        console.error("Failed to update group name", err);
        window.s_alert("Failed to update group name", "error");
      }
    },

    async deleteGroup() {
      if (!confirm("Are you sure you want to delete this group? This action cannot be undone.")) {
        return;
      }

      try {
        const response = await axios.delete(`/messages/conversations/${this.activeGroup.id}/group`);

        if (response.data.status == "success") {
          // Remove from conversations list
          this.conversations = this.conversations.filter((c) => c.id !== this.activeGroup.id);

          // Clear active conversation if it's the deleted group
          if (this.activeConversation?.id === this.activeGroup.id) {
            this.activeConversation = null;
            this.messages = [];
          }

          this.showGroupMembersModal = false;
          this.activeGroup = null;

          window.s_alert("Group deleted successfully");
        }
      } catch (err) {
        console.error("Failed to delete group", err);
        window.s_alert("Failed to delete group", "error");
      }
    },

    debugEchoConnection() {
      console.log("🔍 ===== COMPLETE ECHO DEBUG REPORT =====");

      // Basic setup check
      console.log("1️⃣ BASIC SETUP:");
      console.log("Echo object:", window.Echo);
      console.log("Pusher object:", window.Pusher);
      console.log("Auth token exists:", !!localStorage.getItem('admin_token'));
      console.log("Token preview:", localStorage.getItem('admin_token')?.substring(0, 30) + "...");
      console.log("Current user ID:", this.auth_info?.id);
      console.log("Current user:", this.auth_info);

      // Connection status
      console.log("\n2️⃣ CONNECTION STATUS:");
      if (window.Echo && window.Echo.connector) {
        const pusher = window.Echo.connector.pusher;
        console.log("Pusher connection state:", pusher.connection.state);
        console.log("Pusher socket ID:", pusher.connection.socket_id);
        console.log("Pusher channels:", pusher.channels.channels);
        console.log("Echo channels:", window.Echo.connector.channels);
      } else {
        console.log("❌ Echo connector not available");
      }

      // Channel analysis  
      console.log("\n3️⃣ CHANNEL ANALYSIS:");
      console.log("Vue component channels:", this.echoChannels);

      const userId = this.auth_info?.id;
      if (userId) {
        const expectedChannel = `private-chat.${userId}`;
        const hasChannel = window.Echo?.connector?.channels[expectedChannel];
        console.log(`Expected channel: ${expectedChannel}`);
        console.log("Channel exists:", !!hasChannel);

        if (hasChannel) {
          console.log("Channel details:", hasChannel);
          console.log("Channel callbacks:", hasChannel.callbacks);
          console.log("MessageSent callback exists:", !!hasChannel.callbacks?.MessageSent);
        }
      }

      // Active conversation info
      console.log("\n4️⃣ CONVERSATION INFO:");
      console.log("Active conversation:", this.activeConversation);
      console.log("Messages count:", this.messages.length);
      console.log("Expected receiver ID:", this.getReceiverId());

      // Manual subscription test
      console.log("\n5️⃣ MANUAL SUBSCRIPTION TEST:");
      if (userId && window.Echo) {
        console.log(`Creating test subscription to chat.${userId}`);
        try {
          const testChannel = window.Echo.private(`chat.${userId}`)
            .listen('test-event', (data) => {
              console.log('✅ Test event received:', data);
            })
            .listen('MessageSent', (data) => {
              console.log('✅ Manual MessageSent listener triggered:', data);
            });
          console.log('✅ Test subscription created successfully');

          // Check if callbacks are registered
          setTimeout(() => {
            const channelName = `private-chat.${userId}`;
            const channel = window.Echo.connector.channels[channelName];
            console.log(`Test subscription callbacks for ${channelName}:`, channel?.callbacks);
          }, 500);

        } catch (error) {
          console.error('❌ Test subscription failed:', error);
        }
      }

      console.log("🔍 ===== DEBUG REPORT COMPLETE =====");
      alert('Complete debug information logged to console. Check browser console for details.');
    },
  },
  beforeUnmount() {
    window.removeEventListener("resize", this.handleResize);
    document.removeEventListener("click", this.handleClickOutside);

    // Cancel any pending mark as read
    this.pendingMarkAsRead = null;

    // Clear throttle timers
    if (this.scrollThrottle) {
      clearTimeout(this.scrollThrottle);
      this.scrollThrottle = null;
    }
    if (this.clickThrottle) {
      clearTimeout(this.clickThrottle);
      this.clickThrottle = null;
    }

    // Clear typing indicators
    if (this.typingDebounce) {
      clearTimeout(this.typingDebounce);
      this.typingDebounce = null;
    }
    if (this.typingTimeout) {
      clearTimeout(this.typingTimeout);
      this.typingTimeout = null;
    }
    this.stopTyping();

    // Cleanup Echo listeners
    this.cleanupEchoListeners();
  },
};
</script>

<style scoped src="./Conversation.css"></style>
