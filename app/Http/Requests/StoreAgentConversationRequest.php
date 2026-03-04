<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\AgentMode;
use App\Enums\ModelName;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Laravel\Ai\Files\Base64Image;

final class StoreAgentConversationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // AI SDK sends messages array
            'messages' => ['required', 'array', 'min:1'],
            'messages.*.role' => ['required', 'string', 'in:user,assistant,system'],
            'messages.*.parts' => ['required_if:messages.*.role,user', 'array'],
            'messages.*.parts.*.type' => ['required', 'string'],
            'messages.*.parts.*.text' => ['required_if:messages.*.parts.*.type,text', 'string'],

            // File parts (images sent as data URLs by AI SDK)
            'messages.*.parts.*.mediaType' => ['nullable', 'string'],
            'messages.*.parts.*.url' => ['nullable', 'string'],

            // Body params (sent by AI SDK transport)
            'mode' => ['required', Rule::enum(AgentMode::class)],
            'model' => ['required', Rule::enum(ModelName::class)],
        ];
    }

    /**
     * Get the user's input message from the AI SDK format.
     */
    public function userMessage(): string
    {
        /** @var array<int, array{role: string, parts: array<int, array{type: string, text?: string}>}> $messages */
        $messages = $this->validated('messages');

        $lastUserMessage = collect($messages)
            ->reverse()
            ->firstWhere('role', 'user');

        if (! $lastUserMessage) {
            return '';
        }

        /** @var array{parts: array<int, array{type: string, text?: string}>} $lastUserMessage */
        return collect($lastUserMessage['parts'])
            ->where('type', 'text')
            ->pluck('text')
            ->implode('');
    }

    /**
     * Get the validated mode.
     */
    public function mode(): AgentMode
    {
        /** @var string $mode */
        $mode = $this->validated('mode');

        return AgentMode::from($mode);
    }

    /**
     * Get the validated model.
     */
    public function modelName(): ModelName
    {
        /** @var string $model */
        $model = $this->validated('model');

        return ModelName::from($model);
    }

    /**
     * Extract image attachments from the last user message.
     *
     * @return array<int, Base64Image>
     */
    public function userAttachments(): array
    {
        /** @var array<int, array{role: string, parts: array<int, array{type: string, mediaType?: string, url?: string}>}> $messages */
        $messages = $this->validated('messages');

        $lastUserMessage = collect($messages)
            ->reverse()
            ->firstWhere('role', 'user');

        if (! $lastUserMessage) { // @codeCoverageIgnoreStart
            return []; // @codeCoverageIgnore
        } // @codeCoverageIgnoreEnd

        return collect($lastUserMessage['parts'])
            ->where('type', 'file')
            ->filter(fn (array $part): bool => isset($part['mediaType'], $part['url'])
                && str_starts_with($part['mediaType'], 'image/')
                && str_starts_with($part['url'], 'data:'))
            ->map(function (array $part): Base64Image {
                $url = $part['url'];
                $mediaType = $part['mediaType'];

                // Parse data URL: "data:image/jpeg;base64,/9j/4AAQ..."
                $base64Data = mb_substr($url, mb_strpos($url, ',') + 1);

                return new Base64Image($base64Data, $mediaType);
            })
            ->values()
            ->all();
    }

    public function messages(): array
    {
        return [
            'messages.required' => 'Messages are required',
            'mode.required' => 'Mode is required',
            'model.required' => 'Model is required',
        ];
    }
}
