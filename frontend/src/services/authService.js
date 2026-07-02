import Register from "../views/Register.vue";
import api from "./api";

export default {
    register(userData){
        return api.post('/register', userData)
    },

    login(credentials){
        return api.post('/login', credentials)
    },
    logout(){
        return api.post('/logout')
    },
    getProfile(){
        return api.get('/me')
    }

}