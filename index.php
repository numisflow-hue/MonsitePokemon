<?php
// 1. CHARGEMENT
// On vérifie si le fichier existe pour éviter le crash
if (!file_exists('pokedex.json')) {
    die("<h1>Erreur : Le fichier pokedex.json est introuvable !</h1><p>Veuillez l'uploader dans le dossier public_html sur Hostinger.</p>");
}
$json_data = file_get_contents('pokedex.json');
$pokedex = json_decode($json_data, true);

// 2. CONFIGURATION
$lang = isset($_GET['lang']) && $_GET['lang'] === 'fr' ? 'fr' : 'en';
$sort_order = isset($_GET['sort']) ? $_GET['sort'] : 'id';
$filter_type = isset($_GET['type']) ? $_GET['type'] : null;

// TRADUCTIONS
$tr = [
    'fr' => [
        'title' => 'Pokédex',
        'search_placeholder' => 'Rechercher...',
        'stats' => 'Statistiques',
        'weight' => 'Poids', 'height' => 'Taille',
        'back' => 'Retour',
        'family' => 'Famille d\'évolution',
        'switch_lang' => 'EN', 'switch_link' => 'en', 
        'hp' => 'PV', 'attack' => 'Attaque', 'defense' => 'Défense', 
        'special-attack' => 'Atq. Spé', 'special-defense' => 'Déf. Spé', 'speed' => 'Vitesse',
        'sort_label' => 'Trier', 'sort_id' => '#', 'sort_name' => 'A-Z',
        'type_label' => 'Type', 'all_types' => 'Tous'
    ],
    'en' => [
        'title' => 'Pokedex',
        'search_placeholder' => 'Search...',
        'stats' => 'Base Stats',
        'weight' => 'Weight', 'height' => 'Height',
        'back' => 'Back',
        'family' => 'Evolution Chain',
        'switch_lang' => 'FR', 'switch_link' => 'fr', 
        'hp' => 'HP', 'attack' => 'Attack', 'defense' => 'Defense', 
        'special-attack' => 'Sp. Atk', 'special-defense' => 'Sp. Def', 'speed' => 'Speed',
        'sort_label' => 'Sort', 'sort_id' => '#', 'sort_name' => 'A-Z',
        'type_label' => 'Type', 'all_types' => 'All'
    ]
];
$t = $tr[$lang]; 

// TYPES
$type_names = [
    'normal' => ['fr' => 'Normal', 'en' => 'Normal'], 'fire' => ['fr' => 'Feu', 'en' => 'Fire'],
    'water' => ['fr' => 'Eau', 'en' => 'Water'], 'grass' => ['fr' => 'Plante', 'en' => 'Grass'],
    'electric' => ['fr' => 'Électrik', 'en' => 'Electric'], 'ice' => ['fr' => 'Glace', 'en' => 'Ice'],
    'fighting' => ['fr' => 'Combat', 'en' => 'Fighting'], 'poison' => ['fr' => 'Poison', 'en' => 'Poison'],
    'ground' => ['fr' => 'Sol', 'en' => 'Ground'], 'flying' => ['fr' => 'Vol', 'en' => 'Flying'],
    'psychic' => ['fr' => 'Psy', 'en' => 'Psychic'], 'bug' => ['fr' => 'Insecte', 'en' => 'Bug'],
    'rock' => ['fr' => 'Roche', 'en' => 'Rock'], 'ghost' => ['fr' => 'Spectre', 'en' => 'Ghost'],
    'dragon' => ['fr' => 'Dragon', 'en' => 'Dragon'], 'steel' => ['fr' => 'Acier', 'en' => 'Steel'],
    'dark' => ['fr' => 'Ténèbres', 'en' => 'Dark'], 'fairy' => ['fr' => 'Fée', 'en' => 'Fairy']
];

// TRI
if ($sort_order === 'name') {
    usort($pokedex, function($a, $b) use ($lang) {
        return strcmp($a['noms'][$lang], $b['noms'][$lang]);
    });
}

// ROUTEUR
$request = trim($_SERVER['REQUEST_URI'], '/');
$request = strtok($request, '?');
$request = urldecode($request);
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
                    $famille_data[] = $p_search; break; 
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
        /* RESET & BASE */
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; color: #333; margin: 0; padding: 0; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        a { text-decoration: none; color: inherit; }
        
        /* === HEADER COMPLEXE === */
        header { background-color: #222; color: white; padding: 10px 0; box-shadow: 0 4px 10px rgba(0,0,0,0.2); position: sticky; top: 0; z-index: 1000; }
        
        .header-content { 
            max-width: 1200px; margin: 0 auto; padding: 0 20px; 
            display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 15px;
        }

        /* LOGO */
        .brand { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }
        .brand img { height: 35px; }
        .brand h1 { margin: 0; font-size: 1.4em; letter-spacing: 0.5px; color: white; }
        .brand:hover { opacity: 0.9; }

        /* OUTILS (Recherche + Filtres) */
        .header-tools { 
            display: flex; gap: 10px; flex-grow: 1; justify-content: center;
            width: 100%; order: 3; 
        }
        
        /* INPUTS HEADER STYLE */
        .header-input { 
            background: #444; border: 1px solid #555; color: white; 
            padding: 8px 15px; border-radius: 20px; outline: none; font-size: 0.9em;
        }
        .header-input:focus { background: #555; border-color: #777; }
        .search-bar { flex-grow: 1; max-width: 400px; } 
        .filter-select { cursor: pointer; max-width: 120px;}

        /* BOUTON LANGUE */
        .lang-btn { 
            background: #444; color: white; padding: 6px 12px; border-radius: 15px; 
            font-size: 0.8em; font-weight: bold; border: 1px solid #555; flex-shrink: 0;
            order: 2; 
        }
        .lang-btn:hover { background: #666; }

        /* RESPONSIVE HEADER (PC) */
        @media (min-width: 900px) {
            .header-tools { 
                width: auto; order: 2; 
                justify-content: flex-end; 
            }
            .lang-btn { order: 3; }
        }

        /* GRID */
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 15px; margin-top: 20px; }
        .card { background: white; padding: 15px; border-radius: 16px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.03); transition: transform 0.2s; border: 1px solid white; display: block; position: relative;}
        .card:hover { transform: translateY(-5px); box-shadow: 0 8px 20px rgba(0,0,0,0.08); }
        .card img { width: 100px; height: 100px; object-fit: contain; margin-bottom: 10px; }
        .card-id { color: #ccc; font-weight: bold; font-size: 0.8em; position: absolute; top: 10px; right: 15px; }
        .type-pill { color: white; padding: 3px 8px; border-radius: 10px; font-size: 0.7em; margin: 2px; display: inline-block; font-weight: 600; }
        .card.hidden { display: none !important; }

        /* DETAIL CARD */
        .detail-card { background: white; border-radius: 24px; padding: 30px; box-shadow: 0 15px 40px rgba(0,0,0,0.08); max-width: 800px; margin: 20px auto; position: relative;}
        .detail-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; flex-wrap: wrap;}
        .detail-img { display: block; margin: 0 auto -20px auto; width: 280px; max-width: 100%; filter: drop-shadow(0 10px 15px rgba(0,0,0,0.2)); }
        .desc-box { background: #fafafa; padding: 20px; border-radius: 12px; margin: 30px 0; font-style: italic; color: #555; text-align: center; border-left: 4px solid #eee; }
        .evo-container { display: flex; justify-content: center; align-items: center; gap: 15px; margin: 30px 0; flex-wrap: wrap; }
        .evo-card img { width: 70px; height: 70px; }
        .stats-table td { padding: 8px 0; }
        .btn-retour { display: inline-block; margin-top: 30px; padding: 10px 25px; background: #eee; color: #333; border-radius: 30px; font-weight: bold; font-size: 0.9em; transition: 0.2s; }
        .btn-retour:hover { background: #ddd; }
    </style>
</head>
<body>

<?php 
// LIENS ET VARS
$home_url = "/?lang=" . $lang . "&type=" . $filter_type . "&sort=" . $sort_order;
$lang_switch_url = "?lang=" . $t['switch_link'] . ($filter_type ? "&type=$filter_type" : "") . "&sort=$sort_order";
?>

<header>
    <div class="header-content">
        <a href="<?php echo $home_url; ?>" class="brand">
            <img src="https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/items/poke-ball.png" alt="Logo">
            <h1><?php echo $t['title']; ?></h1>
        </a>

        <a href="<?php echo $lang_switch_url; ?>" class="lang-btn">
            <?php echo $t['switch_lang']; ?>
        </a>

        <?php if (!$pokemon_actuel): ?>
            <form method="GET" action="/" class="header-tools">
                <input type="hidden" name="lang" value="<?php echo $lang; ?>">
                
                <input type="text" id="searchInput" class="header-input search-bar" placeholder="<?php echo $t['search_placeholder']; ?>">
                
                <select name="type" class="header-input filter-select" onchange="this.form.submit()">
                    <option value=""><?php echo $t['all_types']; ?></option>
                    <?php foreach($type_names as $slug => $names): ?>
                        <option value="<?php echo $slug; ?>" <?php echo $filter_type == $slug ? 'selected' : ''; ?>>
                            <?php echo $names[$lang]; ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="sort" class="header-input filter-select" onchange="this.form.submit()">
                    <option value="id" <?php echo $sort_order == 'id' ? 'selected' : ''; ?>><?php echo $t['sort_id']; ?></option>
                    <option value="name" <?php echo $sort_order == 'name' ? 'selected' : ''; ?>><?php echo $t['sort_name']; ?></option>
                </select>
            </form>
        <?php endif; ?>
    </div>
</header>

<div class="container">

    <?php if ($pokemon_actuel): ?>
        <div class="detail-card">
            <a href="<?php echo $home_url; ?>" class="btn-retour">← <?php echo $t['back']; ?></a>
            <br><br>

            <div class="detail-header">
                <div>
                    <h1 style="margin:0; font-size: 2em;"><?php echo $pokemon_actuel['noms'][$lang]; ?></h1>
                    <div style="margin-top:10px;">
                        <?php foreach($pokemon_actuel['types'] as $type_obj): ?>
                            <a href="/?type=<?php echo $type_obj['slug']; ?>&lang=<?php echo $lang; ?>" class="type-pill" style="background-color: <?php echo getTypeColor($type_obj['slug']); ?>;">
                                <?php echo $type_obj[$lang]; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <h2 style="color:#ddd; margin:0; font-size: 2em;">#<?php echo str_pad($pokemon_actuel['id'], 3, '0', STR_PAD_LEFT); ?></h2>
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

            <h3 style="margin-top: 30px;"><?php echo $t['stats']; ?></h3>
            <table style="width:100%; border-collapse: collapse;">
                <?php foreach($pokemon_actuel['stats'] as $stat_key => $val): ?>
                <tr>
                    <td width="30%"><strong><?php echo isset($t[$stat_key]) ? $t[$stat_key] : ucfirst($stat_key); ?></strong></td>
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

    <?php else: ?>
        <div class="grid" id="pokeGrid">
            <?php foreach ($pokedex as $pokemon): ?>
                <?php 
                    if ($filter_type) {
                        $has_type = false;
                        foreach($pokemon['types'] as $pt) { if ($pt['slug'] == $filter_type) $has_type = true; }
                        if (!$has_type) continue; 
                    }
                ?>
                <a href="<?php echo strtolower(urlencode($pokemon['noms']['en'])); ?>?lang=<?php echo $lang; ?>&type=<?php echo $filter_type; ?>&sort=<?php echo $sort_order; ?>" 
                   class="card" 
                   data-name="<?php echo strtolower($pokemon['noms'][$lang] . ' ' . $pokemon['noms']['en']); ?>">
                   
                    <span class="card-id">#<?php echo str_pad($pokemon['id'], 3, '0', STR_PAD_LEFT); ?></span>

                    <img src="<?php echo $pokemon['thumbnail']; ?>" loading="lazy">
                    <h3 style="margin: 5px 0 5px; font-size:1.1em;"><?php echo $pokemon['noms'][$lang]; ?></h3>
                    
                    <div>
                        <?php foreach($pokemon['types'] as $type_obj): ?>
                            <object><a href="/?type=<?php echo $type_obj['slug']; ?>&lang=<?php echo $lang; ?>&sort=<?php echo $sort_order; ?>" class="type-pill" style="background-color: <?php echo getTypeColor($type_obj['slug']); ?>">
                                <?php echo $type_obj[$lang]; ?>
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
