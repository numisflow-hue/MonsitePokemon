<?php
// 1. CHARGEMENT
$json_data = file_get_contents('pokedex.json');
$pokedex = json_decode($json_data, true);

// 2. CONFIGURATION LANGUE & DRAPEAUX
$allowed_langs = ['fr', 'en', 'es', 'de', 'it', 'ja'];
$lang = isset($_GET['lang']) && in_array($_GET['lang'], $allowed_langs) ? $_GET['lang'] : 'en';

$lang_flags = [
    'fr' => '🇫🇷 FR', 
    'en' => '🇬🇧 EN', 
    'es' => '🇪🇸 ES', 
    'de' => '🇩🇪 DE', 
    'it' => '🇮🇹 IT', 
    'ja' => '🇯🇵 JP'
];

$sort_order = isset($_GET['sort']) ? $_GET['sort'] : 'id';
$filter_type = isset($_GET['type']) ? $_GET['type'] : null;

// TRADUCTIONS DE L'INTERFACE
$tr = [
    'fr' => ['title' => 'Pokédex', 'subtitle' => 'Encyclopédie Complète', 'search' => 'Rechercher un Pokémon...', 'stats' => 'Statistiques', 'weight' => 'Poids', 'height' => 'Taille', 'back' => 'Retour à la liste', 'family' => 'Famille d\'évolution', 'type_label' => 'Tous les types', 'sort_id' => 'Numéro', 'sort_name' => 'Nom (A-Z)', 'cry' => 'Cri', 'hp' => 'Points de Vie', 'attack' => 'Attaque', 'defense' => 'Défense', 'special-attack' => 'Attaque Spéciale', 'special-defense' => 'Défense Spéciale', 'speed' => 'Vitesse'],
    'en' => ['title' => 'Pokedex', 'subtitle' => 'The Complete Encyclopedia', 'search' => 'Search a Pokemon...', 'stats' => 'Base Stats', 'weight' => 'Weight', 'height' => 'Height', 'back' => 'Back to list', 'family' => 'Evolution Chain', 'type_label' => 'All Types', 'sort_id' => 'Number', 'sort_name' => 'Name (A-Z)', 'cry' => 'Cry', 'hp' => 'Health Points', 'attack' => 'Attack', 'defense' => 'Defense', 'special-attack' => 'Special Attack', 'special-defense' => 'Special Defense', 'speed' => 'Speed'],
    'es' => ['title' => 'Pokédex', 'subtitle' => 'Enciclopedia Completa', 'search' => 'Buscar...', 'stats' => 'Estadísticas', 'weight' => 'Peso', 'height' => 'Altura', 'back' => 'Volver', 'family' => 'Evolución', 'type_label' => 'Todos', 'sort_id' => 'Número', 'sort_name' => 'Nombre (A-Z)', 'cry' => 'Grito', 'hp' => 'Puntos de Salud', 'attack' => 'Ataque', 'defense' => 'Defensa', 'special-attack' => 'Ataque Especial', 'special-defense' => 'Defensa Especial', 'speed' => 'Velocidad'],
    'de' => ['title' => 'Pokédex', 'subtitle' => 'Komplette Enzyklopädie', 'search' => 'Suchen...', 'stats' => 'Statuswerte', 'weight' => 'Gewicht', 'height' => 'Größe', 'back' => 'Zurück', 'family' => 'Entwicklung', 'type_label' => 'Alle', 'sort_id' => 'Nummer', 'sort_name' => 'Name (A-Z)', 'cry' => 'Ruf', 'hp' => 'Kraftpunkte', 'attack' => 'Angriff', 'defense' => 'Verteidigung', 'special-attack' => 'Spezialangriff', 'special-defense' => 'Spezialverteidigung', 'speed' => 'Initiative'],
    'it' => ['title' => 'Pokédex', 'subtitle' => 'Enciclopedia Completa', 'search' => 'Cerca...', 'stats' => 'Statistiche', 'weight' => 'Peso', 'height' => 'Altezza', 'back' => 'Indietro', 'family' => 'Evoluzione', 'type_label' => 'Tutti', 'sort_id' => 'Numero', 'sort_name' => 'Nome (A-Z)', 'cry' => 'Verso', 'hp' => 'Punti Salute', 'attack' => 'Attacco', 'defense' => 'Difesa', 'special-attack' => 'Attacco Speciale', 'special-defense' => 'Difesa Speciale', 'speed' => 'Velocità'],
    'ja' => ['title' => 'ポケモン図鑑', 'subtitle' => '完全な百科事典', 'search' => '検索...', 'stats' => '種族値', 'weight' => '重さ', 'height' => '高さ', 'back' => '戻る', 'family' => '進化', 'type_label' => 'すべて', 'sort_id' => '番号', 'sort_name' => '名前 (A-Z)', 'cry' => '鳴き声', 'hp' => 'HP', 'attack' => 'こうげき', 'defense' => 'ぼうぎょ', 'special-attack' => 'とくこう', 'special-defense' => 'とくぼう', 'speed' => 'すばやさ']
];

function getTr($key, $lang, $tr) {
    if(isset($tr[$lang][$key])) return $tr[$lang][$key];
    if(isset($tr['en'][$key])) return $tr['en'][$key];
    return $key;
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

if ($sort_order === 'name') {
    usort($pokedex, function($a, $b) use ($lang) {
        $nameA = isset($a['noms'][$lang]) ? $a['noms'][$lang] : $a['noms']['en'];
        $nameB = isset($b['noms'][$lang]) ? $b['noms'][$lang] : $b['noms']['en'];
        return strcmp($nameA, $nameB);
    });
}

$request = trim($_SERVER['REQUEST_URI'], '/');
$request = strtok($request, '?');
$request = urldecode($request);
$pokemon_actuel = null;
$famille_data = [];

if (!empty($request) && $pokedex) {
    foreach ($pokedex as $p) {
        $name_fr = isset($p['noms']['fr']) ? $p['noms']['fr'] : '';
        $name_en = isset($p['noms']['en']) ? $p['noms']['en'] : '';
        
        if (mb_strtolower($name_fr, 'UTF-8') == mb_strtolower($request, 'UTF-8') || strtolower($name_en) == strtolower($request)) {
            $pokemon_actuel = $p;
            break;
        }
    }
    if ($pokemon_actuel && !empty($pokemon_actuel['famille'])) {
        foreach ($pokemon_actuel['famille'] as $membre_nom) {
            foreach ($pokedex as $p_search) {
                 if (strtolower($p_search['noms']['en']) == strtolower($membre_nom)) { 
                    $famille_data[] = $p_search; break; 
                }
            }
        }
    }
}

function getTypeColor($type_slug) {
    $colors = ['grass' => '#78C850', 'fire' => '#F08030', 'water' => '#6890F0', 'bug' => '#A8B820', 'normal' => '#A8A878', 'poison' => '#A040A0', 'electric' => '#F8D030', 'ground' => '#E0C068', 'fairy' => '#EE99AC', 'fighting' => '#C03028', 'psychic' => '#F85888', 'rock' => '#B8A038', 'ghost' => '#705898', 'ice' => '#98D8D8', 'dragon' => '#7038F8', 'steel' => '#B8B8D0', 'dark' => '#705848', 'flying' => '#A890F0'];
    return isset($colors[strtolower($type_slug)]) ? $colors[strtolower($type_slug)] : '#777';
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="/">
    <title><?php echo $pokemon_actuel ? $pokemon_actuel['noms'][$lang] : getTr('title', $lang, $tr); ?></title>
    
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; color: #333; margin: 0; padding: 0; }
        .container { max-width: 1100px; margin: 0 auto; padding: 20px; }
        a { text-decoration: none; color: inherit; }
        
        header { background-color: #333; color: white; padding: 15px 0; box-shadow: 0 4px 10px rgba(0,0,0,0.1); position: sticky; top: 0; z-index: 1000; }
        .header-content { max-width: 1100px; margin: 0 auto; padding: 0 20px; display: flex; justify-content: space-between; align-items: center; }
        .brand { display: flex; align-items: center; gap: 15px; }
        .brand img { height: 40px; }
        .brand h1 { margin: 0; font-size: 1.5em; letter-spacing: 1px; }

        .lang-selector { background: rgba(255,255,255,0.1); color: white; padding: 8px 12px; border-radius: 20px; font-weight: bold; border: 1px solid rgba(255,255,255,0.2); outline: none; cursor: pointer; font-size: 1em; }
        .lang-selector option { color: #333; font-size: 1em; }

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
        .stats-table td { padding: 8px 0; }
        .btn-retour { display: inline-block; margin-top: 10px; margin-bottom: 20px; padding: 10px 25px; background: #eee; color: #333; border-radius: 30px; font-weight: bold; font-size: 0.9em; transition: 0.2s; }
        .btn-retour:hover { background: #ddd; }
    </style>
</head>
<body>

<header>
    <div class="header-content">
        <a href="/?lang=<?php echo $lang; ?>" class="brand">
            <img src="https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/items/poke-ball.png" alt="Logo">
            <div>
                <h1><?php echo getTr('title', $lang, $tr); ?></h1>
            </div>
        </a>

        <select class="lang-selector" onchange="location = this.value;">
            <?php foreach($allowed_langs as $l): ?>
                <?php 
                    $base_url = $pokemon_actuel ? "/" . strtolower(urlencode($pokemon_actuel['noms']['en'])) : "/";
                    $full_url = $base_url . "?lang=" . $l . ($filter_type ? "&type=$filter_type" : "") . "&sort=$sort_order";
                ?>
                <option value="<?php echo $full_url; ?>" <?php echo $lang == $l ? 'selected' : ''; ?>>
                    <?php echo $lang_flags[$l]; ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</header>

<div class="container">

    <?php if ($pokemon_actuel): ?>
        <div class="detail-card">
            
            <a href="/?lang=<?php echo $lang; ?>" class="btn-retour">← <?php echo getTr('back', $lang, $tr); ?></a>

            <div class="detail-header">
                <div>
                    <h1 style="margin:0; font-size: 2em;"><?php echo isset($pokemon_actuel['noms'][$lang]) ? $pokemon_actuel['noms'][$lang] : $pokemon_actuel['noms']['en']; ?></h1>
                    <div style="margin-top:10px;">
                        <?php foreach($pokemon_actuel['types'] as $type_obj): ?>
                            <a href="/?type=<?php echo $type_obj['slug']; ?>&lang=<?php echo $lang; ?>" class="type-pill" style="background-color: <?php echo getTypeColor($type_obj['slug']); ?>;">
                                <?php echo isset($type_names[$type_obj['slug']][$lang]) ? $type_names[$type_obj['slug']][$lang] : ucfirst($type_obj['slug']); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <h2 style="color:#ddd; margin:0; font-size: 2em;">#<?php echo str_pad($pokemon_actuel['id'], 3, '0', STR_PAD_LEFT); ?></h2>
            </div>
            
            <div style="text-align: center; margin-bottom: 20px;">
                <img src="<?php echo $pokemon_actuel['image']; ?>" class="detail-img" id="mainImage" 
                     data-normal="<?php echo $pokemon_actuel['image']; ?>" 
                     data-shiny="<?php echo isset($pokemon_actuel['shiny']) ? $pokemon_actuel['shiny'] : ''; ?>">
                
                <div style="display: flex; justify-content: center; gap: 10px; margin-top: 15px; flex-wrap: wrap;">
                    
                    <?php $audio_cry = isset($pokemon_actuel['cris']['moderne']) ? $pokemon_actuel['cris']['moderne'] : ''; ?>

                    <?php if (!empty($audio_cry)): ?>
                        <audio id="cryAudio" src="<?php echo $audio_cry; ?>"></audio>
                        <button onclick="document.getElementById('cryAudio').play()" class="action-btn">🔊 <?php echo getTr('cry', $lang, $tr); ?></button>
                    <?php endif; ?>

                    <?php if (!empty($pokemon_actuel['shiny'])): ?>
                        <button onclick="toggleShiny()" class="action-btn" id="shinyBtn">✨ Shiny</button>
                    <?php endif; ?>
                </div>
            </div>

            <div class="desc-box">« <?php echo isset($pokemon_actuel['description'][$lang]) ? $pokemon_actuel['description'][$lang] : $pokemon_actuel['description']['en']; ?> »</div>
            
            <p style="text-align: center;">
                <strong><?php echo getTr('height', $lang, $tr); ?> :</strong> <?php echo $pokemon_actuel['taille']; ?> m &nbsp;|&nbsp; 
                <strong><?php echo getTr('weight', $lang, $tr); ?> :</strong> <?php echo $pokemon_actuel['poids']; ?> kg
            </p>

            <?php if (!empty($famille_data) && count($famille_data) > 1): ?>
                <h3 style="text-align:center; margin-top:40px; border-top:1px solid #eee; padding-top:20px;"><?php echo getTr('family', $lang, $tr); ?></h3>
                <div class="evo-container">
                    <?php foreach($famille_data as $index => $evo): ?>
                        <?php if($index > 0) echo '<div style="color:#ccc; font-weight:bold;">→</div>'; ?>
                        <a href="<?php echo strtolower(urlencode($evo['noms']['en'])); ?>?lang=<?php echo $lang; ?>" 
                           class="evo-card" style="text-align: center; opacity: <?php echo ($evo['id'] == $pokemon_actuel['id']) ? '0.5' : '1'; ?>;">
                            <img src="<?php echo $evo['thumbnail']; ?>">
                            <div><?php echo isset($evo['noms'][$lang]) ? $evo['noms'][$lang] : $evo['noms']['en']; ?></div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <h3 style="margin-top: 30px;"><?php echo getTr('stats', $lang, $tr); ?></h3>
            <table style="width:100%; border-collapse: collapse;" class="stats-table">
                <?php foreach($pokemon_actuel['stats'] as $stat_key => $val): ?>
                <tr>
                    <td width="30%"><strong><?php echo getTr($stat_key, $lang, $tr); ?></strong></td>
                    <td width="10%"><?php echo $val; ?></td>
                    <td width="60%">
                        <div style="background: #eee; height: 8px; border-radius: 4px; width: 100%; overflow: hidden;">
                            <?php $bar_color = ($val >= 90) ? '#4caf50' : (($val < 50) ? '#ff5722' : '#ffc107'); ?>
                            <div style="height: 100%; width: <?php echo min(100, $val/1.5); ?>%; background-color: <?php echo $bar_color; ?>;"></div>
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

    <?php else: ?>
        <div class="controls-bar">
            <div class="search-group">
                <input type="text" id="searchInput" class="search-input" placeholder="<?php echo getTr('search', $lang, $tr); ?>">
            </div>
            
            <form method="GET" action="/" class="filters-group">
                <input type="hidden" name="lang" value="<?php echo $lang; ?>">
                
                <select name="type" class="custom-select" onchange="this.form.submit()">
                    <option value=""><?php echo getTr('type_label', $lang, $tr); ?></option>
                    <?php foreach($type_names as $slug => $names): ?>
                        <option value="<?php echo $slug; ?>" <?php echo $filter_type == $slug ? 'selected' : ''; ?>>
                            <?php echo isset($names[$lang]) ? $names[$lang] : $names['en']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="sort" class="custom-select" onchange="this.form.submit()">
                    <option value="id" <?php echo $sort_order == 'id' ? 'selected' : ''; ?>><?php echo getTr('sort_id', $lang, $tr); ?></option>
                    <option value="name" <?php echo $sort_order == 'name' ? 'selected' : ''; ?>><?php echo getTr('sort_name', $lang, $tr); ?></option>
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
                    $name_search = strtolower($name_display . ' ' . $pokemon['noms']['en']);
                ?>
                <a href="<?php echo strtolower(urlencode($pokemon['noms']['en'])); ?>?lang=<?php echo $lang; ?>&type=<?php echo $filter_type; ?>&sort=<?php echo $sort_order; ?>" 
                   class="card" 
                   data-name="<?php echo $name_search; ?>">
                   
                    <span class="card-id">#<?php echo str_pad($pokemon['id'], 3, '0', STR_PAD_LEFT); ?></span>
                    <img src="<?php echo $pokemon['thumbnail']; ?>" loading="lazy">
                    <h3 style="margin: 5px 0 5px; font-size:1.1em;"><?php echo $name_display; ?></h3>
                    
                    <div>
                        <?php foreach($pokemon['types'] as $type_obj): ?>
                            <object><a href="/?type=<?php echo $type_obj['slug']; ?>&lang=<?php echo $lang; ?>" class="type-pill" style="background-color: <?php echo getTypeColor($type_obj['slug']); ?>">
                                <?php echo isset($type_names[$type_obj['slug']][$lang]) ? $type_names[$type_obj['slug']][$lang] : ucfirst($type_obj['slug']); ?>
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