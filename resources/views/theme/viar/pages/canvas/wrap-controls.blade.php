<fieldset id="canvas-wrap-controls" class="canvas-wrap-controls"
    data-wrap-notice="{{ trans('calculator.wrap_notice') }}"
    data-upload-error="{{ trans('calculator.upload_error') }}"
    data-crop-message="{{ trans('canvas_wrap.crop') }}"
    data-zoom-message="{{ trans('canvas_wrap.zoom') }}"
    data-recommend-message="{{ trans('canvas_wrap.recommend') }}">
    <legend>{{ trans('canvas_wrap.title') }}</legend>
    <div class="edge-options">
    @foreach (array_merge(['wrap','mirror','solid'], config('canvas_ai.enabled') ? ['ai'] : []) as $mode)
        @php($locked = $mode === 'ai' && auth()->guest())
        <label class="edge-choice" title="{{ trans('canvas_wrap.'.$mode.'_description') }}">
            @if($locked)
            <button type="button" class="edge-auth-trigger js-popup-login jcf-ignore" aria-haspopup="dialog" aria-label="{{ trans('canvas_wrap.ai_label') }} — {{ trans('canvas_ai.login') }}"></button>
            @else
            <input class="jcf-ignore" type="radio" name="canvas_edge_mode" value="{{ $mode }}" aria-describedby="edge-mode-description" @if($mode==='wrap') checked @endif >
            @endif
            <span class="edge-tile" aria-hidden="true">
                @if($mode === 'mirror')
                <svg viewBox="0 0 36 32" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" focusable="false"><rect x="2" y="7" width="28" height="22" rx="2"/><path d="M22 8v20" stroke-dasharray="2 3"/><path d="M3 24l8-8 11 8 7-7"/><path d="m17 11 2 2-2 2m10-4-2 2 2 2"/></svg>
                @elseif($mode === 'solid')
                <svg viewBox="0 0 36 32" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" focusable="false"><rect x="2" y="7" width="28" height="22" rx="2"/><path d="M24 8h5v20h-5z" fill="currentColor" stroke="none"/><path d="M21 8v20" stroke-dasharray="2 3"/><path d="m3 24 8-8 7 8"/></svg>
                @elseif($mode === 'wrap')
                <svg viewBox="0 0 36 32" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" focusable="false"><rect x="2" y="7" width="28" height="22" rx="2"/><path d="M22 8v20" stroke-dasharray="2 3"/><path d="m3 24 8-8 11 8 4-4 3 4M21 3h8m-3-2 3 2-3 2"/></svg>
                @elseif($mode === 'ai')
                <svg viewBox="0 0 36 32" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" focusable="false"><path d="M21 7H4a2 2 0 0 0-2 2v18a2 2 0 0 0 2 2h17"/><path d="M22 8v21h8V16" stroke-dasharray="2 3"/><path d="m3 24 8-8 8 8"/><path d="m27 2 1.5 4.5L33 8l-4.5 1.5L27 14l-1.5-4.5L21 8l4.5-1.5z"/></svg>
                @endif
                @if($locked)<svg class="edge-lock" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="7" width="10" height="7" rx="2"/><path d="M5 7V5a3 3 0 0 1 6 0v2"/></svg>@endif
            </span>
            <span class="edge-caption">{{ trans('canvas_wrap.'.$mode.'_label') }}</span>
        </label>
    @endforeach
    </div>
    @foreach (array_merge(['wrap','mirror','solid'], config('canvas_ai.enabled') ? ['ai'] : []) as $mode)
        <span hidden data-edge-description="{{ $mode }}">{{ trans('canvas_wrap.'.$mode.'_description') }}</span>
    @endforeach
    <p id="edge-mode-description" hidden>{{ trans('canvas_wrap.wrap_description') }}</p>
    @if (config('canvas_ai.enabled'))
        @auth
            <div data-ai-panel hidden data-csrf="{{ csrf_token() }}"
                @foreach (['upload','waiting','changed','error','limit','quota','rate','timeout','login','unavailable','review','applied','moderation','ready','fee'] as $aiMessage)
                    data-{{ $aiMessage }}="{{ trans('canvas_ai.'.$aiMessage) }}"
                @endforeach
            >
                <p>{{ trans('canvas_ai.notice') }}</p>
                <button class="jcf-ignore" type="button" data-ai-generate>{{ trans('canvas_ai.generate') }}</button>
                <p data-ai-status role="status" aria-live="polite"></p>
                <img data-ai-preview hidden alt="{{ trans('canvas_ai.preview') }}" style="max-width:240px;width:100%;height:auto">
                <button class="jcf-ignore" type="button" data-ai-apply hidden>{{ trans('canvas_ai.apply') }}</button>
            </div>

        @endauth
    @endif
    <p data-crop-warning role="status" aria-live="polite" hidden></p>
</fieldset>
@if (config('canvas_ai.enabled'))
<script defer src="{{ ver_asset(config('theme.current').'js/canvas-outpaint.js') }}"></script>
@endif
<style>
    @if(config('canvas_ui.new_calculator', false))
    #generate .accordion__sizes-block .kviz-c-group { display:grid !important; grid-template-columns:repeat(2,minmax(0,1fr)); gap:10px; margin:0; }
    #generate .accordion__sizes-block .kviz-group__item { display:contents !important; }
    #generate .accordion__sizes-block .calcSize { position:relative; box-sizing:border-box; width:100%; min-width:0; height:116px; margin:0; padding:38px 10px 10px; border:1px solid #d8ccc5; border-radius:10px; background:white; cursor:pointer; }
    #generate .accordion__sizes-block .calcSize.kviz-radio_active { border-color:#ff7952; box-shadow:inset 0 0 0 1px #ff7952; background:#fff0e7; }
    #generate .accordion__sizes-block .calcSize label { display:block; margin:0; padding:0; pointer-events:none; }
    #generate .accordion__sizes-block .calcSize input[name="size"] { position:absolute; top:10px; right:10px; width:20px; height:20px; opacity:0; pointer-events:auto; }
    #generate .accordion__sizes-block .size-card-name { display:block; font-size:16px; font-weight:600; line-height:1.4; color:#26313d; }
    #generate .accordion__sizes-block .size-card-prices { display:flex; flex-wrap:wrap; gap:6px; align-items:baseline; font-size:15px; line-height:1.5; }
    #generate .accordion__sizes-block .size-card-prices del { color:#62636a; font-size:13px; }
    #generate .accordion__sizes-block .size-card-prices b { color:#e65e35; font-weight:700; }
    #generate .accordion__sizes-block .calcSize > .price_label { position:absolute; top:10px; left:14px; margin:0; }
    #generate .accordion__sizes-block .calcSize > .check { position:absolute; top:10px; right:10px; left:auto; margin:0; width:20px; height:20px; }
    #generate .accordion__sizes-block .calcSize.kviz-radio_active > .check::after { content:"✓"; position:absolute; inset:0; display:grid; place-items:center; color:#fff; background:#ff7952; border-radius:50%; font-size:14px; line-height:20px; }
    #generate .accordion__sizes-block .calcSize:focus-within { outline:2px solid #2563eb; outline-offset:2px; }
    @endif
    #generate #imgs.pd-canvas { margin-left:0; margin-right:0; width:100%; max-width:100%; overflow:visible; box-sizing:border-box; }
    #generate #imgs .loader-canvas, #generate #imgs .file-save { width:100%; max-width:100%; box-sizing:border-box; }
    @media (min-width: 900px) {
        body.vz-canvas { overflow-x: clip; overflow-y: visible; }
        .retouch__screen:has(.canvas-workspace) { overflow: clip; }
        #generate .canvas-workspace-runway { position: relative; width: 100%; }
        #generate .canvas-workspace { position: sticky; top: var(--workspace-top, 110px); display: flex; align-items: flex-start; height: calc(100dvh - var(--workspace-top, 110px) - 10px); min-height: 480px; scroll-margin-top: var(--workspace-top, 110px); }
        #generate .canvas-workspace > .photo-filter { height: 100%; min-height: 0; overflow-y: auto; overflow-x: hidden; overscroll-behavior-y: contain; scrollbar-gutter: stable; padding-right: 12px; }
        #generate .canvas-workspace > .slider-tabs { max-height: 100%; overflow-y: auto; overscroll-behavior-y: contain; min-height: 0; scrollbar-width: none; }
        #generate .canvas-workspace > .slider-tabs::-webkit-scrollbar { display: none; width: 0; }
        #generate .canvas-workspace #PhotoEditor .canvas_container { width: min(100%, calc(100dvh - 350px)); margin: auto; flex: 0 1 auto; }
        #generate .canvas-workspace #PhotoEditor { justify-content: center; }
        #generate .canvas-workspace .filter-accordion > .accordion-title:first-child { position: sticky; top: 0; z-index: 4; margin-top: 0; background: #fff; }
        #generate .canvas-workspace .filter-accordion > .accordion-title:first-child + .accordion-content { position: sticky; top: 52px; z-index: 3; background: #fbf2e9; }
        #generate .canvas-workspace #imgs, #generate .canvas-workspace .loader-canvas, #generate .canvas-workspace .file-save { max-width: 100%; transform: none; }
        #generate .canvas-workspace .calcSize { max-width: 100%; white-space: normal; }
        #generate .canvas-workspace .calcSize label { white-space: normal; }
        #generate .canvas-workspace .accordion__sizes-block .kviz-c-group { display: block; }
        #generate .canvas-workspace .accordion__sizes-block .kviz-group__item { width: 100%; }
    }
    .canvas-wrap-controls { border: 0; padding: 12px; margin: 0; min-width: 0; font: inherit; }
    .canvas-wrap-controls legend { font-weight: 600; }
    .canvas-wrap-controls .edge-options { display:flex; gap:10px; align-items:flex-start; margin:12px 0; }
    .canvas-wrap-controls .edge-choice { position:relative; display:flex; flex:0 1 72px; min-width:0; flex-direction:column; align-items:center; gap:6px; margin:0; cursor:pointer; color:#26313d; }
    .canvas-wrap-controls .edge-choice input { position:absolute; inset:0; width:100%; height:100%; opacity:0; margin:0; cursor:pointer; }
    .canvas-wrap-controls .edge-tile { position:relative; box-sizing:border-box; width:64px; max-width:100%; height:60px; display:flex; align-items:center; justify-content:center; border:1.5px solid #bdaba3; border-radius:10px; background:#fff; pointer-events:none; }
    .canvas-wrap-controls .edge-tile > svg { width:32px; height:32px; }
    .canvas-wrap-controls .edge-caption { font-size:11px; line-height:1.3; text-align:center; hyphens:auto; pointer-events:none; }
    .canvas-wrap-controls input:checked + .edge-tile { border-color:#ff7952; background:#fff0e7; box-shadow:0 0 0 1px #ff7952; }
    .canvas-wrap-controls input:focus-visible + .edge-tile { outline:3px solid #2563eb; outline-offset:3px; }
    .canvas-wrap-controls .edge-tile > .edge-lock { position:absolute; right:3px; bottom:3px; width:13px; height:13px; background:#fff; }
    .canvas-wrap-controls .edge-description, .canvas-wrap-controls .edge-access { font-size:14px; line-height:1.5; margin:8px 0; }
    .canvas-wrap-controls .edge-auth-trigger { position:absolute; inset:0; width:100%; height:100%; opacity:0; margin:0; padding:0; border:0; cursor:pointer; z-index:1; }
    .canvas-wrap-controls .edge-auth-trigger:focus-visible + .edge-tile { outline:3px solid #2563eb; outline-offset:3px; }
    .canvas-wrap-controls [data-crop-warning] { padding: 12px; background: #fff2ee; border-left: 3px solid #ff7952; line-height: 1.5; }
    .canvas-wrap-controls [hidden] { display: none; }
    .canvas-wrap-controls [data-ai-panel] { line-height: 1.5; padding-top: 8px; }
    .canvas-wrap-controls [data-ai-panel] button { display: inline-block; min-height: 44px; padding: 10px 16px; border: 0; border-radius: 8px; background: #ff7952; color: white; cursor: pointer; margin: 8px 0; }
    .canvas-wrap-controls [data-ai-panel] button:disabled { opacity: .5; cursor: wait; }
    .canvas-wrap-controls [data-ai-panel] button[hidden] { display: none; }
    @media (min-width: 1024px) {
        .canvas-wrap-controls { padding-left: 32px; }
    }
    @media (max-width: 475px) {
        #tab_screen1 #PhotoEditor { flex-direction: column; }
        #tab_screen1 #PhotoEditor .tools { display: grid; grid-template-columns: repeat(4, 1fr); gap: 4px; width: 100%; padding: 4px 0; }
        #tab_screen1 #PhotoEditor .tools .tool { display: flex; align-items: center; justify-content: center; float: none; width: 100%; height: 44px; margin: 0; }
    }
</style>

