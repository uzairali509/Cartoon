<?php
/* includes/scenes.php — every illustration in the Cartoon Universe is drawn
   right here, on the server, as inline SVG. cu_scene('fox') returns the
   artwork PHP prints straight into the page. */

function cu_spark($x, $y, $s, $f = '#FFFDF7', $o = 0.9)
{
    $pts = [];
    foreach ([[0, -1], [.26, -.26], [1, 0], [.26, .26], [0, 1], [-.26, .26], [-1, 0], [-.26, -.26]] as $d) {
        $pts[] = round($x + $d[0] * $s, 2) . ' ' . round($y + $d[1] * $s, 2);
    }
    $d = 'M' . implode(' L', $pts) . ' Z';
    return "<path d=\"{$d}\" fill=\"{$f}\" opacity=\"{$o}\"/>";
}

function cu_face($x, $y, $s = 1)
{
    $r = 5.5 * $s;
    $gr = 1.8 * $s;
    $br = 5 * $s;
    $lx = $x - 13 * $s;
    $rx = $x + 13 * $s;
    $g1 = $x - 11 * $s;
    $g2 = $x + 15 * $s;
    $gy = $y - 2 * $s;
    $b1 = $x - 25 * $s;
    $b2 = $x + 25 * $s;
    $by = $y + 8 * $s;
    return "<circle cx=\"{$lx}\" cy=\"{$y}\" r=\"{$r}\" fill=\"#33261D\"/>"
        . "<circle cx=\"{$rx}\" cy=\"{$y}\" r=\"{$r}\" fill=\"#33261D\"/>"
        . "<circle cx=\"{$g1}\" cy=\"{$gy}\" r=\"{$gr}\" fill=\"#FFF\"/>"
        . "<circle cx=\"{$g2}\" cy=\"{$gy}\" r=\"{$gr}\" fill=\"#FFF\"/>"
        . "<circle cx=\"{$b1}\" cy=\"{$by}\" r=\"{$br}\" fill=\"#FF5A3C\" opacity=\".38\"/>"
        . "<circle cx=\"{$b2}\" cy=\"{$by}\" r=\"{$br}\" fill=\"#FF5A3C\" opacity=\".38\"/>";
}

function cu_smile($x, $y, $w = 8, $sw = 3.4)
{
    $a = $x - $w;
    $b = $x + $w;
    $c = $y + $w * 0.7;
    return "<path d=\"M{$a} {$y} Q{$x} {$c} {$b} {$y}\" stroke=\"#33261D\" stroke-width=\"{$sw}\" fill=\"none\" stroke-linecap=\"round\"/>";
}

/* ---------- reusable character parts ---------- */

function cu_fox_head()
{
    static $h = null;
    if ($h !== null)
        return $h;
    $face = cu_face(100, 128, 1);
    $h = <<<SVG
<path d="M46 86 L58 22 L94 68 Z" fill="#FF9C63" stroke="#33261D" stroke-width="5" stroke-linejoin="round"/>
<path d="M154 86 L142 22 L106 68 Z" fill="#FF9C63" stroke="#33261D" stroke-width="5" stroke-linejoin="round"/>
<path d="M58 74 L65 42 L79 64 Z" fill="#FFB4A2"/><path d="M142 74 L135 42 L121 64 Z" fill="#FFB4A2"/>
<ellipse cx="100" cy="134" rx="64" ry="57" fill="#FF9C63" stroke="#33261D" stroke-width="5"/>
<ellipse cx="100" cy="158" rx="33" ry="22" fill="#FFFDF7"/>
{$face}
<path d="M93 149 L107 149 L100 159 Z" fill="#33261D"/>
SVG;
    $h .= cu_smile(100, 163, 7);
    return $h;
}

function cu_bunny_head()
{
    static $h = null;
    if ($h !== null)
        return $h;
    $face = cu_face(100, 142, 1);
    $h = <<<SVG
<g transform="rotate(-9 100 90)">
<rect x="62" y="20" width="27" height="84" rx="13.5" fill="#FFFDF7" stroke="#33261D" stroke-width="5"/>
<rect x="69" y="32" width="13" height="58" rx="6.5" fill="#FFB4A2"/>
</g>
<g transform="rotate(9 100 90)">
<rect x="111" y="20" width="27" height="84" rx="13.5" fill="#FFFDF7" stroke="#33261D" stroke-width="5"/>
<rect x="118" y="32" width="13" height="58" rx="6.5" fill="#FFB4A2"/>
</g>
<ellipse cx="100" cy="150" rx="57" ry="51" fill="#FFFDF7" stroke="#33261D" stroke-width="5"/>
{$face}
<path d="M94 160 L106 160 L100 169 Z" fill="#FF7B9C"/>
<path d="M94 174 Q100 181 106 174" stroke="#33261D" stroke-width="3.2" fill="none" stroke-linecap="round"/>
SVG;
    return $h;
}

function cu_star_body()
{
    static $b = null;
    if ($b !== null)
        return $b;
    $face = cu_face(100, 106, .85);
    $b = <<<SVG
<path d="M100 42 L119 88 L169 92 L131 124 L143 172 L100 146 L57 172 L69 124 L31 92 L81 88 Z"
  fill="#FFFDF7" stroke="#33261D" stroke-width="6" stroke-linejoin="round"/>
{$face}
SVG;
    $b .= cu_smile(100, 122, 6.5, 3);
    return $b;
}

/* ---------- the 13 scenes ---------- */

function cu_scene_fox()
{
    $s1 = cu_spark(30, 44, 10);
    $s2 = cu_spark(172, 62, 8);
    $s3 = cu_spark(158, 216, 7, '#FFFDF7', .8);
    $fx = cu_fox_head();
    return <<<SVG
<rect width="200" height="250" fill="#FF9C63"/>
<ellipse cx="100" cy="238" rx="96" ry="36" fill="#FFC38C" opacity=".55"/>
{$s1}{$s2}{$s3}{$fx}
SVG;
}

function cu_scene_rocket()
{
    $s1 = cu_spark(34, 48, 9);
    $s2 = cu_spark(170, 92, 7);
    $s3 = cu_spark(26, 186, 6);
    return <<<SVG
<rect width="200" height="250" fill="#C8EFE4"/>
{$s1}{$s2}{$s3}
<circle cx="38" cy="126" r="4" fill="#FFFDF7"/><circle cx="56" cy="216" r="4" fill="#FFFDF7"/><circle cx="152" cy="30" r="3.5" fill="#FFFDF7"/>
<circle cx="162" cy="200" r="21" fill="#FFC531" stroke="#33261D" stroke-width="4"/>
<ellipse cx="162" cy="200" rx="32" ry="9" fill="none" stroke="#FF5A3C" stroke-width="5" transform="rotate(-18 162 200)"/>
<path d="M79 100 Q100 20 121 100 Z" fill="#FF5A3C" stroke="#33261D" stroke-width="5" stroke-linejoin="round"/>
<rect x="79" y="92" width="42" height="84" rx="20" fill="#FFFDF7" stroke="#33261D" stroke-width="5"/>
<circle cx="100" cy="124" r="13" fill="#7ADCD0" stroke="#33261D" stroke-width="4.5"/>
<path d="M79 150 Q54 168 59 194 L79 176 Z" fill="#FF5A3C" stroke="#33261D" stroke-width="5" stroke-linejoin="round"/>
<path d="M121 150 Q146 168 141 194 L121 176 Z" fill="#FF5A3C" stroke="#33261D" stroke-width="5" stroke-linejoin="round"/>
<path d="M87 176 Q100 220 113 176 Z" fill="#FFC531" stroke="#33261D" stroke-width="4" stroke-linejoin="round"/>
<path d="M92 176 Q100 198 108 176 Z" fill="#FF5A3C"/>
SVG;
}

function cu_scene_cactus()
{
    $s1 = cu_spark(30, 44, 9);
    $s2 = cu_spark(172, 62, 7);
    $face = cu_face(100, 118, .9);
    $sm = cu_smile(100, 138, 6.5);
    return <<<SVG
<rect width="200" height="250" fill="#FBE3BD"/>
{$s1}{$s2}
<path d="M64 196 h72 l-9 40 h-54 Z" fill="#FF5A3C" stroke="#33261D" stroke-width="5" stroke-linejoin="round"/>
<rect x="56" y="186" width="88" height="16" rx="8" fill="#FF8B6B" stroke="#33261D" stroke-width="5"/>
<ellipse cx="100" cy="126" rx="55" ry="60" fill="#3FA85C" stroke="#33261D" stroke-width="5"/>
<path d="M82 82 Q74 126 82 170" stroke="#2E8A4C" stroke-width="5" fill="none" stroke-linecap="round"/>
<path d="M118 82 Q126 126 118 170" stroke="#2E8A4C" stroke-width="5" fill="none" stroke-linecap="round"/>
{$face}{$sm}
<circle cx="100" cy="62" r="9" fill="#FFC531" stroke="#33261D" stroke-width="3.5"/>
<circle cx="86" cy="70" r="8" fill="#FFC531" stroke="#33261D" stroke-width="3.5"/>
<circle cx="114" cy="70" r="8" fill="#FFC531" stroke="#33261D" stroke-width="3.5"/>
<circle cx="100" cy="76" r="7" fill="#FFC531" stroke="#33261D" stroke-width="3.5"/>
SVG;
}

function cu_scene_bear()
{
    $s1 = cu_spark(30, 44, 9);
    $s2 = cu_spark(172, 52, 7);
    $face = cu_face(100, 128, 1);
    $sm = cu_smile(100, 166, 6.5);
    return <<<SVG
<rect width="200" height="250" fill="#F2E1BE"/>
<ellipse cx="100" cy="238" rx="90" ry="34" fill="#E2CB98"/>
{$s1}{$s2}
<circle cx="56" cy="70" r="24" fill="#C08552" stroke="#33261D" stroke-width="5"/>
<circle cx="144" cy="70" r="24" fill="#C08552" stroke="#33261D" stroke-width="5"/>
<circle cx="56" cy="70" r="11" fill="#EFC9A0"/><circle cx="144" cy="70" r="11" fill="#EFC9A0"/>
<ellipse cx="100" cy="134" rx="62" ry="56" fill="#C08552" stroke="#33261D" stroke-width="5"/>
<ellipse cx="100" cy="158" rx="30" ry="21" fill="#FFFDF7"/>
{$face}
<ellipse cx="100" cy="150" rx="8.5" ry="6.5" fill="#33261D"/>{$sm}
SVG;
}

function cu_scene_icecream()
{
    $s1 = cu_spark(32, 44, 9);
    $s2 = cu_spark(170, 70, 7);
    $s3 = cu_spark(148, 206, 6);
    $face = cu_face(100, 104, .7);
    return <<<SVG
<rect width="200" height="250" fill="#C8EFE4"/>
{$s1}{$s2}{$s3}
<path d="M66 146 L134 146 L100 232 Z" fill="#E8B36B" stroke="#33261D" stroke-width="5" stroke-linejoin="round"/>
<path d="M79 168 L113 168 M87 190 L105 190 M92 208 L100 208" stroke="#33261D" stroke-width="3" opacity=".35" stroke-linecap="round"/>
<circle cx="84" cy="122" r="23" fill="#7ADCD0" stroke="#33261D" stroke-width="5"/>
<circle cx="116" cy="122" r="23" fill="#FFB4A2" stroke="#33261D" stroke-width="5"/>
<circle cx="100" cy="104" r="24" fill="#FFFDF7" stroke="#33261D" stroke-width="5"/>
<circle cx="100" cy="72" r="10" fill="#FF5A3C" stroke="#33261D" stroke-width="4"/>
<path d="M100 62 Q104 50 112 46" stroke="#33261D" stroke-width="3.5" fill="none" stroke-linecap="round"/>
{$face}
<path d="M95 118 Q100 122 105 118" stroke="#33261D" stroke-width="2.8" fill="none" stroke-linecap="round"/>
SVG;
}

function cu_scene_balloon()
{
    $s1 = cu_spark(28, 44, 9);
    $s2 = cu_spark(174, 70, 7);
    $face = cu_face(100, 88, .65);
    return <<<SVG
<rect width="200" height="250" fill="#FBE3BD"/>
{$s1}{$s2}
<circle cx="100" cy="92" r="52" fill="#FF5A3C" stroke="#33261D" stroke-width="5"/>
<path d="M86 44 Q100 92 86 140" stroke="#FFFDF7" stroke-width="9" fill="none" opacity=".9" stroke-linecap="round"/>
<path d="M114 44 Q100 92 114 140" stroke="#FFFDF7" stroke-width="9" fill="none" opacity=".9" stroke-linecap="round"/>
{$face}
<path d="M85 142 L91 162 M115 142 L109 162" stroke="#33261D" stroke-width="3.5" stroke-linecap="round"/>
<rect x="84" y="160" width="32" height="24" rx="7" fill="#C08552" stroke="#33261D" stroke-width="4.5"/>
<ellipse cx="38" cy="204" rx="24" ry="13" fill="#FFFDF7"/>
<ellipse cx="165" cy="52" rx="20" ry="11" fill="#FFFDF7"/>
<path d="M148 214 q6 -8 12 0 q6 -8 12 0" stroke="#33261D" stroke-width="3" fill="none" stroke-linecap="round"/>
SVG;
}

function cu_scene_burger()
{
    $s1 = cu_spark(30, 40, 9);
    $s2 = cu_spark(170, 60, 7);
    $s3 = cu_spark(160, 214, 6);
    $face = cu_face(100, 108, .9);
    return <<<SVG
<rect width="200" height="250" fill="#C8EFE4"/>
{$s1}{$s2}{$s3}
<path d="M48 128 Q48 62 100 62 Q152 62 152 128 Z" fill="#E8A34B" stroke="#33261D" stroke-width="5"/>
<ellipse cx="76" cy="92" rx="5" ry="7" fill="#FFFDF7" transform="rotate(-18 76 92)"/>
<ellipse cx="100" cy="84" rx="5" ry="7" fill="#FFFDF7"/>
<ellipse cx="124" cy="94" rx="5" ry="7" fill="#FFFDF7" transform="rotate(16 124 94)"/>
{$face}
<path d="M44 128 q7 13 14 0 q7 13 14 0 q7 13 14 0 q7 13 14 0 q7 13 14 0 q7 13 14 0 q7 13 14 0 q7 13 14 0"
  fill="#7FCF6F" stroke="#33261D" stroke-width="4" stroke-linejoin="round"/>
<rect x="54" y="138" width="92" height="9" fill="#FFC531" stroke="#33261D" stroke-width="3.5"/>
<rect x="68" y="145" width="12" height="13" rx="6" fill="#FFC531" stroke="#33261D" stroke-width="3"/>
<rect x="118" y="145" width="12" height="13" rx="6" fill="#FFC531" stroke="#33261D" stroke-width="3"/>
<rect x="52" y="156" width="96" height="17" rx="8.5" fill="#8A5A38" stroke="#33261D" stroke-width="4.5"/>
<path d="M52 187 h96 v6 q0 22 -24 22 h-48 q-24 0 -24 -22 Z" fill="#E8A34B" stroke="#33261D" stroke-width="5"/>
SVG;
}

function cu_scene_cat()
{
    $s1 = cu_spark(32, 46, 9);
    $s2 = cu_spark(170, 196, 7);
    return <<<SVG
<rect width="200" height="250" fill="#A8E0D2"/>
{$s1}{$s2}
<path d="M50 88 L60 30 L102 68 Z" fill="#4E453F" stroke="#33261D" stroke-width="5" stroke-linejoin="round"/>
<path d="M150 88 L140 30 L98 68 Z" fill="#4E453F" stroke="#33261D" stroke-width="5" stroke-linejoin="round"/>
<path d="M61 78 L67 46 L86 64 Z" fill="#FFB4A2"/><path d="M139 78 L133 46 L114 64 Z" fill="#FFB4A2"/>
<rect x="40" y="66" width="120" height="94" rx="46" fill="#4E453F" stroke="#33261D" stroke-width="5"/>
<path d="M76 122 q10 -10 20 0" stroke="#FFFDF7" stroke-width="5" fill="none" stroke-linecap="round"/>
<path d="M104 122 q10 -10 20 0" stroke="#FFFDF7" stroke-width="5" fill="none" stroke-linecap="round"/>
<ellipse cx="100" cy="148" rx="24" ry="13" fill="#FFFDF7"/>
<path d="M95 142 L105 142 L100 149 Z" fill="#FF5A3C"/>
<path d="M95 152 Q100 156 105 152" stroke="#33261D" stroke-width="2.8" fill="none" stroke-linecap="round"/>
<path d="M42 138 L68 144 M42 154 L68 150" stroke="#33261D" stroke-width="3" stroke-linecap="round"/>
<path d="M158 138 L132 144 M158 154 L132 150" stroke="#33261D" stroke-width="3" stroke-linecap="round"/>
<circle cx="70" cy="160" r="6" fill="#FFB4A2" opacity=".6"/><circle cx="130" cy="160" r="6" fill="#FFB4A2" opacity=".6"/>
SVG;
}

function cu_scene_controller()
{
    $s1 = cu_spark(30, 44, 9);
    $s2 = cu_spark(172, 58, 7);
    return <<<SVG
<rect width="200" height="250" fill="#FFE9C3"/>
{$s1}{$s2}
<rect x="46" y="98" width="108" height="62" rx="30" fill="#FFFDF7" stroke="#33261D" stroke-width="5"/>
<path d="M74 110 h12 v10 h10 v12 h-10 v10 h-12 v-10 h-10 v-12 h10 Z" fill="#33261D"/>
<circle cx="122" cy="118" r="7" fill="#FF5A3C"/>
<circle cx="138" cy="130" r="7" fill="#12A594"/>
<circle cx="118" cy="136" r="4.5" fill="#33261D"/>
<path d="M100 160 q0 34 -26 44" stroke="#33261D" stroke-width="4" fill="none" stroke-linecap="round"/>
<circle cx="66" cy="208" r="7" fill="none" stroke="#33261D" stroke-width="4"/>
SVG;
}

function cu_scene_rainbow()
{
    $s1 = cu_spark(170, 66, 9);
    $s2 = cu_spark(160, 150, 6);
    return <<<SVG
<rect width="200" height="250" fill="#FBE3BD"/>
<circle cx="34" cy="58" r="13" fill="#FFC531" stroke="#33261D" stroke-width="4"/>
{$s1}{$s2}
<path d="M44 196 A56 56 0 0 1 156 196" stroke="#FF5A3C" stroke-width="17" fill="none"/>
<path d="M58 196 A42 42 0 0 1 142 196" stroke="#FFC531" stroke-width="17" fill="none"/>
<path d="M72 196 A28 28 0 0 1 128 196" stroke="#12A594" stroke-width="17" fill="none"/>
<g fill="#FFFDF7" stroke="#33261D" stroke-width="4">
<ellipse cx="42" cy="200" rx="27" ry="16"/><ellipse cx="158" cy="200" rx="27" ry="16"/>
</g>
SVG;
}

function cu_scene_star()
{
    $body = cu_star_body();
    $s1 = cu_spark(166, 26, 10);
    $s2 = cu_spark(24, 120, 8);
    return <<<SVG
<rect width="200" height="250" fill="#FFC531"/>
<circle cx="30" cy="40" r="5" fill="#FFFDF7" opacity=".8"/><circle cx="172" cy="66" r="4" fill="#FFFDF7" opacity=".8"/>
<circle cx="40" cy="210" r="4" fill="#FFFDF7" opacity=".8"/><circle cx="160" cy="214" r="3.5" fill="#FFFDF7" opacity=".8"/>
{$s1}{$s2}{$body}
SVG;
}

function cu_scene_bunny()
{
    $head = cu_bunny_head();
    $s1 = cu_spark(30, 40, 9);
    $s2 = cu_spark(172, 74, 7);
    return <<<SVG
<rect width="200" height="250" fill="#FFE7EE"/>
<ellipse cx="100" cy="238" rx="92" ry="34" fill="#FFC9DA" opacity=".7"/>
{$s1}{$s2}{$head}
SVG;
}

function cu_scene_world()
{
    $s1 = cu_spark(26, 108, 8, '#FFFDF7', .8);
    return <<<SVG
<rect width="200" height="250" fill="#AEE3DA"/>
<circle cx="158" cy="50" r="19" fill="#FFC531" stroke="#33261D" stroke-width="4"/>
<g stroke="#33261D" stroke-width="3" stroke-linecap="round" opacity=".5">
<line x1="158" y1="26" x2="158" y2="19"/><line x1="134" y1="50" x2="127" y2="50"/><line x1="180" y1="36" x2="185" y2="31"/>
</g>
<ellipse cx="44" cy="60" rx="22" ry="12" fill="#FFFDF7"/>
<ellipse cx="120" cy="112" rx="17" ry="9" fill="#FFFDF7" opacity=".85"/>
{$s1}
<path d="M0 168 Q60 118 130 156 Q180 180 200 166 L200 250 H0 Z" fill="#79C99A"/>
<path d="M0 212 Q80 178 160 204 Q190 214 200 208 L200 250 H0 Z" fill="#4CAF74"/>
<rect x="163" y="176" width="8" height="20" fill="#8A5A38"/><circle cx="167" cy="164" r="20" fill="#2F8A57"/>
<g><circle cx="64" cy="198" r="13" fill="#FF9C63" stroke="#33261D" stroke-width="3.5"/>
<path d="M55 190 l-4 -9 9 4 Z" fill="#FF9C63" stroke="#33261D" stroke-width="2.5" stroke-linejoin="round"/>
<path d="M73 190 l4 -9 -9 4 Z" fill="#FF9C63" stroke="#33261D" stroke-width="2.5" stroke-linejoin="round"/>
<circle cx="60" cy="196" r="2.4" fill="#33261D"/><circle cx="68" cy="196" r="2.4" fill="#33261D"/>
<path d="M62 203 L66 203 L64 206 Z" fill="#33261D"/></g>
<g><rect x="126" y="182" width="7" height="16" rx="3.5" fill="#FFFDF7" stroke="#33261D" stroke-width="2.5" transform="rotate(-8 130 190)"/>
<rect x="137" y="182" width="7" height="16" rx="3.5" fill="#FFFDF7" stroke="#33261D" stroke-width="2.5" transform="rotate(8 140 190)"/>
<circle cx="133" cy="200" r="11" fill="#FFFDF7" stroke="#33261D" stroke-width="3.5"/>
<circle cx="130" cy="198" r="2" fill="#33261D"/><circle cx="136" cy="198" r="2" fill="#33261D"/></g>
SVG;
}

/* ---------- dispatchers ---------- */

function cu_scene($key)
{
    $fn = 'cu_scene_' . preg_replace('/[^a-z]/', '', strtolower((string) $key));
    if (!function_exists($fn))
        return '';
    return '<svg viewBox="0 0 200 250" preserveAspectRatio="xMidYMid slice" aria-hidden="true">' . $fn() . '</svg>';
}

function cu_floater($key)
{
    switch ($key) {
        case 'fox':
            return '<svg viewBox="24 12 152 188" aria-hidden="true">' . cu_fox_head() . '</svg>';
        case 'bunny':
            return '<svg viewBox="38 10 124 200" aria-hidden="true">' . cu_bunny_head() . '</svg>';
        case 'star':
            return '<svg viewBox="26 36 148 148" aria-hidden="true">' . cu_star_body() . '</svg>';
        case 'balloon':
            $face = cu_face(100, 88, .65);
            return <<<SVG
<svg viewBox="40 24 140 190" aria-hidden="true">
<circle cx="100" cy="92" r="52" fill="#FF5A3C" stroke="#33261D" stroke-width="5"/>
<path d="M86 44 Q100 92 86 140" stroke="#FFFDF7" stroke-width="9" fill="none" opacity=".9" stroke-linecap="round"/>
<path d="M114 44 Q100 92 114 140" stroke="#FFFDF7" stroke-width="9" fill="none" opacity=".9" stroke-linecap="round"/>
{$face}
<path d="M85 142 L91 162 M115 142 L109 162" stroke="#33261D" stroke-width="3.5" stroke-linecap="round"/>
<rect x="84" y="160" width="32" height="24" rx="7" fill="#C08552" stroke="#33261D" stroke-width="4.5"/>
<path d="M118 186 q5 -7 10 0 q5 -7 10 0" stroke="#33261D" stroke-width="2.5" fill="none" stroke-linecap="round"/>
</svg>
SVG;
    }
    return '';
}

function cu_pop_star($golden = false)
{
    $fill = $golden ? '#FFC531' : '#FFFDF7';
    return <<<SVG
<svg viewBox="0 0 100 100" aria-hidden="true">
<path d="M50 21 L59.5 44 L84.5 46 L65.5 62 L71.5 86 L50 73 L28.5 86 L34.5 62 L15.5 46 L40.5 44 Z"
  fill="{$fill}" stroke="#33261D" stroke-width="5" stroke-linejoin="round"/>
<circle cx="43" cy="52" r="3.2" fill="#33261D"/><circle cx="57" cy="52" r="3.2" fill="#33261D"/>
<path d="M46 59 Q50 62.5 54 59" stroke="#33261D" stroke-width="2.6" fill="none" stroke-linecap="round"/>
</svg>
SVG;
}

function cu_cloud()
{
    return '<svg viewBox="0 0 170 80" aria-hidden="true"><g fill="#FFFFFF">'
        . '<ellipse cx="50" cy="52" rx="42" ry="26"/><ellipse cx="92" cy="40" rx="38" ry="27"/>'
        . '<ellipse cx="126" cy="54" rx="32" ry="21"/><rect x="34" y="46" width="106" height="30" rx="15"/></g></svg>';
}