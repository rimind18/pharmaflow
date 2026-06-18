import { defineStore } from "pinia";
import { ref, computed } from "vue";

export const useCartStore = defineStore("cart", () => {

    // ==========================
    // STATE
    // ==========================

    const items = ref([]);

    const guestInfo = ref({
    customer_name: "",
    phone: "",
    address: "",
    city: "",
    province: "",
    postal_code: "",
    notes: "",

    shipping_method: "cod",
    payment_method: "cod",

    latitude: null,
    longitude: null,
});

    const discountAmount = ref(0);

    // ==========================
    // COMPUTED
    // ==========================

    const subtotal = computed(() => {
        return items.value.reduce((sum, item) => {
            return sum + (item.price * item.quantity);
        }, 0);
    });

    const shippingCost = computed(() => 0);

    const total = computed(() => {
        return (
            subtotal.value +
            shippingCost.value -
            discountAmount.value
        );
    });

    const cartCount = computed(() => {
        return items.value.reduce(
            (sum, item) => sum + item.quantity,
            0
        );
    });

    // ==========================
    // STORAGE
    // ==========================

    const saveToLocalStorage = () => {

        localStorage.setItem(
            "cart",
            JSON.stringify(items.value)
        );

        localStorage.setItem(
            "guestInfo",
            JSON.stringify(guestInfo.value)
        );

        localStorage.setItem(
            "discountAmount",
            discountAmount.value
        );
    };

    const loadFromLocalStorage = () => {

        const savedCart =
            localStorage.getItem("cart");

        const savedGuest =
            localStorage.getItem("guestInfo");

        const savedDiscount =
            localStorage.getItem(
                "discountAmount"
            );

        if (savedCart) {
            items.value =
                JSON.parse(savedCart);
        }

        if (savedGuest) {
            guestInfo.value =
                JSON.parse(savedGuest);
        }

        if (savedDiscount) {
            discountAmount.value =
                Number(savedDiscount);
        }
    };

    // ==========================
    // CART
    // ==========================

    const addToCart = (
        medicine,
        quantity = 1
    ) => {

        const existingItem =
            items.value.find(
                (item) =>
                    item.id === medicine.id
            );

        if (existingItem) {

            existingItem.quantity += quantity;

        } else {

            items.value.push({
                id: medicine.id,
                name: medicine.name,
                price:
                    medicine.selling_price ??
                    medicine.price ??
                    0,
                quantity,
            });
        }

        saveToLocalStorage();
    };

    const removeFromCart = (id) => {

        items.value =
            items.value.filter(
                (item) => item.id !== id
            );

        saveToLocalStorage();
    };

    const updateQuantity = (
        id,
        quantity
    ) => {

        const item =
            items.value.find(
                (item) => item.id === id
            );

        if (!item) return;

        if (quantity <= 0) {

            removeFromCart(id);
            return;
        }

        item.quantity = quantity;

        saveToLocalStorage();
    };

    const clearCart = () => {

        items.value = [];

        saveToLocalStorage();
    };

    // ==========================
    // ORDER DATA
    // ==========================

    const getOrderData = () => {

        return {
    items: items.value.map(item => ({
        medicine_id: item.id,
        quantity: item.quantity,
    })),

    customer_name:
        guestInfo.value.customer_name,

    customer_phone:
        guestInfo.value.phone,

    delivery_address:
        guestInfo.value.address,

    delivery_city:
        guestInfo.value.city,

    notes:
        guestInfo.value.notes,

    shipping_method:
guestInfo.value.payment_method === "cod"
    ? "cod"
    : "online_payment",

    payment_method:
        guestInfo.value.payment_method,

    delivery_latitude:
        guestInfo.value.latitude,

    delivery_longitude:
        guestInfo.value.longitude,
};
    };

    // ==========================
    // RESET
    // ==========================

    const resetAfterOrder = () => {

        clearCart();

        guestInfo.value = {
    customer_name: "",
    phone: "",
    address: "",
    city: "",
    province: "",
    postal_code: "",
    notes: "",

    shipping_method: "cod",
    payment_method: "cod",

    latitude: null,
    longitude: null,
};

        discountAmount.value = 0;

        saveToLocalStorage();
    };

    return {

        items,
        guestInfo,
        discountAmount,

        subtotal,
        shippingCost,
        total,
        cartCount,

        addToCart,
        removeFromCart,
        updateQuantity,
        clearCart,

        loadFromLocalStorage,
        saveToLocalStorage,

        getOrderData,
        resetAfterOrder,
    };

    
});