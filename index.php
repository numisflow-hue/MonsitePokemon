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
            padding: 8px 15px; border-radius: 20px; outline: none; font-size
