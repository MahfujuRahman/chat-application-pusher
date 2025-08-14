//app layout
import Layout from "../Layouts/Layout.vue";

import Conversation from "../Management/Message/Conversation.vue";
//SettingsRoutes
import SettingsRoutes from "../Management/Settings/setup/routes.js";
const routes = {
  path: "",
  component: Layout,
  children: [
    {
      path: "message/conversation",
      name: "ConversationMessage",
      component: Conversation,
    },
    SettingsRoutes,
  ],
};

export default routes;
