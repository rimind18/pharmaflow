import api from "@/services/api";

export const getPosSummary = () =>
    api.get('/dashboard/pos-summary');

export const checkoutPOS = async (payload) => {
    const response = await api.post(
        "/transactions",
        payload
    );

    return response.data;
};