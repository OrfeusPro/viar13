@extends('voyager::master')

@section('page_title', 'SA Conversations')

@section('content')
<div class="page-content browse container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-bordered">
                <div class="panel-heading" style="display:flex; justify-content:space-between; align-items:center;">
                    <h3 class="panel-title">
                        <i class="voyager-chat"></i> SA Conversations
                    </h3>
                    <a href="{{ route('admin.sa.simulator') }}" class="btn btn-sm btn-primary">SA Simulator</a>
                </div>
                <div class="panel-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <form method="GET" action="{{ route('admin.sa.conversations.index') }}" class="row" style="margin-bottom:15px;">
                        <div class="col-md-5">
                            <input type="text" name="q" class="form-control" value="{{ $q }}" placeholder="conversation_id / phone / name / order id">
                        </div>
                        <div class="col-md-3">
                            <select name="scope" class="form-control">
                                <option value="">Все диалоги</option>
                                <option value="unread" {{ $scope === 'unread' ? 'selected' : '' }}>Только непрочитанные</option>
                                <option value="unlinked" {{ $scope === 'unlinked' ? 'selected' : '' }}>Только без заказа</option>
                                <option value="awaiting_reply" {{ $scope === 'awaiting_reply' ? 'selected' : '' }}>Ожидают ответа менеджера</option>
                                <option value="recent" {{ $scope === 'recent' ? 'selected' : '' }}>Новые за 24 часа</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-primary" type="submit">Фильтр</button>
                            <a href="{{ route('admin.sa.conversations.index') }}" class="btn btn-default">Сбросить</a>
                        </div>
                    </form>

                    <div
                        id="saConversationsTableWrap"
                        data-fragment-url="{{ route('admin.sa.conversations.index') }}"
                        data-state-url="{{ route('admin.sa.conversations.unread_state') }}"
                        data-scope="{{ $scope }}"
                        data-q="{{ $q }}"
                    >
                        @include('admin.partials.sa_conversations_table', ['conversations' => $conversations])
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('javascript')
<script>
    (function () {
        var wrap = document.getElementById('saConversationsTableWrap');
        if (!wrap) {
            return;
        }

        var fragmentUrl = wrap.getAttribute('data-fragment-url');
        var stateUrl = wrap.getAttribute('data-state-url');
        var scope = wrap.getAttribute('data-scope') || '';
        var q = wrap.getAttribute('data-q') || '';
        var lastStateFingerprint = null;
        var isRefreshing = false;

        function buildStateFingerprint(payload) {
            if (!payload || !payload.data) {
                return '';
            }
            return [
                payload.data.unread_count || 0,
                payload.data.latest_message_at || '',
                payload.data.latest_unread_conversation_id || ''
            ].join('|');
        }

        function refreshTable() {
            if (isRefreshing) {
                return;
            }

            isRefreshing = true;
            $.get(fragmentUrl, {
                fragment: 1,
                scope: scope,
                q: q
            }).done(function (html) {
                wrap.innerHTML = html;
            }).always(function () {
                isRefreshing = false;
            });
        }

        function refreshIfNeeded(payload) {
            var nextFingerprint = buildStateFingerprint(payload);
            if (!nextFingerprint) {
                return;
            }

            if (lastStateFingerprint === null) {
                lastStateFingerprint = nextFingerprint;
                return;
            }

            if (lastStateFingerprint !== nextFingerprint) {
                lastStateFingerprint = nextFingerprint;
                refreshTable();
            }
        }

        $(function () {
            $.getJSON(stateUrl).done(function (payload) {
                lastStateFingerprint = buildStateFingerprint(payload);
            });
        });

        window.addEventListener('sa-conversations-state-updated', function (event) {
            refreshIfNeeded((event && event.detail) || null);
        });
    })();
</script>
@endsection
