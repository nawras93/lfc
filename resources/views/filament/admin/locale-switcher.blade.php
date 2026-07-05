@php
    $currentLocale = app()->getLocale();
    // Flag-only toggle mirroring the public site & mobile app: the flag shown is
    // the language you'll switch **to** — Qatar (Arabic) while in English,
    // Great Britain (English) while in Arabic.
    $targetLocale = $currentLocale === 'ar' ? 'en' : 'ar';
    $targetFlag = $currentLocale === 'ar' ? 'gb' : 'qa';
    $targetLabel = $currentLocale === 'ar' ? 'English' : 'العربية';
@endphp

<a
    href="{{ route('admin.locale.switch', ['locale' => $targetLocale]) }}"
    class="fi-admin-locale-switch"
    aria-label="{{ $targetLabel }}"
    title="{{ $targetLabel }}"
>
    <img
        class="fi-admin-locale-flag"
        src="{{ asset('images/flags/' . $targetFlag . '.png') }}"
        alt="{{ $targetLabel }}"
    >
</a>
