<svg class="face-illustration" viewBox="0 0 200 240" role="img" aria-label="{{ $guide['shape'] }} face: {{ $mode }} illustration">
<path d="{{ $guide['shape_guide']['outline'] }}" fill="#f2d8c7" stroke="#916c60" stroke-width="2"/>
@if($mode === 'contour')
<path d="{{ $guide['shape_guide']['contour'] }}" fill="#9a6a53" opacity=".65"/>
@elseif($mode === 'blush')
@php($b = $guide['shape_guide']['blush'])
<ellipse cx="{{ $b[0] }}" cy="{{ $b[1] }}" rx="20" ry="10" transform="rotate({{ $b[2] }} {{ $b[0] }} {{ $b[1] }})" fill="#d77987" opacity=".7"/>
<ellipse cx="{{ $b[3] }}" cy="{{ $b[4] }}" rx="20" ry="10" transform="rotate({{ $b[5] }} {{ $b[3] }} {{ $b[4] }})" fill="#d77987" opacity=".7"/>
@elseif($mode === 'highlight')
@if($guide['shape'] !== 'Diamond')<path d="M43 119 Q55 113 73 118 M127 118 Q145 113 157 119" fill="none" stroke="#fff7de" stroke-width="9" stroke-linecap="round"/>@endif
@if(in_array($guide['shape'], ['Heart','Diamond']))<ellipse cx="100" cy="192" rx="8" ry="5" fill="#fff7de"/>
@elseif(in_array($guide['shape'], ['Round','Square','Triangle','Diamond']))<ellipse cx="100" cy="60" rx="13" ry="8" fill="#fff7de"/>@endif
@if($guide['shape'] === 'Diamond')<ellipse cx="100" cy="60" rx="12" ry="8" fill="#fff7de"/>@endif
@endif
<g fill="none" stroke="#785b55" stroke-width="2" stroke-linecap="round"><path d="M49 91 Q62 84 77 90 M123 90 Q138 84 151 91 M51 103 Q64 110 77 102 M123 102 Q136 110 149 103 M99 110 L94 137 Q100 141 106 137 M83 161 Q100 169 117 161"/></g>
</svg>