<?php
$json_data = file_get_contents('pokedex.json');
$pokedex = json_decode($json_data, true);

// LANGUE
$lang = isset($_GET['lang']) && $_GET['lang'] === 'fr' ? 'fr' : 'en';

// TRADUCTIONS
$tr = [
    'fr' => [
        'title' => 'Pokédex Complet',
        'search_placeholder' => 'Rechercher un Pokémon...',
        'stats' => 'Statistiques',
        'weight' => 'Poids', 'height' => 'Taille',
        'back' => 'Retour à la liste',
        'family' => 'Famille d\'évolution',
        'switch_lang' => 'English Version', 'switch_link' => 'en',
        'hp' => 'PV', 'attack' => 'Attaque', 'defense' => 'Défense', 
        'special-attack' => 'Atq. Spé', 'special-defense' => 'Déf. Spé', 'speed' => 'Vitesse',
        'filter_by' => 'Filtre : ',
        'no_result' => 'Aucun Pokémon trouvé...'
    ],
    'en' => [
        'title' => 'Cool Pokemon Games - Pokedex',
        'search_placeholder' => 'Search a Pokemon...',
        'stats' => 'Base Stats',
        'weight' => 'Weight', 'height' => 'Height',
        'back' => 'Back to list',
        'family' => 'Evolution Chain',
        'switch_lang' => 'Version Française', 'switch_link' => 'fr',
        'hp' => 'HP', 'attack' => 'Attack', 'defense' => 'Defense', 
        'special-attack' => 'Sp. Atk', 'special-defense' => 'Sp. Def', 'speed' => 'Speed',
        'filter_by' => 'Filter: ',
        'no_result' => 'No Pokemon found...'
    ]
];
$t = $tr[$lang]; 

// ROUTEUR
$request = trim($_SERVER['REQUEST_URI'], '/');
$request = strtok($request, '?');
$request = urldecode($request);
$filter_type = isset($_GET['type']) ? $_GET['type'] : null;

$pokemon_actuel = null;
$famille_data = [];

if (!empty($request)) {
    foreach ($pokedex as $p) {
        if (mb_strtolower($p['noms']['fr'], 'UTF-8') == mb_strtolower($request, 'UTF-8') || 
            strtolower($p['noms']['en']) == strtolower($request)) {
            $pokemon_actuel = $p;
            break;
        }
    }
    if ($pokemon_actuel && !empty($pokemon_actuel['famille'])) {
        foreach ($pokemon_actuel['famille'] as $membre_nom) {
            foreach ($pokedex as $p_search) {
                if ($p_search['noms']['en'] == ucfirst($membre_nom)) { 
                    $famille_data[] = $p_search;
                    break; 
                }
            }
        }
    }
}

function getTypeColor($type_slug) {
    $colors = [
        'grass' => '#78C850', 'fire' => '#F08030', 'water' => '#6890F0', 'bug' => '#A8B820',
        'normal' => '#A8A878', 'poison' => '#A040A0', 'electric' => '#F8D030', 'ground' => '#E0C068',
        'fairy' => '#EE99AC', 'fighting' => '#C03028', 'psychic' => '#F85888', 'rock' => '#B8A038',
        'ghost' => '#705898', 'ice' => '#98D8D8', 'dragon' => '#7038F8', 'steel' => '#B8B8D0', 
        'dark' => '#705848', 'flying' => '#A890F0'
    ];
    return isset($colors[strtolower($type_slug)]) ? $colors[strtolower($type_slug)] : '#777';
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="/">
    <title><?php echo $pokemon_actuel ? $pokemon_actuel['noms'][$lang] : $t['title']; ?></title>
    
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; color: #333; margin: 0; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; }
        a { text-decoration: none; color: inherit; }
        .lang-switch { position: absolute; top: 20px; right: 20px; background: #333; color: white; padding: 5px 12px; border-radius: 20px; font-size: 0.85em; font-weight:bold; z-index: 100;}
        
        /* SEARCH BAR */
        .search-container { margin: 20px auto 40px; max-width: 500px; position: relative; }
        .search-input { width: 100%; padding: 15px 20px; border-radius: 30px; border: 1px solid #ddd; font-size: 1.1em; box-shadow: 0 4px 10px rgba(0,0,0,0.05); outline: none; transition: 0.3s; }
        .search-input:focus { box-shadow: 0 4px 15px rgba(0,0,0,0.1); border-color: #aaa; }

        /* HEADER LISTE */
        .list-header { text-align: center; margin-bottom: 10px; }
        .filter-tag { background: #333; color: white; padding: 5px 15px; border-radius: 20px; display: inline-block; margin-top: 10px; }
        .remove-filter { color: #ff6b6b; font-weight: bold; margin-left: 10px; cursor: pointer; }

        /* GRID */
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 25px; }
        .card { background: white; padding: 20px; border-radius: 16px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.03); transition: transform 0.2s, box-shadow 0.2s; border: 1px solid white; display: block;} /* display block important pour JS */
        .card:hover { transform: translateY(-5px); box-shadow: 0 8px 20px rgba(0,0,0,0.08); }
        .card img { width: 120px; height: 120px; object-fit: contain; margin-bottom: 10px; }
        .type-pill { color: white; padding: 4px 10px; border-radius: 12px; font-size: 0.75em; margin: 2px; display: inline-block; font-weight: 600; transition: opacity 0.2s;}
        .type-pill:hover { opacity: 0.8; }
        .card.hidden { display: none !important; } /* Classe pour cacher via JS */

        /* DÉTAIL & AUTRES STYLES (Conservés) */
        .detail-card { background: white; border-radius: 24px; padding: 40px; box-shadow: 0 15px 40px rgba(0,0,0,0.08); max-width: 800px; margin: 40px auto; position: relative;}
        .detail-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
        .detail-img { display: block; margin: 0 auto -20px auto; width: 300px; max-width: 100%; filter: drop-shadow(0 10px 15px rgba(0,0,0,0.2)); }
        .desc-box { background: #fafafa; padding: 25px; border-radius: 12px; margin: 30px 0; font-style: italic; color: #555; text-align: center; border-left: 4px solid #eee; }
        .evo-container { display: flex; justify-content: center; align-items: center; gap: 20px; margin: 30px 0; flex-wrap: wrap; }
        .evo-card { text-align: center; opacity: 0.6; transition: 0.3s; }
        .evo-card:hover { opacity: 1; transform: scale(1.05); }
        .evo-card.current { opacity: 1; font-weight: bold; transform: scale(1.1); pointer-events: none;}
        .evo-card img { width: 80px; height: 80px; object-fit: contain; }
        .arrow { font-size: 1.5em; color: #ccc; }
        .stats-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .stats-table td { padding: 8px 0; border-bottom: 1px solid #f0f0f0; }
        .bar-bg { background: #eee; height: 8px; border-radius: 4px; width: 100%; overflow: hidden; }
        .bar-fill { height: 100%; background: #4CAF50; border-radius: 4px; }
        .btn-retour { display: inline-block; margin-top: 30px; color: #888; font-weight: 600; font-size: 0.9em; transition: color 0.2s; }
    </style>
</head>
<body>

<?php $lang_url = "?lang=" . $t['switch_link'] . ($filter_type ? "&type=$filter_type" : ""); ?>
<a href="<?php echo $lang_url; ?>" class="lang-switch"><?php echo $t['switch_lang']; ?></a>

<div class="container">

    <?php if ($pokemon_actuel): ?>
        <div class="detail-card">
            <div class="detail-header">
                <div>
                    <h1 style="margin:0; font-size: 2.5em;"><?php echo $pokemon_actuel['noms'][$lang]; ?></h1>
                    <div style="margin-top:10px;">
                        <?php foreach($pokemon_actuel['types'] as $type_obj): ?>
                            <a href="/?type=<?php echo $type_obj['slug']; ?>&lang=<?php echo $lang; ?>" class="type-pill" style="background-color: <?php echo getTypeColor($type_obj['slug']); ?>;">
                                <?php echo $type_obj[$lang]; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <h2 style="color:#ddd; margin:0; font-size: 2.5em;">#<?php echo str_pad($pokemon_actuel['id'], 3, '0', STR_PAD_LEFT); ?></h2>
            </div>
            <img src="<?php echo $pokemon_actuel['image']; ?>" class="detail-img">
            <div class="desc-box">« <?php echo $pokemon_actuel['description'][$lang]; ?> »</div>
            
            <p style="text-align: center;">
                <strong><?php echo $t['height']; ?> :</strong> <?php echo $pokemon_actuel['taille']; ?> m &nbsp;|&nbsp; 
                <strong><?php echo $t['weight']; ?> :</strong> <?php echo $pokemon_actuel['poids']; ?> kg
            </p>

            <?php if (count($famille_data) > 1): ?>
                <h3 style="text-align:center; margin-top:40px; border-top:1px solid #eee; padding-top:20px;"><?php echo $t['family']; ?></h3>
                <div class="evo-container">
                    <?php foreach($famille_data as $index => $evo): ?>
                        <?php if($index > 0) echo '<div class="arrow">→</div>'; ?>
                        <a href="<?php echo strtolower(urlencode($evo['noms']['en'])); ?>?lang=<?php echo $lang; ?>" 
                           class="evo-card <?php echo ($evo['id'] == $pokemon_actuel['id']) ? 'current' : ''; ?>">
                            <img src="<?php echo $evo['thumbnail']; ?>">
                            <div><?php echo $evo['noms'][$lang]; ?></div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <h3><?php echo $t['stats']; ?></h3>
            <table class="stats-table">
                <?php foreach($pokemon_actuel['stats'] as $stat_key => $val): ?>
                <tr>
                    <td width="30%"><strong><?php echo isset($t[$stat_key]) ? $t[$stat_key] : ucfirst($stat_key); ?></strong></td>
                    <td width="10%"><?php echo $val; ?></td>
                    <td width="60%">
                        <div class="bar-bg">
                            <?php $bar_color = ($val >= 90) ? '#4caf50' : (($val < 50) ? '#ff5722' : '#ffc107'); ?>
                            <div class="bar-fill" style="width: <?php echo min(100, $val/1.5); ?>%; background-color: <?php echo $bar_color; ?>"></div>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
            <center><a href="/?lang=<?php echo $lang; ?>" class="btn-retour">← <?php echo $t['back']; ?></a></center>
        </div>

    <?php else: ?>
        <div class="list-header">
            <img src="https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/items/poke-ball.png" width="60">
            <h1><?php echo $t['title']; ?></h1>
            
            <?php if($filter_type): ?>
                <div class="filter-tag">
                    <?php echo $t['filter_by'] . ucfirst($filter_type); ?>
                    <a href="/?lang=<?php echo $lang; ?>" class="remove-filter">✕</a>
                </div>
            <?php endif; ?>
        </div>

        <div class="search-container">
            <input type="text" id="searchInput" class="search-input" placeholder="<?php echo $t['search_placeholder']; ?>">
        </div>

        <div class="grid" id="pokeGrid">
            <?php foreach ($pokedex as $pokemon): ?>
                <?php 
                    if ($filter_type) {
                        $has_type = false;
                        foreach($pokemon['types'] as $pt) { if ($pt['slug'] == $filter_type) $has_type = true; }
                        if (!$has_type) continue; 
                    }
                ?>
                <a href="<?php echo strtolower(urlencode($pokemon['noms']['en'])); ?>?lang=<?php echo $lang; ?>" 
                   class="card" 
                   data-name="<?php echo strtolower($pokemon['noms'][$lang] . ' ' . $pokemon['noms']['en']); ?>">
                    
                    <img src="<?php echo $pokemon['thumbnail']; ?>" loading="lazy">
                    <h3 style="margin: 10px 0 5px;"><?php echo $pokemon['noms'][$lang]; ?></h3>
                    
                    <div>
                        <?php foreach($pokemon['types'] as $type_obj): ?>
                            <object><a href="/?type=<?php echo $type_obj['slug']; ?>&lang=<?php echo $lang; ?>" class="type-pill" style="background-color: <?php echo getTypeColor($type_obj['slug']); ?>">
                                <?php echo $type_obj[$lang]; ?>
                            </a></object>
                        <?php endforeach; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
        <p id="noResult" style="display:none; text-align:center; color:#888; margin-top:50px;"><?php echo $t['no_result']; ?></p>

        <script>
            document.getElementById('searchInput').addEventListener('keyup', function(e) {
                let term = e.target.value.toLowerCase();
                let cards = document.querySelectorAll('.card');
                let hasResult = false;

                cards.forEach(function(card) {
                    let name = card.getAttribute('data-name');
                    if (name.includes(term)) {
                        card.classList.remove('hidden');
                        hasResult = true;
                    } else {
                        card.classList.add('hidden');
                    }
                });

                document.getElementById('noResult').style.display = hasResult ? 'none' : 'block';
            });
        </script>
    <?php endif; ?>

</div>
</body>
</html>
