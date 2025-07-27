@if(isset($languageSwitcherData) && count($languageSwitcherData) > 1)
<div class="language-switcher dropdown">
    <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="languageDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <i class="fas fa-globe"></i>
        {{ $languageSwitcherData[array_search(true, array_column($languageSwitcherData, 'is_current'))]['name'] ?? 'Language' }}
    </button>
    <div class="dropdown-menu" aria-labelledby="languageDropdown">
        @foreach($languageSwitcherData as $language)
            <a class="dropdown-item {{ $language['is_current'] ? 'active' : '' }}" href="{{ $language['url'] }}">
                {{ $language['name'] }}
                @if($language['is_current'])
                    <i class="fas fa-check ml-2"></i>
                @endif
            </a>
        @endforeach
    </div>
</div>
@endif 