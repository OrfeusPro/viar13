<div style="display: grid; gap: 8px; overflow-wrap: anywhere;">
    @if(filled($record->client_comment))<p style="white-space: pre-wrap;">{{ $record->client_comment }}</p>@endif
    @forelse($comments as $comment)
        <p style="white-space: pre-wrap;"><time>{{ $comment->created_at?->format('d.m.Y') ?: '—' }}</time> — {{ $comment->comment }}</p>
    @empty
        <p>Комментариев нет.</p>
    @endforelse
</div>
