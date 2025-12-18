<?php
// 1. CHARGEMENT DONNÉES
$json_data = file_get_contents('pokedex.json');
$pokedex = json_decode($json_data, true);

// 2. GESTION DE LA LANGUE
// Par défaut c'est 'fr', sauf si on demande 'en' dans l'URL (ex: ?lang=en)
$lang = isset($_GET['lang']) && $_GET['lang'] === 'en' ? 'en' : 'fr';

// Petit dictionnaire pour l'interface (Boutons, titres...)
$tr = [
    'fr' => [
        'title' => 'Pokédex Complet',
        'search_placeholder' => 'Rechercher un Pokémon...',
        'stats' => 'Statistiques',
        'weight' => 'Poids',
        'height' => 'Taille',
        'back' => 'Retour à la liste',
        'evolves_from' => 'Évolue depuis',
        'switch_lang' => 'English Version',
        'switch_link' => 'en',
        // Traduction des stats techniques
        'hp' => 'PV', 'attack' => 'Attaque', 'defense' => 'Défense', 
        'special-attack' => 'Atq. Spé', 'special-defense' => 'Déf. Spé', 'speed' => 'Vitesse'
    ],
    'en' => [
        'title' => 'Full Pokédex',
        'search_placeholder' => 'Search a Pokemon...',
        'stats' => 'Base Stats',
        'weight' => 'Weight',
        'height' => 'Height',
        'back' => 'Back to list',
        'evolves_from' => 'Evolves from',
        'switch_lang' => 'Version Française',
        'switch_link' => 'fr',
        // Stats technical names
        'hp' => 'HP', 'attack' => 'Attack', 'defense' => 'Defense', 
        'special-attack' => 'Sp. Atk', 'special-defense' => 'Sp. Def', 'speed' => 'Speed'
    ]
];
// On raccourcit la variable pour l'utiliser facilement : $t['weight']
$t = $tr[$lang]; 

// 3. ROUTEUR
$request = trim($_SERVER['REQUEST_URI'], '/');
$request = strtok($request, '?'); // On enlève le ?lang=en de l'analyse
$request = urldecode($request);

$pokemon_actuel = null;
$parent_pokemon = null;

if (!empty($request)) {
    foreach ($pokedex as $p) {
        // On compare avec le nom FR ou EN (pour que /pikachu et /charizard marchent)
        if (mb_strtolower($p['noms']['fr'], 'UTF-8') == mb_strtolower($request, 'UTF-8') || 
            strtolower($p['noms']['en']) == strtolower($request)) {
            $pokemon_actuel = $p;
            break;
        }
    }
    
    // Recherche du parent (Évolution)
    if ($pokemon_actuel && $pokemon_actuel['evolue_de']) {
        foreach ($pokedex as $p) {
            // L'API nous donne le parent en anglais (ID technique), on le cherche
            if (strtolower($p['noms']['en']) == strtolower($pokemon_actuel['evolue_de'])) {
                $parent_pokemon = $p;
                break;
            }
        }
    }
}

// Fonction Couleur (inchangée)
function getTypeColor($type_fr) {
    $colors = [
        'Plante' => '#78C850', 'Feu' => '#F08030', 'Eau' => '#6890F0', 'Insecte' => '#A8B820',
        'Normal' => '#A8A878', 'Poison' => '#A040A0', 'Électrik' => '#F8D030', 'Sol' => '#E0C068',
        'Fée' => '#EE99AC', 'Combat' => '#C03028', 'Psy' => '#F85888', 'Roche' => '#B8A038',
        'Spectre' => '#705898', 'Glace' => '#98D8D8', 'Dragon' => '#7038F8'
    ];
    // Fallback simple pour l'anglais ou types inconnus
    return isset($colors[$type_fr]) ? $colors[$type_fr] : '#777';
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
        body { font-family: 'Segoe UI', sans-serif; background: #f4f4f4; color: #333; margin: 0; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; }
        a { text-decoration: none; color: inherit; }
        
        /* Bouton Langue */
        .lang-switch { position: absolute; top: 20px; right: 20px; background: #333; color: white; padding: 5px 10px; border-radius: 5px; font-size: 0.8em; }
        .lang-switch:hover { background: #555; }

        /* GRID */
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 20px; margin-top: 30px;}
        .card { background: white; padding: 15px; border-radius: 15px; text-align: center; box-shadow: 0 4px 6px rgba(0,0,0,0.05); transition: transform 0.2s; }
        .card:hover { transform: translateY(-5px); }
        .card img { width: 100px; height: 100px; object-fit: contain; }
        .type-pill { color: white; padding: 3px 8px; border-radius: 10px; font-size: 0.7em; margin: 2px; display: inline-block;}

        /* DÉTAIL */
        .detail-card { background: white; border-radius: 20px; padding: 40px; box-shadow: 0 10px 20px rgba(0,0,0,0.1); max-width: 700px; margin: 50px auto; position: relative;}
        .detail-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #eee; padding-bottom: 20px; margin-bottom: 20px; }
        .detail-img { display: block; margin: 0 auto; max-width: 300px; }
        .info-box { background: #f9f9f9; padding: 20px; border-radius: 10px; margin: 20px 0; font-style: italic;}
        .stats-table { width: 100%; border-collapse: collapse; }
        .stats-table td { padding: 8px; border-bottom: 1px solid #eee; }
        .bar-container { background: #eee; height: 10px; border-radius: 5px; width: 100%; overflow: hidden; }
        .bar-fill { height: 100%; background: #4CAF50; }
        .btn-retour { display: inline-block; margin-top: 20px; color: #666; font-weight: bold; }
    </style>
</head>
<body>

<?php 
    // On construit le lien inverse
    $current_slug = $pokemon_actuel ? strtolower($pokemon_actuel['noms']['en']) : ''; // On utilise le slug EN par défaut pour l'URL
    if($pokemon_actuel && $lang == 'en') $current_slug = strtolower($pokemon_actuel['noms']['fr']); // Si on veut passer en FR
    
    // Simplification : Retour accueil avec paramètre langue
    $lang_url = "?lang=" . $t['switch_link'];
    // Si on est sur une fiche, on reste dessus (optionnel, plus complexe à gérer parfaitement sans URL rewriting avancé)
    // Pour l'instant, le bouton langue renvoie à l'accueil dans la bonne langue pour faire simple
?>
<a href="<?php echo $lang_url; ?>" class="lang-switch"><?php echo $t['switch_lang']; ?></a>

<div class="container">

    <?php if ($pokemon_actuel): ?>
        <div class="detail-card">
            <div class="detail-header">
                <div>
                    <h1 style="margin:0"><?php echo $pokemon_actuel['noms'][$lang]; ?></h1>
                    
                    <div style="margin-top:5px;">
                        <?php foreach($pokemon_actuel['types'] as $type_obj): ?>
                            <span class="type-pill" style="background-color: <?php echo getTypeColor($type_obj['fr']); ?>;">
                                <?php echo $type_obj[$lang]; ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <h2 style="color:#aaa; margin:0">#<?php echo str_pad($pokemon_actuel['id'], 3, '0', STR_PAD_LEFT); ?></h2>
            </div>

            <img src="<?php echo $pokemon_actuel['image']; ?>" class="detail-img">

            <div class="info-box">
                « <?php echo $pokemon_actuel['description'][$lang]; ?> »
            </div>
            
            <p><strong><?php echo $t['height']; ?> :</strong> <?php echo $pokemon_actuel['taille']; ?> m | <strong><?php echo $t['weight']; ?> :</strong> <?php echo $pokemon_actuel['poids']; ?> kg</p>

            <?php if ($parent_pokemon): ?>
            <div style="background: #eef5ff; padding: 15px; border-radius: 10px; text-align: center; margin-top: 20px;">
                <p><?php echo $t['evolves_from']; ?> :</p>
                <a href="<?php echo strtolower(urlencode($parent_pokemon['noms'][$lang])); ?>?lang=<?php echo $lang; ?>" style="font-weight: bold; color: #3b4cca;">
                    <img src="<?php echo $parent_pokemon['thumbnail']; ?>" style="vertical-align: middle; width: 40px;">
                    <?php echo $parent_pokemon['noms'][$lang]; ?>
                </a>
            </div>
            <?php endif; ?>

            <h3><?php echo $t['stats']; ?></h3>
            <table class="stats-table">
                <?php foreach($pokemon_actuel['stats'] as $stat_key => $val): ?>
                <tr>
                    <td width="30%"><strong><?php echo isset($t[$stat_key]) ? $t[$stat_key] : ucfirst($stat_key); ?></strong></td>
                    <td width="10%"><?php echo $val; ?></td>
                    <td width="60%">
                        <div class="bar-container">
                            <div class="bar-fill" style="width: <?php echo min(100, $val/1.5); ?>%;"></div>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>

            <center><a href="/?lang=<?php echo $lang; ?>" class="btn-retour">← <?php echo $t['back']; ?></a></center>
        </div>

    <?php else: ?>
        <header style="text-align:center; margin-bottom:40px;">
            <img src="https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/items/poke-ball.png" width="50">
            <h1><?php echo $t['title']; ?></h1>
        </header>

        <div class="grid">
            <?php foreach ($pokedex as $pokemon): ?>
                <a href="<?php echo strtolower(urlencode($pokemon['noms'][$lang])); ?>?lang=<?php echo $lang; ?>" class="card">
                    <img src="<?php echo $pokemon['thumbnail']; ?>" loading="lazy">
                    <h3><?php echo $pokemon['noms'][$lang]; ?></h3>
                    
                    <div>
                        <?php foreach($pokemon['types'] as $type_obj): ?>
                            <span class="type-pill" style="background-color: <?php echo getTypeColor($type_obj['fr']); ?>">
                                <?php echo $type_obj[$lang]; ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

</body>
</html>
