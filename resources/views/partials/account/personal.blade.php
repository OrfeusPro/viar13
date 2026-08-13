<h3>@lang('account.index6')</h3>
<div id="AccountFormChangePasswordAndContact">
    <div class="form-items">
        <h4>@lang('account.index38')</h4>
        <label>@lang('account.index39')</label>
        <input type="password" name="old_password">
        <div class="error" name="old_password"></div>

        <label>@lang('account.index40')</label>
        <input type="password" name="password">
        <div class="error" name="password"></div>

        <label>@lang('account.index41')</label>
        <input type="password" name="password_confirmation">
        <div class="error" name="password_confirmation"></div>
    </div>
    <div class="form-items">
        <h4>@lang('account.index42')</h4>
        <label>@lang('account.index43')</label>
        <input value="{{ Auth::user()->last_name }}" name="last_name" type="text" value="">
        <div class="error" name="last_name"></div>

        <label>@lang('account.index44')</label>
        <input value="{{ Auth::user()->first_name }}" name="first_name" type="text">
        <div class="error" name="first_name"></div>

        <label>@lang('account.index45')</label>
        <input class="phone_no_mask" value="{{ Auth::user()->phone }}" name="phone" type="text">
        <div class="error" name="phone"></div>
    </div>
    <div class="form-items">
        <div class="input-checkbox">
            <label for="q">@lang('account.index46')</label>
            <input id="q" name="news" @if(Auth::user()->news == 'YES') checked @endif type="checkbox">
        </div>
        <div class="input-checkbox">
            <label for="w">@lang('account.index47')</label>
            <input id="w" name="ad" @if(Auth::user()->ad == 'YES') checked @endif type="checkbox">
        </div>
    </div>
    <div class="form-items">
        <div class="input-checkbox">
            <label for="e">@lang('account.index48')</label>
            <input id="e" name="client_data" @if(Auth::user()->client_data == 'YES') checked
            @endif type="checkbox">
        </div>
    </div>
    <button><span>@lang('account.index49')</span></button>
    </form>
</div>