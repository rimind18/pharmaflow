import api from "@/services/api";

export const checkoutPOS = async (payload) => {
    const response = await api.post(
        "/transactions",
        payload
    );

    return response.data;
};