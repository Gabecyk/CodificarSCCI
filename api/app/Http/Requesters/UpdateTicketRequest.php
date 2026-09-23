<?php

namespace App\Http\Requesters;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateTicketRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],'priority' => ['required', new Enum(TicketPriority::class)],
            'employee_email' => ['required', 'email'],
            'responsible_id' => ['nullable', 'integer', Rule::exists('responsibles', 'id')],
            'status' => ['nullable', new Enum(TicketStatus::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'O título do chamado é obrigatório.',
            'title.max' => 'O título não pode ter mais de 255 caracteres.',
            'description.required' => 'A descrição do chamado é obrigatória.',
            'employee_email.required' => 'O e-mail do funcionário é obrigatório.',
            'priority.required' => 'Selecione uma prioridade para o chamado.',
            'priority.Illuminate\Validation\Rules\Enum' => 'A prioridade informada é inválida.',
            'responsible_id.exists' => 'O responsável selecionado não existe na base de dados.',
            'status.Illuminate\Validation\Rules\Enum' => 'O status informado é inválido.',
        ];
    }
}