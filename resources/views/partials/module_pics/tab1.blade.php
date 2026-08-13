<div class="modular tabs-item tabs-item1 active" id="mtab1">
    <table id="tab__table">
        <tr>
            <td>&nbsp;</td>
            <td>
                <div class="h-ruler"></div>
            </td>
            <td></td>
        </tr>
        <tr>
            <td>
                <div class="v-ruler"></div>
            </td>
            <td class="canv_cel" id="canv_cell_td">


                <svg id="msvg">
                    <rect width="100%" height="100%" fill="none" />
                    <pattern id="image" patternUnits="userSpaceOnUse" width="100%" height="100%">
                        <image preserveAspectRatio="xMidYMid slice" width="100%" height="100%" />
                    </pattern>
                    <g></g>
                </svg>


            </td>
            <td class="controls">
                <button title="Разделить блок вертикально" class="split_v disabled"
                    style="background-image: url({{ asset('img/split_v.svg') }})"></button>
                <button title="Разделить блок горизонтально" class="split_h disabled"
                    style="background-image: url({{ asset('img/split_v.svg') }}); transform: rotate(90deg)"></button>
                <button title="Удалить блок" class="delete disabled"
                    style="background-image: url({{ asset('img/collage7.png') }})"></button>
            </td>
        </tr>
    </table>
    <h4 class="sum__mod"><span id="glob_summ2">0</span> €</h4>
</div>
