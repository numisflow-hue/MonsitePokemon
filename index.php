<?php
$json_data = file_get_contents('pokedex.json');
$pokedex = json_decode($json_data, true);

// ROUTEUR (Mise à jour pour chercher les noms français ou anglais)
// L'URL rewriting nettoie l'URL
$request = trim($_SERVER['REQUEST_URI'], '/');
$request = strtok($request, '?');
// On décode les accents (ex: "m%C3%A9lof%C3%A9e" devient "mélofée")
$request = urldecode($request); 

$pokemon_actuel = null;

if (!empty($request)) {
    foreach ($pokedex as $p) {
        // On vérifie si l'URL correspond au Nom FR OU au Nom EN
        if (mb_strtolower($p['nom'], 'UTF-8') == mb_strtolower($request, 'UTF-8') || 
            strtolower($p['nom_en']) == strtolower($request)) {
            $pokemon_actuel = $p;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="/"> 
    <meta name="description" content="<?php echo $pokemon_actuel ? $pokemon_actuel['description'] : 'Le Pokédex complet avec stats et descriptions.'; ?>">
    
    <title><?php echo $pokemon_actuel ? $pokemon_actuel['nom'] . " - Fiche Pokédex" : "Mon Pokédex Ultime"; ?></title>
    
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f0f2f5; padding: 20px; text-align: center; color:#333; }
        .container { max-width: 900px; margin: 0 auto; }
        
        /* Grille Accueil */
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 20px; }
        .card { background: white; padding: 15px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); text-decoration: none; color: inherit; transition: all 0.2s; border: 1px solid #eee; }
        .card:hover { transform: translateY(-5px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); border-color: #ddd; }
        .card img { width: 96px; height: 96px; object-fit: contain; }
        .card h3 { margin: 10px 0 5px; font-size: 1.1em; }
        
        /* Fiche Détail */
        .detail-card { background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); max-width: 600px; margin: 0 auto; text-align: left;}
        .header-detail { text-align: center; margin-bottom: 20px; }
        .detail-img { width: 250px; height: 250px; object-fit: contain; display: block; margin: 0 auto; filter: drop-shadow(0 10px 10px rgba(0,0,0,0.2)); }
        
        .description-box { background: #f9f9f9; padding: 20px; border-left: 5px solid #ffcb05; margin: 20px 0; border-radius: 4px; font-style: italic; font-size: 1.1em; line-height: 1.6; }
        
        .stats-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .stats-table td { padding: 8px 0; border-bottom: 1px solid #eee; }
        .stat-bar-bg { background: #eee; height: 8px; border-radius: 4px; width: 100%; display: inline-block; overflow: hidden; }
        .stat-bar-fill { background: #3b4cca; height: 100%; display: block; border-radius: 4px; }
        
        .btn-retour { display: inline-block; margin-top: 30px; color: #666; text-decoration: none; font-weight: bold; }
        .btn-retour:hover { color: #000; }
    </style>
</head>
<body>

<div class="container">

    <?php if ($pokemon_actuel): ?>
        <div class="detail-card">
            <div class="header-detail">
                <h1 style="margin:0; font-size: 2.5em;"><?php echo $pokemon_actuel['nom']; ?></h1>
                <span style="color:#999; font-weight:bold; font-size: 1.2em;">#<?php echo str_pad($pokemon_actuel['id'], 3, '0', STR_PAD_LEFT); ?></span>
            </div>
            
            <img src="<?php echo $pokemon_actuel['image']; ?>" alt="<?php echo $pokemon_actuel['nom']; ?>" class="detail-img">
            
            <div class="description-box">
                « <?php echo $pokemon_actuel['description']; ?> »
            </div>

            <h3>Statistiques de base</h3>
            <table class="stats-table">
                <?php foreach($pokemon_actuel['stats'] as $stat => $val): ?>
                <tr>
                    <td width="30%"><strong><?php echo $stat; ?></strong></td>
                    <td width="10%"><?php echo $val; ?></td>
                    <td width="60%">
                        <div class="stat-bar-bg">
                            <div class="stat-bar-fill" style="width: <?php echo min(100, $val/1.5); ?>%;"></div>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
            
            <div style="margin-top: 20px; font-size: 0.9em; color: #666;">
                Poids : <?php echo $pokemon_actuel['poids']; ?> kg | Taille : <?php echo $pokemon_actuel['taille']; ?> m
            </div>

            <center><a href="/" class="btn-retour">← Retourner à la liste</a></center>
        </div>

    <?php else: ?>
        <img src="https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/items/poke-ball.png" style="width: 50px; margin-bottom: 20px;">
        <h1>Le Pokédex Complet</h1>
        
        <div class="grid">
            <?php foreach ($pokedex as $pokemon): ?>
                <a href="<?php echo strtolower(urlencode($pokemon['nom'])); ?>" class="card">
                    <img src="<?php echo $pokemon['thumbnail']; ?>" loading="lazy" alt="<?php echo $pokemon['nom']; ?>">
                    <h3><?php echo $pokemon['nom']; ?></h3>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

</body>
</html>
