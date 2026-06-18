import axios from "axios";

const api = axios.create({
    baseURL: import.meta.env.VITE_API_URL,

    headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
    },

    timeout: 15000,
});

// ================================
// REQUEST INTERCEPTOR
// ================================
api.interceptors.request.use(
    (config) => {
        const token = localStorage.getItem("token");

        console.log(
            "API REQUEST:",
            `${config.baseURL}${config.url}`
        );

        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }

        return config;
    },

    (error) => {
        console.error("REQUEST ERROR:", error);

        return Promise.reject(error);
    }
);

// ================================
// RESPONSE INTERCEPTOR
// ================================
api.interceptors.response.use(
    (response) => response,

    (error) => {
        const status = error.response?.status;

        console.error(
            "API ERROR:",
            error.response?.data || error.message
        );

        // TOKEN EXPIRED
    if (error.response?.status === 401) {
  console.warn('401 unauthorized')

  localStorage.removeItem('token')
  localStorage.removeItem('user')

  // JANGAN redirect otomatis
}

        return Promise.reject(error);
    }
);

export default api;