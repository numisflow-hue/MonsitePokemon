<?php
// 1. CHARGEMENT DES DONNÉES
// On récupère le contenu du fichier JSON
$json_data = file_get_contents('pokedex.json');
// On le transforme en tableau PHP utilisable
$pokedex = json_decode($json_data, true);

// 2. LE ROUTEUR (Le cerveau du site)
// On regarde si l'URL contient "?id=..."
$pokemon_actuel = null;
if (isset($_GET['id'])) {
    // On cherche le Pokémon correspondant à l'ID demandé
    foreach ($pokedex as $p) {
        if ($p['id'] == $_GET['id']) {
            $pokemon_actuel = $p;
            break;
        }
    }
}

// 3. AFFICHAGE (Le HTML)
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pokemon_actuel ? $pokemon_actuel['nom'] . " - Fiche Pokédex" : "Mon Super Pokédex"; ?></title>
    
    <style>
        body { font-family: sans-serif; background-color: #f4f4f4; padding: 20px; text-align: center; }
        .container { max-width: 800px; margin: 0 auto; }
        
        /* Style de la grille (Accueil) */
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 15px; }
        .card { background: white; padding: 15px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); text-decoration: none; color: black; transition: transform 0.2s; }
        .card:hover { transform: translateY(-5px); }
        .card img { max-width: 100px; }
        
        /* Style de la fiche détail */
        .detail-card { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); display: inline-block; }
        .detail-img { max-width: 300px; }
        .stats-table { margin: 20px auto; width: 100%; max-width: 400px; text-align: left; }
        .stats-table th { background: #eee; padding: 5px; }
        .stats-table td { border-bottom: 1px solid #ddd; padding: 5px; }
        .type-badge { background: #333; color: white; padding: 5px 10px; border-radius: 20px; margin: 2px; display:inline-block; font-size: 0.8em; }
        .btn-retour { display: inline-block; margin-top: 20px; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>

<div class="container">

    <?php if ($pokemon_actuel): ?>
        <div class="detail-card">
            <h1><?php echo $pokemon_actuel['nom']; ?> <span style="color:#888">#<?php echo $pokemon_actuel['id']; ?></span></h1>
            
            <img src="<?php echo $pokemon_actuel['image']; ?>" alt="<?php echo $pokemon_actuel['nom']; ?>" class="detail-img">
            
            <div>
                <?php foreach($pokemon_actuel['types'] as $type): ?>
                    <span class="type-badge"><?php echo $type; ?></span>
                <?php endforeach; ?>
            </div>

            <table class="stats-table">
                <?php foreach($pokemon_actuel['stats'] as $stat => $val): ?>
                <tr>
                    <td><strong><?php echo $stat; ?></strong></td>
                    <td><?php echo $val; ?></td>
                </tr>
                <?php endforeach; ?>
            </table>

            <a href="index.php" class="btn-retour">← Retour au Pokédex</a>
        </div>

    <?php else: ?>
        <h1>Bienvenue sur le Pokédex</h1>
        <p>Choisis un Pokémon pour voir ses stats !</p>
        
        <div class="grid">
            <?php foreach ($pokedex as $pokemon): ?>
                <a href="?id=<?php echo $pokemon['id']; ?>" class="card">
                    <img src="<?php echo $pokemon['thumbnail']; ?>" alt="<?php echo $pokemon['nom']; ?>">
                    <h3><?php echo $pokemon['nom']; ?></h3>
                    <?php foreach($pokemon['types'] as $type): ?>
                        <small style="color:#666"><?php echo $type; ?> </small>
                    <?php endforeach; ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

</body>
</html>
