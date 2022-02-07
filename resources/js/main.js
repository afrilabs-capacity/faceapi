import { createApp } from "vue";
import {createWebHistory, createRouter, createWebHashHistory} from "vue-router";
import mitt from 'mitt';
import VueClipboard from 'vue-clipboard2'
const emitter = mitt();
// styles

import "@fortawesome/fontawesome-free/css/all.min.css";
import "./assets/styles/tailwind.css";

// mouting point for the whole app

import App from "./App.vue";

// layouts

import Admin from "./layouts/Admin.vue";
import Auth from "./layouts/Auth.vue";

// views for Admin layout

import Dashboard from "./views/admin/Dashboard.vue";
import Settings from "./views/admin/Settings.vue";
import Tables from "./views/admin/Tables.vue";
import WebsiteUsers from "./views/admin/WebsiteUsers.vue";
import Maps from "./views/admin/Maps.vue";

// views for Auth layout

import Login from "./views/auth/Login.vue";
import Register from "./views/auth/Register.vue";

// views without layouts

import Landing from "./views/Landing.vue";
import Profile from "./views/Profile.vue";
import Index from "./views/Index.vue";

// routes

const routes = [
  {
    path: "/admin",
    redirect: "/admin/dashboard",
    component: Admin,
    children: [
      {
        path: "/admin/dashboard",
        component: Dashboard,
      },
      {
        path: "/admin/settings",
        component: Settings,
      },
      {
        path: "/admin/websites",
        component: Tables,
      },
      {
        path: "/admin/websites-users",
        component: WebsiteUsers,
      },
      {
        path: "/admin/maps",
        component: Maps,
      },
    ],
  },
  {
    path: "/auth",
    redirect: "/auth/login",
    component: Auth,
    children: [
      {
        path: "/auth/login",
        component: Login,
      },
      {
        path: "/auth/register",
        component: Register,
      },
    ],
  },
  {
    path: "/landing",
    component: Landing,
  },
  {
    path: "/profile",
    component: Profile,
  },
  {
    path: "/",
      redirect: "/auth/login",
      component: Auth,
  },
  { path: "/:pathMatch(.*)*", redirect: "/" },
];

const router = createRouter({
    mode: 'hash',
  history: createWebHashHistory(),
  routes,
});

window._ = require('lodash');

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

window.axios = require('axios');

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const app = createApp(App);
VueClipboard.config.autoSetContainer = true
app.config.globalProperties.emitter = emitter;
app.use(VueClipboard)
app.use(router).mount("#app")
