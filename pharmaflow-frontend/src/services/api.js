import axios from "axios";

const api = axios.create({
    baseURL: import.meta.env.VITE_API_URL,

    headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
    },

    timeout: 15000,
});

api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem("token")

    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }

    return config
  },
  (error) => Promise.reject(error)
)

api.interceptors.response.use(
    (response) => response,

    (error) => {
        console.error(
            "API ERROR:",
            error.response?.data || error.message
        );

        if (error.response?.status === 401) {
            console.warn("401 unauthorized")

            localStorage.removeItem("token")
            localStorage.removeItem("user")
        }

        return Promise.reject(error);
    }
);

export default api;