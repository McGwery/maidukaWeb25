<?php

namespace App\Http\Requests;

use App\Enums\MessageType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SendMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'receiverShopId' => 'required|uuid|exists:shops,id',
            'message' => 'required_if:messageType,text|nullable|string|max:5000',
            'messageType' => ['required', Rule::enum(MessageType::class)],
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'nullable|string|url',
            'productId' => 'nullable|uuid|exists:products,id',
            'locationLat' => 'nullable|numeric|between:-90,90',
            'locationLng' => 'nullable|numeric|between:-180,180',
            'locationName' => 'nullable|string|max:255',
            'replyToMessageId' => 'nullable|uuid|exists:messages,id',
        ];
    }

    public function messages(): array
    {
        return [
            'receiverShopId.required' => 'Receiver shop is required.',
            'receiverShopId.exists' => 'Receiver shop not found.',
            'message.required_if' => 'Message text is required for text messages.',
            'messageType.required' => 'Message type is required.',
            'attachments.max' => 'Maximum 5 attachments allowed.',
            'productId.exists' => 'Product not found.',
        ];
    }
}

