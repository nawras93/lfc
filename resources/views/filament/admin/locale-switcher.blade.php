@php
    $currentLocale = app()->getLocale();
    $baseClasses = 'rounded-full px-3 py-1 text-sm font-medium transition';
    $activeClasses = 'bg-primary-600 text-white';
    $inactiveClasses = 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-white/10 dark:hover:text-white';
@endphp

<div class="flex items-center gap-2">
    <a
        href="{{ route('admin.locale.switch', ['locale' => 'en']) }}"
        @class([$baseClasses, $currentLocale === 'en' ? $activeClasses : $inactiveClasses])
    >
        EN
    </a>

    <a
        href="{{ route('admin.locale.switch', ['locale' => 'ar']) }}"
        @class([$baseClasses, $currentLocale === 'ar' ? $activeClasses : $inactiveClasses])
    >
        AR
    </a>
</div>
