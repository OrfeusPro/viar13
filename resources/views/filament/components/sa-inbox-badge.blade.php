<div wire:poll.10s style="flex-shrink:0;display:flex;align-items:center;gap:5px;"
     x-data="{ sound: false, previous: null, audio: null,
        beep() { if (!this.audio) return; const oscillator = this.audio.createOscillator(); const gain = this.audio.createGain(); oscillator.connect(gain); gain.connect(this.audio.destination); gain.gain.value = 0.06; oscillator.frequency.value = 660; oscillator.start(); oscillator.stop(this.audio.currentTime + 0.15); } }"
     x-on:sa-inbox-count.window="if (previous !== null && $event.detail.count > previous && sound) beep(); previous = $event.detail.count">
    @if($allowed)
        <a href="{{ \App\Filament\Resources\SaConversations\SaConversationResource::getUrl() }}" aria-label="SA-диалоги, непрочитанных: {{ $count }}" style="display:inline-flex;align-items:center;gap:6px;padding:7px 10px;border-radius:9px;color:#2563eb;background:#eff6ff;white-space:nowrap;font-size:13px;">
            <x-filament::icon icon="heroicon-o-chat-bubble-left-right" style="width:20px;height:20px;"/>
            <span>SA</span><strong>{{ $count }}</strong>
        </a>
        <button type="button" aria-label="Звук новых SA-обращений" x-bind:aria-pressed="sound" title="Звук новых SA-обращений"
                x-on:click="sound = !sound; if(sound) { audio ??= new (window.AudioContext || window.webkitAudioContext)(); audio.resume(); }"
                style="padding:5px;color:#64748b;">
            <x-filament::icon icon="heroicon-o-speaker-wave" style="width:18px;height:18px;" x-show="sound" x-cloak/>
            <x-filament::icon icon="heroicon-o-speaker-x-mark" style="width:18px;height:18px;" x-show="!sound"/>
        </button>
    @endif
</div>
