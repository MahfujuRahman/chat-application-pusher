//app layout
import Layout from "../Layouts/Layout.vue";

//SettingsRoutes
import SettingsRoutes from "../Management/Settings/setup/routes.js";
//routes

import UserRoutes from "../Management/UserManagement/User/setup/routes.js";

import Conversation from "../Management/Message/Conversation.vue";

const routes = {
  path: "",
  component: Layout,
  children: [
    {
      path: "message/conversation",
      name: "Conversation" + "Message",
      component: Conversation,
    },
    //management routes

    UserRoutes,

    //settings
    SettingsRoutes,
  ],
};

export default routes;
