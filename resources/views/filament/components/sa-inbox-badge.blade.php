<div wire:poll.10s class="viar-sa-global"
     x-data="{ sound: false, previous: @js($count), audio: null, top: 70, pointer: null, startY: 0, startTop: 0, moved: false,
        init() { try { const saved = Number.parseInt(localStorage.getItem('saConversationsFloatingTop'), 10); if(Number.isFinite(saved)) this.top = saved; this.sound = localStorage.getItem('viar.sa.sound') === 'true'; } catch(e) {} this.$nextTick(() => this.clamp()); },
        clamp() { this.top = Math.min(Math.max(this.top, 64), Math.max(64, window.innerHeight - this.$el.offsetHeight - 20)); },
        begin(e) { this.moved = false; if(e.button !== 0 || e.target.closest('button')) return; this.pointer = e.pointerId; this.startY = e.clientY; this.startTop = this.top; },
        move(e) { if(this.pointer !== e.pointerId) return; const delta = e.clientY - this.startY; if(!this.moved && Math.abs(delta) <= 4) return; this.moved = true; this.top = this.startTop + delta; this.clamp(); e.preventDefault(); },
        end() { if(this.pointer === null) return; this.pointer = null; if(this.moved) { try { localStorage.setItem('saConversationsFloatingTop', String(this.top)); } catch(e) {} } },
        enableAudio() { try { const Audio = window.AudioContext || window.webkitAudioContext; if(Audio) { this.audio ??= new Audio(); this.audio.resume(); } } catch(e) {} },
        toggleSound() { this.sound = !this.sound; try { localStorage.setItem('viar.sa.sound', String(this.sound)); } catch(e) {} if(this.sound) this.enableAudio(); },
        beep() { this.enableAudio(); if (!this.audio) return; const oscillator = this.audio.createOscillator(); const gain = this.audio.createGain(); oscillator.connect(gain); gain.connect(this.audio.destination); gain.gain.value = 0.06; oscillator.frequency.value = 660; oscillator.start(); oscillator.stop(this.audio.currentTime + 0.15); },
        update(count) { if(count > this.previous) { if(this.sound) this.beep(); if(window.FilamentNotification) new window.FilamentNotification().title('Появилось новое SA-обращение.').info().send(); } this.previous = count; } }"
     x-bind:style="{ top: top + 'px' }"
     x-on:dragstart.prevent
     x-on:pointerdown="begin($event)" x-on:pointermove.window="move($event)" x-on:pointerup.window="end()" x-on:pointercancel.window="end()"
     x-on:click.capture="if(moved && $event.detail !== 0) { $event.preventDefault(); $event.stopPropagation(); moved = false; }"
     x-on:resize.window="clamp()" x-on:sa-inbox-count.window="update($event.detail.count)">
    @if($allowed)
        <a href="{{ \App\Filament\Resources\SaConversations\SaConversationResource::getUrl() }}" draggable="false" aria-label="SA-диалоги, непрочитанных: {{ $count }}" class="viar-sa-global-link">
            <x-filament::icon icon="heroicon-s-chat-bubble-left-right"/>
            <span>SA-диалоги</span><strong class="viar-sa-global-count">{{ $count }}</strong>
        </a>
        <button type="button" aria-label="Звук новых SA-обращений" x-bind:aria-pressed="sound" title="Звук новых SA-обращений"
                x-on:click="toggleSound()"
                class="viar-sa-global-sound">
            <x-filament::icon icon="heroicon-o-speaker-wave" style="width:18px;height:18px;" x-show="sound" x-cloak/>
            <x-filament::icon icon="heroicon-o-speaker-x-mark" style="width:18px;height:18px;" x-show="!sound"/>
        </button>
    @endif
</div>
