public function authorize(): bool
{
    return true;
}

public function rules(): array
{
    return [
        'customer_id' => 'nullable|exists:users,id',
        'customer_name' => 'required|string',
        'customer_phone' => 'required|string',
        'delivery_address' => 'required|string',
        'delivery_latitude' => 'required|numeric',
        'delivery_longitude' => 'required|numeric',
        'delivery_city' => 'nullable|string',
        'notes' => 'nullable|string',
        'shipping_method' => 'required|in:cod,online_payment',
        'items' => 'required|array|min:1',
        'items.*.medicine_id' => 'required|exists:medicines,id',
        'items.*.quantity' => 'required|integer|min:1',
    ];
}