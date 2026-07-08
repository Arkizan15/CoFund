import api from "./api";

export default {
    register(userData) {
        return api.post('/auth/register', userData);
    },

    login(credentials) {
        return api.post('/auth/login', credentials);
    },

    logout() {
        return api.post('/auth/logout');
    },

    getProfile() {
        return api.get('/auth/me');
    },

    forgotPassword(data) {
        return api.post('/auth/password/forgot', data);
    },

    resetPassword(data) {
        return api.post('/auth/password/reset', data);
    },
};
