@component('mail::message')
# Новая заявка с сайта

| Поле | Значение |
|---|---|
| **Имя** | {{ $lead->name }} |
| **Телефон** | {{ $lead->phone }} |
| **E-mail** | {{ $lead->email ?? '—' }} |
| **Сообщение** | {{ $lead->message ?? '—' }} |
| **UTM** | {{ !empty($lead->utm) ? json_encode($lead->utm, JSON_UNESCAPED_UNICODE) : '—' }} |
| **Referer** | {{ $lead->referer_url ?: '—' }} |
| **Landing** | {{ $lead->landing_url ?: '—' }} |
| **IP (hash)** | {{ $lead->ip_hash ?? '—' }} |
| **User-Agent** | {{ $lead->user_agent ?? '—' }} |
| **Создано** | {{ $lead->created_at?->format('d.m.Y H:i') }} |

@component('mail::button', ['url' => url('/admin/contact-requests/' . $lead->id)])
Открыть в админке
@endcomponent

@endcomponent
