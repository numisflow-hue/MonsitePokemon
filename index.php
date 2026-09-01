<?php
// 1. CHARGEMENT
$json_data = file_get_contents(__DIR__ . '/pokedex.json');
if ($json_data === false) {
    http_response_code(500);
    exit('Impossible de charger les données du Pokédex.');
}

try {
    $pokedex = json_decode($json_data, true, 512, JSON_THROW_ON_ERROR);
} catch (JsonException $e) {
    http_response_code(500);
    exit('Les données du Pokédex sont invalides.');
}

// 2. CONFIGURATION LANGUE & DRAPEAUX
$allowed_langs = ['fr', 'en', 'es', 'de', 'it', 'ja'];
$request_path = parse_url(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/', PHP_URL_PATH);
$decoded_path = trim(rawurldecode((string) $request_path), '/');
$path_segments = $decoded_path === '' ? [] : explode('/', $decoded_path);
$route_lang = !empty($path_segments) && in_array($path_segments[0], $allowed_langs, true)
    ? array_shift($path_segments)
    : null;
$has_valid_route_shape = count($path_segments) <= 1;
$request = $has_valid_route_shape && !empty($path_segments) ? $path_segments[0] : implode('/', $path_segments);
$is_home = $has_valid_route_shape && $request === '';

$requested_lang = isset($_GET['lang']) && is_string($_GET['lang']) ? $_GET['lang'] : 'en';
$query_lang = in_array($requested_lang, $allowed_langs, true) ? $requested_lang : 'en';
$lang = $route_lang !== null ? $route_lang : $query_lang;

if ($has_valid_route_shape && in_array($request, ['quiz', 'quiz.php'], true)) {
    require __DIR__ . '/quiz.php';
    exit;
}

$lang_names = [
    'fr' => 'Français',
    'en' => 'English',
    'es' => 'Español',
    'de' => 'Deutsch',
    'it' => 'Italiano',
    'ja' => '日本語'
];

$quiz_labels = [
    'fr' => 'Quiz',
    'en' => 'Quizzes',
    'es' => 'Quiz',
    'de' => 'Quiz',
    'it' => 'Quiz',
    'ja' => 'クイズ'
];

$allowed_sorts = ['id', 'name'];
$requested_sort = isset($_GET['sort']) && is_string($_GET['sort']) ? $_GET['sort'] : 'id';
$sort_order = in_array($requested_sort, $allowed_sorts, true) ? $requested_sort : 'id';
$filter_type = null;

// TRADUCTIONS DE L'INTERFACE
$tr = [
    'fr' => ['title' => 'Pokédex', 'subtitle' => 'Encyclopédie Complète', 'search' => 'Rechercher un Pokémon...', 'stats' => 'Statistiques', 'weight' => 'Poids', 'height' => 'Taille', 'back' => 'Retour à la liste', 'family' => 'Famille d\'évolution', 'type_label' => 'Tous les types', 'sort_id' => 'Numéro', 'sort_name' => 'Nom (A-Z)', 'cry' => 'Cri', 'hp' => 'Points de Vie', 'attack' => 'Attaque', 'defense' => 'Défense', 'special-attack' => 'Attaque Spéciale', 'special-defense' => 'Défense Spéciale', 'speed' => 'Vitesse'],
    'en' => ['title' => 'Pokedex', 'subtitle' => 'The Complete Encyclopedia', 'search' => 'Search a Pokemon...', 'stats' => 'Base Stats', 'weight' => 'Weight', 'height' => 'Height', 'back' => 'Back to list', 'family' => 'Evolution Chain', 'type_label' => 'All Types', 'sort_id' => 'Number', 'sort_name' => 'Name (A-Z)', 'cry' => 'Cry', 'hp' => 'Health Points', 'attack' => 'Attack', 'defense' => 'Defense', 'special-attack' => 'Special Attack', 'special-defense' => 'Special Defense', 'speed' => 'Speed'],
    'es' => ['title' => 'Pokédex', 'subtitle' => 'Enciclopedia Completa', 'search' => 'Buscar...', 'stats' => 'Estadísticas', 'weight' => 'Peso', 'height' => 'Altura', 'back' => 'Volver', 'family' => 'Evolución', 'type_label' => 'Todos', 'sort_id' => 'Número', 'sort_name' => 'Nombre (A-Z)', 'cry' => 'Grito', 'hp' => 'Puntos de Salud', 'attack' => 'Ataque', 'defense' => 'Defensa', 'special-attack' => 'Ataque Especial', 'special-defense' => 'Defensa Especial', 'speed' => 'Velocidad'],
    'de' => ['title' => 'Pokédex', 'subtitle' => 'Komplette Enzyklopädie', 'search' => 'Suchen...', 'stats' => 'Statuswerte', 'weight' => 'Gewicht', 'height' => 'Größe', 'back' => 'Zurück', 'family' => 'Entwicklung', 'type_label' => 'Alle', 'sort_id' => 'Nummer', 'sort_name' => 'Name (A-Z)', 'cry' => 'Ruf', 'hp' => 'Kraftpunkte', 'attack' => 'Angriff', 'defense' => 'Verteidigung', 'special-attack' => 'Spezialangriff', 'special-defense' => 'Spezialverteidigung', 'speed' => 'Initiative'],
    'it' => ['title' => 'Pokédex', 'subtitle' => 'Enciclopedia Completa', 'search' => 'Cerca...', 'stats' => 'Statistiche', 'weight' => 'Peso', 'height' => 'Altezza', 'back' => 'Indietro', 'family' => 'Evoluzione', 'type_label' => 'Tutti', 'sort_id' => 'Numero', 'sort_name' => 'Nome (A-Z)', 'cry' => 'Verso', 'hp' => 'Punti Salute', 'attack' => 'Attacco', 'defense' => 'Difesa', 'special-attack' => 'Attacco Speciale', 'special-defense' => 'Difesa Speciale', 'speed' => 'Velocità'],
    'ja' => ['title' => 'ポケモン図鑑', 'subtitle' => '完全な百科事典', 'search' => '検索...', 'stats' => '種族値', 'weight' => '重さ', 'height' => '高さ', 'back' => '戻る', 'family' => '進化', 'type_label' => 'すべて', 'sort_id' => '番号', 'sort_name' => '名前 (A-Z)', 'cry' => '鳴き声', 'hp' => 'HP', 'attack' => 'こうげき', 'defense' => 'ぼうぎょ', 'special-attack' => 'とくこう', 'special-defense' => 'とくぼう', 'speed' => 'すばやさ']
];

$not_found_tr = [
    'fr' => ['not_found_title' => 'Oups ! Ce Pokémon s’est échappé…', 'not_found_text' => 'La page que tu cherches semble s’être cachée dans les hautes herbes.', 'home' => 'Retourner au Pokédex'],
    'en' => ['not_found_title' => 'Oops! This Pokémon got away…', 'not_found_text' => 'The page you are looking for seems to be hiding in the tall grass.', 'home' => 'Back to the Pokédex'],
    'es' => ['not_found_title' => '¡Ups! Este Pokémon se escapó…', 'not_found_text' => 'La página que buscas parece esconderse entre la hierba alta.', 'home' => 'Volver a la Pokédex'],
    'de' => ['not_found_title' => 'Hoppla! Dieses Pokémon ist entwischt…', 'not_found_text' => 'Die gesuchte Seite versteckt sich wohl im hohen Gras.', 'home' => 'Zurück zum Pokédex'],
    'it' => ['not_found_title' => 'Ops! Questo Pokémon è scappato…', 'not_found_text' => 'La pagina che cerchi sembra essersi nascosta nell’erba alta.', 'home' => 'Torna al Pokédex'],
    'ja' => ['not_found_title' => 'おっと！ポケモンが逃げ出した…', 'not_found_text' => '探しているページは草むらに隠れているようです。', 'home' => 'ポケモン図鑑に戻る']
];

foreach ($not_found_tr as $translation_lang => $translations) {
    $tr[$translation_lang] = array_merge($tr[$translation_lang], $translations);
}

function getTr($key, $lang, $tr) {
    if(isset($tr[$lang][$key])) return $tr[$lang][$key];
    if(isset($tr['en'][$key])) return $tr['en'][$key];
    return $key;
}

function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function buildUrl($path, $params = []) {
    $params = array_filter($params, function ($value) {
        return $value !== null && $value !== '';
    });
    $query = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    return $path . ($query !== '' ? '?' . $query : '');
}

function slugifyPokemonName($name) {
    $name = str_replace(['♀', '♂'], ['-female', '-male'], trim((string) $name));
    $name = strtr($name, [
        'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Ā' => 'A', 'Ă' => 'A', 'Ą' => 'A',
        'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'ā' => 'a', 'ă' => 'a', 'ą' => 'a',
        'Ç' => 'C', 'Ć' => 'C', 'Č' => 'C', 'ç' => 'c', 'ć' => 'c', 'č' => 'c',
        'Ď' => 'D', 'Đ' => 'D', 'ď' => 'd', 'đ' => 'd',
        'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ē' => 'E', 'Ė' => 'E', 'Ę' => 'E',
        'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ē' => 'e', 'ė' => 'e', 'ę' => 'e',
        'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ī' => 'I', 'İ' => 'I',
        'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ī' => 'i', 'ı' => 'i',
        'Ñ' => 'N', 'Ń' => 'N', 'ñ' => 'n', 'ń' => 'n',
        'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O', 'Ō' => 'O',
        'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ø' => 'o', 'ō' => 'o',
        'Ŕ' => 'R', 'Ř' => 'R', 'ŕ' => 'r', 'ř' => 'r',
        'Ś' => 'S', 'Š' => 'S', 'Ş' => 'S', 'ś' => 's', 'š' => 's', 'ş' => 's',
        'Ť' => 'T', 'ť' => 't',
        'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ū' => 'U',
        'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ū' => 'u',
        'Ý' => 'Y', 'Ÿ' => 'Y', 'ý' => 'y', 'ÿ' => 'y',
        'Ž' => 'Z', 'Ź' => 'Z', 'Ż' => 'Z', 'ž' => 'z', 'ź' => 'z', 'ż' => 'z',
        'Æ' => 'AE', 'æ' => 'ae', 'Œ' => 'OE', 'œ' => 'oe', 'ß' => 'ss', 'Ł' => 'L', 'ł' => 'l',
    ]);
    $contains_non_ascii = preg_match('/[^\x00-\x7F]/u', $name) === 1;
    $ascii_name = !$contains_non_ascii && function_exists('iconv')
        ? @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name)
        : false;

    if ($ascii_name !== false && preg_match('/[a-z0-9]/i', $ascii_name)) {
        $slug = strtolower($ascii_name);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    } else {
        $slug = mb_strtolower($name, 'UTF-8');
        $slug = preg_replace('/[^\p{L}\p{N}]+/u', '-', $slug);
    }

    return trim($slug, '-');
}

function languageHomePath($lang) {
    return '/' . rawurlencode($lang);
}

function pokemonPath($pokemon, $lang) {
    $name = isset($pokemon['noms'][$lang]) ? $pokemon['noms'][$lang] : $pokemon['noms']['en'];
    return languageHomePath($lang) . '/' . rawurlencode(slugifyPokemonName($name));
}

$t = isset($tr[$lang]) ? $tr[$lang] : $tr['en']; 

// TRADUCTIONS DES TYPES
$type_names = [
    'normal' => ['fr' => 'Normal', 'en' => 'Normal', 'es' => 'Normal', 'de' => 'Normal', 'it' => 'Normale', 'ja' => 'ノーマル'],
    'fire' => ['fr' => 'Feu', 'en' => 'Fire', 'es' => 'Fuego', 'de' => 'Feuer', 'it' => 'Fuoco', 'ja' => 'ほのお'],
    'water' => ['fr' => 'Eau', 'en' => 'Water', 'es' => 'Agua', 'de' => 'Wasser', 'it' => 'Acqua', 'ja' => 'みず'],
    'grass' => ['fr' => 'Plante', 'en' => 'Grass', 'es' => 'Planta', 'de' => 'Pflanze', 'it' => 'Erba', 'ja' => 'くさ'],
    'electric' => ['fr' => 'Électrik', 'en' => 'Electric', 'es' => 'Eléctrico', 'de' => 'Elektro', 'it' => 'Elettro', 'ja' => 'でんき'],
    'ice' => ['fr' => 'Glace', 'en' => 'Ice', 'es' => 'Hielo', 'de' => 'Eis', 'it' => 'Ghiaccio', 'ja' => 'こおり'],
    'fighting' => ['fr' => 'Combat', 'en' => 'Fighting', 'es' => 'Lucha', 'de' => 'Kampf', 'it' => 'Lotta', 'ja' => 'かくとう'],
    'poison' => ['fr' => 'Poison', 'en' => 'Poison', 'es' => 'Veneno', 'de' => 'Gift', 'it' => 'Veleno', 'ja' => 'どく'],
    'ground' => ['fr' => 'Sol', 'en' => 'Ground', 'es' => 'Tierra', 'de' => 'Boden', 'it' => 'Terra', 'ja' => 'じめん'],
    'flying' => ['fr' => 'Vol', 'en' => 'Flying', 'es' => 'Volador', 'de' => 'Flug', 'it' => 'Volante', 'ja' => 'ひこう'],
    'psychic' => ['fr' => 'Psy', 'en' => 'Psychic', 'es' => 'Psíquico', 'de' => 'Psycho', 'it' => 'Psico', 'ja' => 'エスパー'],
    'bug' => ['fr' => 'Insecte', 'en' => 'Bug', 'es' => 'Bicho', 'de' => 'Käfer', 'it' => 'Coleottero', 'ja' => 'むし'],
    'rock' => ['fr' => 'Roche', 'en' => 'Rock', 'es' => 'Roca', 'de' => 'Gestein', 'it' => 'Roccia', 'ja' => 'いわ'],
    'ghost' => ['fr' => 'Spectre', 'en' => 'Ghost', 'es' => 'Fantasma', 'de' => 'Geist', 'it' => 'Spettro', 'ja' => 'ゴースト'],
    'dragon' => ['fr' => 'Dragon', 'en' => 'Dragon', 'es' => 'Dragón', 'de' => 'Drache', 'it' => 'Drago', 'ja' => 'ドラゴン'],
    'steel' => ['fr' => 'Acier', 'en' => 'Steel', 'es' => 'Acero', 'de' => 'Stahl', 'it' => 'Acciaio', 'ja' => 'はがね'],
    'dark' => ['fr' => 'Ténèbres', 'en' => 'Dark', 'es' => 'Siniestro', 'de' => 'Unlicht', 'it' => 'Buio', 'ja' => 'あく'],
    'fairy' => ['fr' => 'Fée', 'en' => 'Fairy', 'es' => 'Hada', 'de' => 'Fee', 'it' => 'Folletto', 'ja' => 'フェアリー']
];

$requested_type = isset($_GET['type']) && is_string($_GET['type']) ? $_GET['type'] : '';
$filter_type = array_key_exists($requested_type, $type_names) ? $requested_type : null;

if ($sort_order === 'name') {
    usort($pokedex, function($a, $b) use ($lang) {
        $nameA = isset($a['noms'][$lang]) ? $a['noms'][$lang] : $a['noms']['en'];
        $nameB = isset($b['noms'][$lang]) ? $b['noms'][$lang] : $b['noms']['en'];
        return strcmp($nameA, $nameB);
    });
}

$pokemon_actuel = null;
$famille_data = [];

if (!$is_home && $has_valid_route_shape && $pokedex) {
    $requested_slug = slugifyPokemonName($request);

    foreach ($pokedex as $p) {
        foreach ($allowed_langs as $candidate_lang) {
            $candidate_name = isset($p['noms'][$candidate_lang]) ? $p['noms'][$candidate_lang] : $p['noms']['en'];
            if (slugifyPokemonName($candidate_name) === $requested_slug) {
                $pokemon_actuel = $p;
                break 2;
            }
        }
    }

    if ($pokemon_actuel && !empty($pokemon_actuel['famille'])) {
        foreach ($pokemon_actuel['famille'] as $membre_nom) {
            foreach ($pokedex as $p_search) {
                 if (mb_strtolower($p_search['noms']['en'], 'UTF-8') === mb_strtolower($membre_nom, 'UTF-8')) {
                    $famille_data[] = $p_search; break; 
                }
            }
        }
    }
}

$canonical_params = [
    'type' => $filter_type,
    'sort' => $sort_order === 'name' ? 'name' : null
];
$current_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';

if ($is_home) {
    $canonical_url = buildUrl(languageHomePath($lang), $canonical_params);
    if ($current_url !== $canonical_url) {
        header('Location: ' . $canonical_url, true, 301);
        exit;
    }
} elseif ($pokemon_actuel !== null) {
    $canonical_url = pokemonPath($pokemon_actuel, $lang);
    if ($current_url !== $canonical_url) {
        header('Location: ' . $canonical_url, true, 301);
        exit;
    }
}

$is_not_found = !$is_home && $pokemon_actuel === null;
if ($is_not_found) {
    http_response_code(404);
}

function getTypeColor($type_slug) {
    $colors = ['grass' => '#78C850', 'fire' => '#F08030', 'water' => '#6890F0', 'bug' => '#A8B820', 'normal' => '#A8A878', 'poison' => '#A040A0', 'electric' => '#F8D030', 'ground' => '#E0C068', 'fairy' => '#EE99AC', 'fighting' => '#C03028', 'psychic' => '#F85888', 'rock' => '#B8A038', 'ghost' => '#705898', 'ice' => '#98D8D8', 'dragon' => '#7038F8', 'steel' => '#B8B8D0', 'dark' => '#705848', 'flying' => '#A890F0'];
    return isset($colors[strtolower($type_slug)]) ? $colors[strtolower($type_slug)] : '#777';
}

$page_title = $pokemon_actuel
    ? (isset($pokemon_actuel['noms'][$lang]) ? $pokemon_actuel['noms'][$lang] : $pokemon_actuel['noms']['en'])
    : ($is_not_found ? getTr('not_found_title', $lang, $tr) : getTr('title', $lang, $tr));
?>
<!DOCTYPE html>
<html lang="<?php echo e($lang); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="/">
    <title><?php echo e($page_title); ?></title>
    
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; color: #333; margin: 0; padding: 0; }
        .container { max-width: 1100px; margin: 0 auto; padding: 20px; }
        a { text-decoration: none; color: inherit; }
        
        header { background-color: #333; color: white; padding: 15px 0; box-shadow: 0 4px 10px rgba(0,0,0,0.1); position: sticky; top: 0; z-index: 1000; }
        .header-content { max-width: 1100px; margin: 0 auto; padding: 0 20px; display: flex; justify-content: space-between; align-items: center; }
        .brand { display: flex; align-items: center; gap: 15px; }
        .brand img { height: 40px; }
        .brand h1 { margin: 0; font-size: 1.5em; letter-spacing: 1px; }

        .header-actions { display: flex; align-items: center; gap: 10px; }
        .quiz-link { display: inline-flex; align-items: center; gap: 7px; padding: 8px 14px; border: 1px solid rgba(255,255,255,0.16); border-radius: 20px; background: rgba(255,255,255,0.1); color: white; font-size: 0.9rem; font-weight: 700; transition: 0.2s; }
        .quiz-link:hover, .quiz-link:focus-visible { background: white; color: #333; outline: none; transform: translateY(-1px); }

        .lang-menu { position: relative; }
        .lang-menu summary { display: flex; align-items: center; gap: 8px; min-width: 72px; padding: 8px 12px; border: 1px solid rgba(255,255,255,0.2); border-radius: 20px; background: rgba(255,255,255,0.1); color: white; cursor: pointer; font-weight: 700; list-style: none; box-sizing: border-box; }
        .lang-menu summary::-webkit-details-marker { display: none; }
        .lang-menu summary::after { content: '⌄'; margin-left: auto; color: rgba(255,255,255,0.75); transform: translateY(-2px); }
        .lang-menu[open] summary { background: rgba(255,255,255,0.18); }
        .lang-menu-list { position: absolute; top: calc(100% + 9px); right: 0; z-index: 1100; min-width: 190px; padding: 7px; border-radius: 16px; background: white; color: #333; box-shadow: 0 14px 35px rgba(0,0,0,0.22); }
        .lang-menu-list a { display: flex; align-items: center; gap: 10px; padding: 10px 11px; border-radius: 11px; }
        .lang-menu-list a:hover, .lang-menu-list a:focus-visible { background: #f0f2f5; outline: none; }
        .lang-menu-list a.current { background: #eef5ff; color: #1d5fa7; font-weight: 700; }
        .lang-menu-code { margin-left: auto; color: #8a929d; font-size: 0.78rem; font-weight: 800; }
        .flag { display: inline-block; width: 24px; height: 16px; flex: 0 0 24px; overflow: hidden; border: 1px solid rgba(0,0,0,0.16); border-radius: 3px; box-shadow: 0 1px 2px rgba(0,0,0,0.12); box-sizing: border-box; }
        .flag-fr { background: linear-gradient(to right, #0055a4 0 33.33%, #fff 33.33% 66.66%, #ef4135 66.66%); }
        .flag-es { background: linear-gradient(to bottom, #aa151b 0 25%, #f1bf00 25% 75%, #aa151b 75%); }
        .flag-de { background: linear-gradient(to bottom, #151515 0 33.33%, #dd0000 33.33% 66.66%, #ffce00 66.66%); }
        .flag-it { background: linear-gradient(to right, #009246 0 33.33%, #fff 33.33% 66.66%, #ce2b37 66.66%); }
        .flag-ja { background: radial-gradient(circle at center, #bc002d 0 30%, transparent 31%), #fff; }
        .flag-en { background: #012169 url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 60 30'%3E%3Crect width='60' height='30' fill='%23012169'/%3E%3Cpath d='M0 0L60 30M60 0L0 30' stroke='%23fff' stroke-width='7'/%3E%3Cpath d='M0 0L60 30M60 0L0 30' stroke='%23c8102e' stroke-width='4'/%3E%3Cpath d='M30 0V30M0 15H60' stroke='%23fff' stroke-width='11'/%3E%3Cpath d='M30 0V30M0 15H60' stroke='%23c8102e' stroke-width='6'/%3E%3C/svg%3E") center / cover no-repeat; }

        .controls-bar { background: white; padding: 15px 20px; border-radius: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); display: flex; flex-direction: column; gap: 15px; margin-bottom: 30px; margin-top: 10px; }
        @media (min-width: 768px) { .controls-bar { flex-direction: row; align-items: center; justify-content: space-between; } .search-group { flex: 3; } .filters-group { flex: 2; } }

        .search-input { width: 100%; padding: 12px 15px; border: 1px solid #eee; border-radius: 20px; font-size: 1em; outline: none; background: #f9f9f9; box-sizing: border-box; }
        .search-input:focus { border-color: #ccc; background: white; }

        .filters-group { display: flex; gap: 10px; width: 100%; }
        .custom-select { flex: 1; padding: 12px 15px; border-radius: 20px; border: 1px solid #eee; background: #f9f9f9; cursor: pointer; font-size: 0.9em; outline: none; min-width: 0;}
        
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 15px; }
        .card { background: white; padding: 15px; border-radius: 16px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.03); transition: transform 0.2s; border: 1px solid white; display: block; position: relative;}
        .card:hover { transform: translateY(-5px); box-shadow: 0 8px 20px rgba(0,0,0,0.08); }
        .card img { width: 100px; height: 100px; object-fit: contain; margin-bottom: 10px; }
        .card-id { color: #ccc; font-weight: bold; font-size: 0.8em; position: absolute; top: 10px; right: 15px; }
        .type-pill { color: white; padding: 3px 8px; border-radius: 10px; font-size: 0.7em; margin: 2px; display: inline-block; font-weight: 600; text-transform: uppercase;}
        .card.hidden { display: none !important; }

        .detail-card { background: white; border-radius: 24px; padding: 30px; box-shadow: 0 15px 40px rgba(0,0,0,0.08); max-width: 800px; margin: 20px auto; position: relative;}
        .detail-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; flex-wrap: wrap;}
        .detail-img { display: block; margin: 0 auto; width: 280px; max-width: 100%; filter: drop-shadow(0 10px 15px rgba(0,0,0,0.2)); transition: 0.3s ease; }
        
        .action-btn { background: #333; color: white; border: none; padding: 8px 18px; border-radius: 20px; cursor: pointer; font-weight: bold; font-size: 0.9em; transition: 0.2s; box-shadow: 0 4px 8px rgba(0,0,0,0.1); margin: 5px; }
        .action-btn:hover { background: #555; transform: translateY(-2px); }
        .action-btn.active { background: #f1c40f; color: #333; }

        .desc-box { background: #fafafa; padding: 20px; border-radius: 12px; margin: 30px 0; font-style: italic; color: #555; text-align: center; border-left: 4px solid #eee; }
        .evo-container { display: flex; justify-content: center; align-items: center; gap: 15px; margin: 30px 0; flex-wrap: wrap; }
        .evo-card img { width: 70px; height: 70px; }
        .not-found-card { text-align: center; overflow: hidden; padding: 50px 30px; }
        .not-found-code { color: #eceff3; font-size: clamp(5rem, 18vw, 9rem); font-weight: 900; line-height: 0.85; letter-spacing: -0.06em; }
        .not-found-icon { font-size: 4rem; margin: -12px 0 12px; animation: pokemon-search 1.8s ease-in-out infinite; }
        .not-found-card h2 { margin: 0 0 12px; font-size: clamp(1.6rem, 5vw, 2.2rem); }
        .not-found-card p { color: #666; font-size: 1.05rem; margin: 0 auto 20px; max-width: 520px; line-height: 1.6; }
        @keyframes pokemon-search { 0%, 100% { transform: translateX(-10px) rotate(-8deg); } 50% { transform: translateX(10px) rotate(8deg); } }
        .stats-table td { padding: 8px 0; }
        .btn-retour { display: inline-block; margin-top: 10px; margin-bottom: 20px; padding: 10px 25px; background: #eee; color: #333; border-radius: 30px; font-weight: bold; font-size: 0.9em; transition: 0.2s; }
        .btn-retour:hover { background: #ddd; }
        @media (max-width: 560px) {
            .header-content { padding: 0 12px; }
            .brand { gap: 8px; }
            .brand img { height: 34px; }
            .brand h1 { font-size: 1.05rem; }
            .header-actions { gap: 6px; }
            .quiz-link { padding: 8px 9px; font-size: 0.78rem; }
            .lang-menu summary { min-width: 66px; padding: 8px 9px; font-size: 0.82rem; }
        }
    </style>
</head>
<body>

<header>
    <div class="header-content">
        <a href="<?php echo e(languageHomePath($lang)); ?>" class="brand">
            <img src="https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/items/poke-ball.png" alt="Logo">
            <div>
                <h1><?php echo e(getTr('title', $lang, $tr)); ?></h1>
            </div>
        </a>

        <div class="header-actions">
            <a href="<?php echo e(languageHomePath($lang) . '/quiz'); ?>" class="quiz-link"><span aria-hidden="true">★</span> <?php echo e($quiz_labels[$lang]); ?></a>
            <details class="lang-menu">
                <summary aria-label="Changer de langue">
                    <span class="flag flag-<?php echo e($lang); ?>" aria-hidden="true"></span>
                    <span><?php echo e(strtoupper($lang)); ?></span>
                </summary>
                <nav class="lang-menu-list" aria-label="Langues disponibles">
                    <?php foreach($allowed_langs as $l): ?>
                        <?php
                            $base_url = $pokemon_actuel ? pokemonPath($pokemon_actuel, $l) : languageHomePath($l);
                            $selector_params = $pokemon_actuel ? [] : [
                                'type' => $filter_type,
                                'sort' => $sort_order === 'name' ? 'name' : null
                            ];
                            $full_url = buildUrl($base_url, $selector_params);
                        ?>
                        <a href="<?php echo e($full_url); ?>" class="<?php echo $lang === $l ? 'current' : ''; ?>" <?php echo $lang === $l ? 'aria-current="page"' : ''; ?>>
                            <span class="flag flag-<?php echo e($l); ?>" aria-hidden="true"></span>
                            <span><?php echo e($lang_names[$l]); ?></span>
                            <span class="lang-menu-code"><?php echo e(strtoupper($l)); ?></span>
                        </a>
                    <?php endforeach; ?>
                </nav>
            </details>
        </div>
    </div>
</header>

<div class="container">

    <?php if ($pokemon_actuel): ?>
        <div class="detail-card">
            
            <a href="<?php echo e(languageHomePath($lang)); ?>" class="btn-retour">← <?php echo e(getTr('back', $lang, $tr)); ?></a>

            <div class="detail-header">
                <div>
                    <h1 style="margin:0; font-size: 2em;"><?php echo e(isset($pokemon_actuel['noms'][$lang]) ? $pokemon_actuel['noms'][$lang] : $pokemon_actuel['noms']['en']); ?></h1>
                    <div style="margin-top:10px;">
                        <?php foreach($pokemon_actuel['types'] as $type_obj): ?>
                            <a href="<?php echo e(buildUrl(languageHomePath($lang), ['type' => $type_obj['slug']])); ?>" class="type-pill" style="background-color: <?php echo e(getTypeColor($type_obj['slug'])); ?>;">
                                <?php echo e(isset($type_names[$type_obj['slug']][$lang]) ? $type_names[$type_obj['slug']][$lang] : ucfirst($type_obj['slug'])); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <h2 style="color:#ddd; margin:0; font-size: 2em;">#<?php echo e(str_pad($pokemon_actuel['id'], 3, '0', STR_PAD_LEFT)); ?></h2>
            </div>
            
            <div style="text-align: center; margin-bottom: 20px;">
                <img src="<?php echo e($pokemon_actuel['image']); ?>" class="detail-img" id="mainImage"
                     data-normal="<?php echo e($pokemon_actuel['image']); ?>"
                     data-shiny="<?php echo e(isset($pokemon_actuel['shiny']) ? $pokemon_actuel['shiny'] : ''); ?>">
                
                <div style="display: flex; justify-content: center; gap: 10px; margin-top: 15px; flex-wrap: wrap;">
                    
                    <?php $audio_cry = isset($pokemon_actuel['cris']['moderne']) ? $pokemon_actuel['cris']['moderne'] : ''; ?>

                    <?php if (!empty($audio_cry)): ?>
                        <audio id="cryAudio" src="<?php echo e($audio_cry); ?>"></audio>
                        <button onclick="document.getElementById('cryAudio').play()" class="action-btn">🔊 <?php echo e(getTr('cry', $lang, $tr)); ?></button>
                    <?php endif; ?>

                    <?php if (!empty($pokemon_actuel['shiny'])): ?>
                        <button onclick="toggleShiny()" class="action-btn" id="shinyBtn">✨ Shiny</button>
                    <?php endif; ?>
                </div>
            </div>

            <div class="desc-box">« <?php echo e(isset($pokemon_actuel['description'][$lang]) ? $pokemon_actuel['description'][$lang] : $pokemon_actuel['description']['en']); ?> »</div>
            
            <p style="text-align: center;">
                <strong><?php echo e(getTr('height', $lang, $tr)); ?> :</strong> <?php echo e($pokemon_actuel['taille']); ?> m &nbsp;|&nbsp;
                <strong><?php echo e(getTr('weight', $lang, $tr)); ?> :</strong> <?php echo e($pokemon_actuel['poids']); ?> kg
            </p>

            <?php if (!empty($famille_data) && count($famille_data) > 1): ?>
                <h3 style="text-align:center; margin-top:40px; border-top:1px solid #eee; padding-top:20px;"><?php echo e(getTr('family', $lang, $tr)); ?></h3>
                <div class="evo-container">
                    <?php foreach($famille_data as $index => $evo): ?>
                        <?php if($index > 0) echo '<div style="color:#ccc; font-weight:bold;">→</div>'; ?>
                        <a href="<?php echo e(pokemonPath($evo, $lang)); ?>"
                           class="evo-card" style="text-align: center; opacity: <?php echo ((int) $evo['id'] === (int) $pokemon_actuel['id']) ? '0.5' : '1'; ?>;">
                            <img src="<?php echo e($evo['thumbnail']); ?>">
                            <div><?php echo e(isset($evo['noms'][$lang]) ? $evo['noms'][$lang] : $evo['noms']['en']); ?></div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <h3 style="margin-top: 30px;"><?php echo e(getTr('stats', $lang, $tr)); ?></h3>
            <table style="width:100%; border-collapse: collapse;" class="stats-table">
                <?php foreach($pokemon_actuel['stats'] as $stat_key => $val): ?>
                <tr>
                    <td width="30%"><strong><?php echo e(getTr($stat_key, $lang, $tr)); ?></strong></td>
                    <td width="10%"><?php echo e($val); ?></td>
                    <td width="60%">
                        <div style="background: #eee; height: 8px; border-radius: 4px; width: 100%; overflow: hidden;">
                            <?php $bar_color = ($val >= 90) ? '#4caf50' : (($val < 50) ? '#ff5722' : '#ffc107'); ?>
                            <div style="height: 100%; width: <?php echo e(min(100, $val/1.5)); ?>%; background-color: <?php echo e($bar_color); ?>;"></div>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>

        </div>

        <script>
            function toggleShiny() {
                let img = document.getElementById('mainImage');
                let btn = document.getElementById('shinyBtn');
                let normal = img.getAttribute('data-normal');
                let shiny = img.getAttribute('data-shiny');
                if (img.src === normal) { img.src = shiny; btn.classList.add('active'); } 
                else { img.src = normal; btn.classList.remove('active'); }
            }
        </script>

    <?php elseif ($is_not_found): ?>
        <div class="detail-card not-found-card">
            <div class="not-found-code" aria-hidden="true">404</div>
            <div class="not-found-icon" aria-hidden="true">🔎</div>
            <h2><?php echo e(getTr('not_found_title', $lang, $tr)); ?></h2>
            <p><?php echo e(getTr('not_found_text', $lang, $tr)); ?></p>
            <a href="<?php echo e(languageHomePath($lang)); ?>" class="btn-retour">← <?php echo e(getTr('home', $lang, $tr)); ?></a>
        </div>

    <?php else: ?>
        <div class="controls-bar">
            <div class="search-group">
                <input type="text" id="searchInput" class="search-input" placeholder="<?php echo e(getTr('search', $lang, $tr)); ?>">
            </div>
            
            <form method="GET" action="<?php echo e(languageHomePath($lang)); ?>" class="filters-group">
                <select name="type" class="custom-select" onchange="this.form.submit()">
                    <option value=""><?php echo e(getTr('type_label', $lang, $tr)); ?></option>
                    <?php foreach($type_names as $slug => $names): ?>
                        <option value="<?php echo e($slug); ?>" <?php echo $filter_type === $slug ? 'selected' : ''; ?>>
                            <?php echo e(isset($names[$lang]) ? $names[$lang] : $names['en']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="sort" class="custom-select" onchange="this.form.submit()">
                    <option value="id" <?php echo $sort_order === 'id' ? 'selected' : ''; ?>><?php echo e(getTr('sort_id', $lang, $tr)); ?></option>
                    <option value="name" <?php echo $sort_order === 'name' ? 'selected' : ''; ?>><?php echo e(getTr('sort_name', $lang, $tr)); ?></option>
                </select>
            </form>
        </div>

        <div class="grid" id="pokeGrid">
            <?php foreach ($pokedex as $pokemon): ?>
                <?php 
                    if ($filter_type) {
                        $has_type = false;
                        foreach($pokemon['types'] as $pt) { if ($pt['slug'] == $filter_type) $has_type = true; }
                        if (!$has_type) continue; 
                    }
                    $name_display = isset($pokemon['noms'][$lang]) ? $pokemon['noms'][$lang] : $pokemon['noms']['en'];
                    $name_search = mb_strtolower($name_display . ' ' . $pokemon['noms']['en'], 'UTF-8');
                ?>
                <a href="<?php echo e(pokemonPath($pokemon, $lang)); ?>"
                   class="card"
                   data-name="<?php echo e($name_search); ?>">
                   
                    <span class="card-id">#<?php echo e(str_pad($pokemon['id'], 3, '0', STR_PAD_LEFT)); ?></span>
                    <img src="<?php echo e($pokemon['thumbnail']); ?>" loading="lazy">
                    <h3 style="margin: 5px 0 5px; font-size:1.1em;"><?php echo e($name_display); ?></h3>
                    
                    <div>
                        <?php foreach($pokemon['types'] as $type_obj): ?>
                            <object><a href="<?php echo e(buildUrl(languageHomePath($lang), ['type' => $type_obj['slug']])); ?>" class="type-pill" style="background-color: <?php echo e(getTypeColor($type_obj['slug'])); ?>">
                                <?php echo e(isset($type_names[$type_obj['slug']][$lang]) ? $type_names[$type_obj['slug']][$lang] : ucfirst($type_obj['slug'])); ?>
                            </a></object>
                        <?php endforeach; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
        
        <p id="noResult" style="display:none; text-align:center; color:#888; margin-top:50px;">Aucun résultat...</p>

        <script>
            document.getElementById('searchInput').addEventListener('keyup', function(e) {
                let term = e.target.value.toLowerCase();
                let cards = document.querySelectorAll('.card');
                let hasResult = false;
                cards.forEach(function(card) {
                    let name = card.getAttribute('data-name');
                    if (name.includes(term)) {
                        card.classList.remove('hidden'); hasResult = true;
                    } else { card.classList.add('hidden'); }
                });
                document.getElementById('noResult').style.display = hasResult ? 'none' : 'block';
            });
        </script>
    <?php endif; ?>

</div>
</body>
</html>
